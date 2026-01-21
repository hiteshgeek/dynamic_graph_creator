/**
 * Dashboard Preview Page - Delete functionality and widget loading
 * Uses GraphPreview class for chart rendering (shared with builder)
 */
(function() {
    'use strict';

    console.log('[dashboard-preview.js] Script loaded');

    // Wait for dependencies to be available
    function waitForDependencies(callback, maxAttempts) {
        maxAttempts = maxAttempts || 100;
        var attempts = 0;

        function check() {
            attempts++;
            var ajaxReady = typeof window.Ajax !== 'undefined';
            var echartsReady = typeof window.echarts !== 'undefined';
            var graphPreviewReady = typeof window.GraphPreview !== 'undefined';

            console.log('[dashboard-preview.js] Check #' + attempts + ' - Ajax:', ajaxReady, 'ECharts:', echartsReady, 'GraphPreview:', graphPreviewReady);

            if (ajaxReady && echartsReady && graphPreviewReady) {
                console.log('[dashboard-preview.js] Dependencies ready');
                callback();
            } else if (attempts < maxAttempts) {
                setTimeout(check, 100);
            } else {
                console.error('[dashboard-preview.js] Dependencies not available after ' + maxAttempts + ' attempts');
            }
        }

        check();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM is already ready
        init();
    }

    function init() {
        console.log('[dashboard-preview.js] DOM ready, initializing...');

        // Add page-specific body class for CSS targeting
        document.body.classList.add('dashboard-preview-page');

        // Wait for dependencies then initialize
        waitForDependencies(function() {
            console.log('[dashboard-preview.js] Starting widget loading...');
            loadAllWidgetGraphs();
            initDeleteButton();
            initDescriptionTruncation();
        });
    }

    /**
     * Check if description text is truncated and add is-truncated class
     */
    function initDescriptionTruncation() {
        var descriptions = document.querySelectorAll('.widget-graph-description');
        descriptions.forEach(function(desc) {
            checkTruncation(desc);
        });

        // Also check on window resize
        window.addEventListener('resize', function() {
            var descriptions = document.querySelectorAll('.widget-graph-description');
            descriptions.forEach(function(desc) {
                checkTruncation(desc);
            });
        });
    }

    /**
     * Check if a description element is truncated
     */
    function checkTruncation(descElement) {
        // Only check when collapsed
        if (!descElement.classList.contains('collapsed')) {
            descElement.classList.add('is-truncated');
            return;
        }

        // Check the text span for truncation
        var textSpan = descElement.querySelector('.description-text');
        if (textSpan && textSpan.scrollWidth > textSpan.clientWidth) {
            descElement.classList.add('is-truncated');
        } else {
            descElement.classList.remove('is-truncated');
        }
    }

    /**
     * Initialize delete button handler
     */
    function initDeleteButton() {
        var deleteBtn = document.querySelector('.delete-dashboard-btn');
        if (!deleteBtn) {
            console.log('[dashboard-preview.js] No delete button found');
            return;
        }

        deleteBtn.addEventListener('click', function() {
            var btn = this;
            var dashboardId = btn.dataset.dashboardId;

            window.ConfirmDialog.delete('Are you sure you want to delete this dashboard?', 'Confirm Delete')
                .then(function(confirmed) {
                    if (!confirmed) return;

                    // Show loading state
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

                    return window.Ajax.post('delete_dashboard', { id: dashboardId });
                })
                .then(function(result) {
                    if (!result) return; // User cancelled

                    if (result.success) {
                        window.Toast.success('Dashboard deleted successfully');
                        setTimeout(function() {
                            window.location.href = '?urlq=dashboard';
                        }, 500);
                    } else {
                        window.Toast.error(result.message || 'Failed to delete dashboard');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash"></i>';
                    }
                })
                .catch(function(error) {
                    console.error('[dashboard-preview.js] Delete error:', error);
                    window.Toast.error('Failed to delete dashboard');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i>';
                });
        });
    }

    /**
     * Load all widget graphs on the dashboard preview
     */
    function loadAllWidgetGraphs() {
        console.log('[dashboard-preview.js] loadAllWidgetGraphs called');

        var graphContainers = document.querySelectorAll('.widget-graph-container[data-graph-id]');
        console.log('[dashboard-preview.js] Found ' + graphContainers.length + ' graph containers');

        if (graphContainers.length === 0) {
            console.log('[dashboard-preview.js] No graph containers found on page');
            return;
        }

        // Convert NodeList to array for forEach compatibility
        var containers = Array.prototype.slice.call(graphContainers);

        containers.forEach(function(container) {
            var graphId = parseInt(container.dataset.graphId, 10);
            console.log('[dashboard-preview.js] Processing container with graphId:', graphId);

            if (!graphId) {
                console.log('[dashboard-preview.js] Skipping container - no valid graphId');
                return;
            }

            // Get graph type from parent element
            var areaContent = container.closest('.area-content');
            var graphType = (areaContent && areaContent.dataset.graphType) ? areaContent.dataset.graphType : 'bar';
            console.log('[dashboard-preview.js] Graph type:', graphType);

            loadWidgetGraph(container, graphId, graphType);
        });
    }

    /**
     * Load and render a single widget graph using GraphPreview class
     */
    function loadWidgetGraph(container, graphId, graphType) {
        console.log('[dashboard-preview.js] Loading widget graph:', graphId, 'type:', graphType);
        console.log('[dashboard-preview.js] Container dimensions:', container.offsetWidth, 'x', container.offsetHeight);

        window.Ajax.post('preview_graph', {
            id: graphId,
            filters: {}
        })
        .then(function(result) {
            console.log('[dashboard-preview.js] Graph API result:', result);

            if (!result.success || !result.data) {
                console.error('[dashboard-preview.js] API returned error:', result.message);
                container.innerHTML = '<div class="widget-graph-error"><i class="fas fa-exclamation-triangle"></i><span>' + (result.message || 'Failed to load chart') + '</span></div>';
                return;
            }

            // Check if chartData has error
            var chartData = result.data.chartData;
            if (chartData && chartData.error) {
                container.innerHTML = '<div class="widget-graph-error"><i class="fas fa-exclamation-triangle"></i><span>' + chartData.error + '</span></div>';
                return;
            }

            // Clear loading state
            container.innerHTML = '';

            // Ensure container has dimensions before initializing chart
            if (container.offsetWidth === 0 || container.offsetHeight === 0) {
                container.style.minHeight = '300px';
                container.style.minWidth = '100%';
            }

            // Use GraphPreview class for consistent rendering with builder
            var preview = new window.GraphPreview(container);

            // Set graph type from API response
            var actualGraphType = result.data.graphType || graphType;
            preview.setType(actualGraphType);

            // Set config from graph data
            if (result.data.config) {
                preview.setConfig(result.data.config);
            }

            // Set mapping if available (for axis titles)
            if (chartData && chartData.mapping) {
                preview.setMapping(chartData.mapping);
            }

            // Set data and render
            preview.setData(chartData);
            preview.render();

            console.log('[dashboard-preview.js] Chart rendered successfully for graph', graphId);
        })
        .catch(function(error) {
            console.error('[dashboard-preview.js] Error loading widget graph:', error);
            container.innerHTML = '<div class="widget-graph-error"><i class="fas fa-exclamation-triangle"></i><span>Failed to load chart</span></div>';
        });
    }

})();
