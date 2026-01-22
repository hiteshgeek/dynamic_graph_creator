/**
 * Dashboard Preview Page - Delete functionality and widget loading
 * Uses GraphPreview class for chart rendering (shared with builder)
 */
(function() {
    'use strict';

    // Shared widget loader instance
    var widgetLoader = null;
    var filterView = null;

    // Wait for dependencies to be available
    function waitForDependencies(callback, maxAttempts) {
        maxAttempts = maxAttempts || 100;
        var attempts = 0;

        function check() {
            attempts++;
            var ajaxReady = typeof window.Ajax !== 'undefined';
            var echartsReady = typeof window.echarts !== 'undefined';
            var graphPreviewReady = typeof window.GraphPreview !== 'undefined';
            var widgetLoaderReady = typeof window.WidgetLoader !== 'undefined';
            var filterViewReady = typeof window.FilterView !== 'undefined';
            var filterRendererReady = typeof window.FilterRenderer !== 'undefined';

            if (ajaxReady && echartsReady && graphPreviewReady && widgetLoaderReady && filterViewReady && filterRendererReady) {
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
        // Add page-specific body class for CSS targeting
        document.body.classList.add('dashboard-preview-page');

        // Wait for dependencies then initialize everything
        waitForDependencies(async function() {
            // Initialize filter bar (needs DatePickerInit)
            await initDashboardFilterBar();

            // Initialize shared WidgetLoader
            widgetLoader = new window.WidgetLoader({
                logPrefix: '[dashboard-preview.js]'
            });

            // Load widgets with current filter values (filters are now loaded from session)
            var previewContainer = document.getElementById('dashboard-preview');
            if (previewContainer && filterView) {
                var filterValues = filterView.getFilterValues();
                widgetLoader.loadAll(previewContainer, filterValues);
            }

            initDeleteButton();
            initDescriptionTruncation();
        });
    }

    /**
     * Initialize dashboard filter bar using FilterView
     * Centralized filter handling shared with dashboard-builder
     * @returns {Promise} Resolves when filter bar is initialized and filters are loaded from session
     */
    async function initDashboardFilterBar() {
        // Get dashboard ID from page
        var dashboardId = null;
        var deleteBtn = document.querySelector('.delete-dashboard-btn');
        if (deleteBtn && deleteBtn.dataset.dashboardId) {
            dashboardId = parseInt(deleteBtn.dataset.dashboardId, 10);
        }

        // Initialize FilterView with Bar layout
        filterView = new window.FilterView({
            containerSelector: '.dashboard-filter-bar',
            dashboardId: dashboardId,
            onFilterChange: function(filterValues) {
                // Reload all widgets with new filter values
                var previewContainer = document.getElementById('dashboard-preview');
                if (previewContainer && widgetLoader) {
                    widgetLoader.loadAll(previewContainer, filterValues);
                }
            },
            logPrefix: '[dashboard-preview.js]'
        }).Bar();

        // Wait for filters to load from session before proceeding
        if (filterView.filtersLoadedPromise) {
            await filterView.filtersLoadedPromise;
        }
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
        if (!deleteBtn) return;

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
                .catch(function() {
                    window.Toast.error('Failed to delete dashboard');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i>';
                });
        });
    }

})();
