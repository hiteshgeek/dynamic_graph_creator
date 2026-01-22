/**
 * Dashboard Preview Page - Delete functionality and widget loading
 * Uses GraphPreview class for chart rendering (shared with builder)
 */
(function() {
    'use strict';

    console.log('[dashboard-preview.js] Script loaded');

    // Shared widget loader instance
    var widgetLoader = null;

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

            var datePickerReady = typeof window.DatePickerInit !== 'undefined';
            console.log('[dashboard-preview.js] Check #' + attempts + ' - Ajax:', ajaxReady, 'ECharts:', echartsReady, 'GraphPreview:', graphPreviewReady, 'WidgetLoader:', widgetLoaderReady, 'DatePickerInit:', datePickerReady);

            if (ajaxReady && echartsReady && graphPreviewReady && widgetLoaderReady && datePickerReady) {
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

        // Wait for dependencies then initialize everything
        waitForDependencies(function() {
            console.log('[dashboard-preview.js] Starting initialization...');

            // Initialize filter bar (needs DatePickerInit)
            initDashboardFilterBar();

            // Initialize shared WidgetLoader
            widgetLoader = new window.WidgetLoader({
                logPrefix: '[dashboard-preview.js]'
            });

            // Load widgets with current filter values
            var previewContainer = document.getElementById('dashboard-preview');
            if (previewContainer) {
                var filterValues = getDashboardFilterValues();
                widgetLoader.loadAll(previewContainer, filterValues);
            }

            initDeleteButton();
            initDescriptionTruncation();
        });
    }

    /**
     * Initialize dashboard filter bar
     * Handles datepickers, collapse state, auto-apply toggle
     */
    function initDashboardFilterBar() {
        var filterBar = document.querySelector('.dashboard-filter-bar');
        if (!filterBar) return;

        var filtersContainer = filterBar.querySelector('#dashboard-filters');
        if (!filtersContainer) return;

        console.log('[dashboard-preview.js] Initializing filter bar');

        // Initialize datepickers using centralized DatePickerInit class
        if (window.DatePickerInit) {
            window.DatePickerInit.init(filtersContainer);
        }

        // Get UI elements
        var applyBtn = filterBar.querySelector('.filter-apply-btn');
        var autoApplySwitch = filterBar.querySelector('#dashboard-auto-apply-switch');
        var collapseBtn = filterBar.querySelector('.filter-collapse-btn');

        // Track auto-apply state
        var autoApplyEnabled = false;

        // Collapse/Expand functionality
        var COLLAPSE_KEY = 'dgc_dashboard_filters_collapsed';

        function updateCollapseState(collapsed) {
            if (collapsed) {
                filterBar.classList.add('collapsed');
                if (collapseBtn) collapseBtn.title = 'Expand Filters';
            } else {
                filterBar.classList.remove('collapsed');
                if (collapseBtn) collapseBtn.title = 'Collapse Filters';
            }
        }

        // Restore collapse state from localStorage
        var savedCollapsed = localStorage.getItem(COLLAPSE_KEY) === '1';
        updateCollapseState(savedCollapsed);

        // Enable transitions after initial state is applied
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                filterBar.classList.add('transitions-enabled');
            });
        });

        // Collapse button click handler
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function() {
                var isCollapsed = filterBar.classList.contains('collapsed');
                var newState = !isCollapsed;
                updateCollapseState(newState);
                localStorage.setItem(COLLAPSE_KEY, newState ? '1' : '0');
            });
        }

        // Auto-apply switch handler
        if (autoApplySwitch) {
            autoApplySwitch.addEventListener('change', function() {
                autoApplyEnabled = this.checked;
                if (applyBtn) {
                    applyBtn.style.display = autoApplyEnabled ? 'none' : '';
                }
            });
        }

        // Apply button click - reload charts with new filters
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                console.log('[dashboard-preview.js] Applying filters...');
                var previewContainer = document.getElementById('dashboard-preview');
                if (previewContainer && widgetLoader) {
                    var filterValues = getDashboardFilterValues();
                    widgetLoader.loadAll(previewContainer, filterValues);
                }
            });
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
     * Get filter values from dashboard filter bar
     * Reusable utility for extracting filter values
     */
    function getDashboardFilterValues() {
        var filtersContainer = document.querySelector('#dashboard-filters');
        if (!filtersContainer) return {};

        var filterValues = {};
        var filterItems = filtersContainer.querySelectorAll('.filter-input-item');

        filterItems.forEach(function(item) {
            var filterKey = item.dataset.filterKey;
            if (!filterKey) return;

            var value = getFilterItemValue(item, filterKey);
            if (value !== null) {
                Object.keys(value).forEach(function(key) {
                    filterValues[key] = value[key];
                });
            }
        });

        return filterValues;
    }

    /**
     * Get value from a single filter item
     */
    function getFilterItemValue(item, filterKey) {
        // Single select
        var select = item.querySelector('select.filter-input');
        if (select && select.value) {
            var result = {};
            result['::' + filterKey] = select.value;
            return result;
        }

        // Multi-select dropdown (checkboxes)
        var multiSelectChecked = item.querySelectorAll('.filter-multiselect-options input[type="checkbox"]:checked');
        if (multiSelectChecked.length > 0) {
            var values = Array.prototype.slice.call(multiSelectChecked).map(function(cb) { return cb.value; });
            var result = {};
            result['::' + filterKey] = values;
            return result;
        }

        // Checkbox group
        var checkboxChecked = item.querySelectorAll('.filter-checkbox-group input[type="checkbox"]:checked');
        if (checkboxChecked.length > 0) {
            var values = Array.prototype.slice.call(checkboxChecked).map(function(cb) { return cb.value; });
            var result = {};
            result['::' + filterKey] = values;
            return result;
        }

        // Radio group
        var radioChecked = item.querySelector('.filter-radio-group input[type="radio"]:checked');
        if (radioChecked) {
            var result = {};
            result['::' + filterKey] = radioChecked.value;
            return result;
        }

        // Date range picker
        var dateRangePicker = item.querySelector('.dgc-datepicker[data-picker-type="range"], .dgc-datepicker[data-picker-type="main"]');
        if (dateRangePicker) {
            var from = dateRangePicker.dataset.from;
            var to = dateRangePicker.dataset.to;

            if (from || to) {
                var result = {};
                if (from) result['::' + filterKey + '_from'] = from;
                if (to) result['::' + filterKey + '_to'] = to;
                return result;
            }
            return null;
        }

        // Single date picker
        var singleDatePicker = item.querySelector('.dgc-datepicker[data-picker-type="single"]');
        if (singleDatePicker && singleDatePicker.value) {
            var result = {};
            result['::' + filterKey] = singleDatePicker.value;
            return result;
        }

        // Text/number input
        var textInput = item.querySelector('input.filter-input:not(.dgc-datepicker)');
        if (textInput && textInput.value) {
            var result = {};
            result['::' + filterKey] = textInput.value;
            return result;
        }

        return null;
    }

})();
