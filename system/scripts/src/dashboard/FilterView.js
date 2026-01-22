/**
 * FilterView - Manages filter rendering and interactions for different view types
 *
 * Supports multiple view types:
 * - Bar: Collapsible horizontal filter bar (default dashboard view)
 * - Grid: Future - Grid layout for filters
 * - List: Future - Vertical list layout
 *
 * Usage:
 *   const filterView = new FilterView({
 *       containerSelector: '.dashboard-filter-bar',
 *       onFilterChange: (filterValues) => { ... }
 *   });
 *   filterView.Bar(); // Initialize as bar view
 */
export class FilterView {
    constructor(options = {}) {
        this.options = {
            containerSelector: null,
            filtersContainerSelector: '#dashboard-filters',
            dashboardId: null, // Dashboard ID for session storage
            onFilterChange: null, // Callback when filters change
            logPrefix: '[FilterView]',
            ...options
        };

        this.container = null;
        this.filtersContainer = null;
        this.applyBtn = null;
        this.autoApplySwitch = null;
        this.collapseBtn = null;
        this.autoApplyEnabled = false;
        this.debounceTimer = null;
        this.viewType = null;
        this.filtersLoadedPromise = null;

        // Constants
        this.COLLAPSE_KEY = 'dgc_dashboard_filters_collapsed';
    }

    /**
     * Initialize Bar view (collapsible horizontal filter bar)
     * @param {string|HTMLElement} container - Container selector or element
     * @returns {FilterView} Returns this for chaining
     */
    Bar(container = null) {
        this.viewType = 'bar';

        // Find container
        if (container) {
            this.container = typeof container === 'string'
                ? document.querySelector(container)
                : container;
        } else if (this.options.containerSelector) {
            this.container = document.querySelector(this.options.containerSelector);
        }

        if (!this.container) {
            console.warn(`${this.options.logPrefix} Container not found`);
            return this;
        }

        // Find filters container
        this.filtersContainer = this.container.querySelector(this.options.filtersContainerSelector);
        if (!this.filtersContainer) {
            console.warn(`${this.options.logPrefix} Filters container not found`);
            return this;
        }

        // Get UI elements
        this.applyBtn = this.container.querySelector('.filter-apply-btn');
        this.autoApplySwitch = this.container.querySelector('#dashboard-auto-apply-switch');
        this.collapseBtn = this.container.querySelector('.filter-collapse-btn');

        // Initialize bar-specific features
        this.initBarCollapse();
        this.initBarAutoApply();
        this.initBarListeners();

        // Initialize pickers first (with defaults)
        this.initPickers();

        // Then load and apply saved filters from session
        // This ensures saved values override the defaults
        // Store the promise so external code can wait for it
        this.filtersLoadedPromise = this.loadFiltersFromSession();

        return this;
    }

    /**
     * Initialize pickers using FilterRenderer (handles datepickers, multi-selects, etc.)
     */
    initPickers() {
        // Use FilterRenderer for comprehensive initialization
        if (typeof window.FilterRenderer !== 'undefined') {
            window.FilterRenderer.initPickers(this.filtersContainer);
        } else if (typeof window.DatePickerInit !== 'undefined') {
            // Fallback to just datepickers
            window.DatePickerInit.init(this.filtersContainer);
        } else {
            console.warn(`${this.options.logPrefix} FilterRenderer and DatePickerInit not available`);
        }
    }

    /**
     * Initialize Bar collapse/expand functionality
     */
    initBarCollapse() {
        if (!this.collapseBtn) return;

        // Restore collapse state from localStorage
        const savedCollapsed = localStorage.getItem(this.COLLAPSE_KEY) === '1';
        this.updateBarCollapseState(savedCollapsed);

        // Enable transitions after initial state is applied
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                this.container.classList.add('transitions-enabled');
            });
        });

        // Collapse button click handler
        this.collapseBtn.addEventListener('click', () => {
            const isCollapsed = this.container.classList.contains('collapsed');
            const newState = !isCollapsed;
            this.updateBarCollapseState(newState);
            localStorage.setItem(this.COLLAPSE_KEY, newState ? '1' : '0');
        });
    }

    /**
     * Update Bar collapse state
     */
    updateBarCollapseState(collapsed) {
        if (collapsed) {
            this.container.classList.add('collapsed');
            if (this.collapseBtn) this.collapseBtn.title = 'Expand Filters';
        } else {
            this.container.classList.remove('collapsed');
            if (this.collapseBtn) this.collapseBtn.title = 'Collapse Filters';
        }
    }

    /**
     * Initialize Bar auto-apply toggle
     */
    initBarAutoApply() {
        if (!this.autoApplySwitch) return;

        // Restore auto-apply state from localStorage
        const savedAutoApply = localStorage.getItem('dgc_dashboard_auto_apply');
        this.autoApplyEnabled = savedAutoApply === '1';
        this.autoApplySwitch.checked = this.autoApplyEnabled;
        this.updateAutoApplyUI();

        // Auto-apply toggle handler
        this.autoApplySwitch.addEventListener('change', () => {
            this.autoApplyEnabled = this.autoApplySwitch.checked;
            localStorage.setItem('dgc_dashboard_auto_apply', this.autoApplyEnabled ? '1' : '0');
            this.updateAutoApplyUI();

            // If turning on auto-apply, immediately apply current filters
            if (this.autoApplyEnabled) {
                this.applyFilters();
            }
        });
    }

    /**
     * Update UI based on auto-apply state
     */
    updateAutoApplyUI() {
        if (!this.applyBtn) return;

        const separator = this.container.querySelector('.filter-actions-separator:not(:first-of-type)');

        if (this.autoApplyEnabled) {
            // Hide apply button and separator when live filtering is ON
            this.applyBtn.classList.remove('visible');
            if (separator) separator.classList.remove('visible');
        } else {
            // Show apply button and separator when live filtering is OFF
            this.applyBtn.classList.add('visible');
            if (separator) separator.classList.add('visible');
        }
    }

    /**
     * Initialize Bar filter change listeners
     */
    initBarListeners() {
        // Apply button click
        if (this.applyBtn) {
            this.applyBtn.addEventListener('click', () => {
                this.applyFilters();
            });
        }

        // Listen for ALL change events on the container (catches bubbled events from all inputs)
        // This includes datepickers, selects, checkboxes, radios, etc.
        this.filtersContainer.addEventListener('change', () => {
            if (this.autoApplyEnabled) {
                this.applyFilters();
            }
        });

        // Text inputs (debounced) - use input event for live typing
        this.filtersContainer.querySelectorAll('input.filter-input:not(.dgc-datepicker)').forEach(input => {
            input.addEventListener('input', () => {
                if (this.autoApplyEnabled) {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.applyFilters();
                    }, 500);
                }
            });
        });
    }

    /**
     * Apply filters - calls the onFilterChange callback with current filter values
     */
    applyFilters() {
        if (this.options.onFilterChange) {
            const filterValues = this.getFilterValues();
            this.options.onFilterChange(filterValues);

            // Save filters to session if dashboardId is provided
            this.saveFiltersToSession(filterValues);

            // Show toast notification
            if (window.Toast) {
                window.Toast.success('Filters Applied');
            }
        }
    }

    /**
     * Get all filter values from the filters container
     * @returns {Object} Filter values keyed by placeholder (e.g., {'::company': '1', '::date_from': '2024-01-01'})
     */
    getFilterValues() {
        const filterValues = {};
        const filterItems = this.filtersContainer.querySelectorAll('.filter-input-item');

        filterItems.forEach(item => {
            const filterKey = item.dataset.filterKey;
            if (!filterKey) return;

            const value = this.getFilterItemValue(item, filterKey);
            if (value !== null) {
                Object.keys(value).forEach(key => {
                    filterValues[key] = value[key];
                });
            }
        });

        return filterValues;
    }

    /**
     * Get value from a single filter item
     * @param {HTMLElement} item - Filter item element
     * @param {string} filterKey - Filter key (without :: prefix)
     * @returns {Object|null} Filter value object or null
     */
    getFilterItemValue(item, filterKey) {
        // Single select
        const select = item.querySelector('select.filter-input');
        if (select && select.value) {
            const result = {};
            result['::' + filterKey] = select.value;
            return result;
        }

        // Multi-select dropdown (checkboxes)
        const multiSelectChecked = item.querySelectorAll('.filter-multiselect-options input[type="checkbox"]:checked');
        if (multiSelectChecked.length > 0) {
            const values = Array.prototype.slice.call(multiSelectChecked).map(cb => cb.value);
            const result = {};
            result['::' + filterKey] = values;
            return result;
        }

        // Checkbox group
        const checkboxChecked = item.querySelectorAll('.filter-checkbox-group input[type="checkbox"]:checked');
        if (checkboxChecked.length > 0) {
            const values = Array.prototype.slice.call(checkboxChecked).map(cb => cb.value);
            const result = {};
            result['::' + filterKey] = values;
            return result;
        }

        // Radio group
        const radioChecked = item.querySelector('.filter-radio-group input[type="radio"]:checked');
        if (radioChecked) {
            const result = {};
            result['::' + filterKey] = radioChecked.value;
            return result;
        }

        // Date range picker (range or main type)
        const dateRangePicker = item.querySelector('.dgc-datepicker[data-picker-type="range"], .dgc-datepicker[data-picker-type="main"]');
        if (dateRangePicker) {
            const from = dateRangePicker.dataset.from;
            const to = dateRangePicker.dataset.to;


            if (from || to) {
                const result = {};
                if (from) result['::' + filterKey + '_from'] = from;
                if (to) result['::' + filterKey + '_to'] = to;
                return result;
            }
            return null;
        }

        // Single date picker
        const singleDatePicker = item.querySelector('.dgc-datepicker[data-picker-type="single"]');
        if (singleDatePicker && singleDatePicker.value) {
            const result = {};
            result['::' + filterKey] = singleDatePicker.value;
            return result;
        }

        // Text/number input
        const textInput = item.querySelector('input.filter-input:not(.dgc-datepicker)');
        if (textInput && textInput.value) {
            const result = {};
            result['::' + filterKey] = textInput.value;
            return result;
        }

        return null;
    }

    /**
     * Save filters to session storage
     * @param {Object} filterValues - Filter values to save
     */
    saveFiltersToSession(filterValues) {
        if (!this.options.dashboardId) return;

        if (typeof window.Ajax !== 'undefined') {
            window.Ajax.post('save_dashboard_filter_values', {
                dashboard_id: this.options.dashboardId,
                filters: filterValues
            }).then(response => {
            }).catch(error => {
                console.warn(`${this.options.logPrefix} Failed to save filters:`, error);
            });
        }
    }

    /**
     * Load filters from session storage and apply them to inputs
     */
    async loadFiltersFromSession() {
        if (!this.options.dashboardId) {
            return;
        }

        if (typeof window.Ajax === 'undefined') {
            return;
        }


        try {
            const result = await window.Ajax.post('get_dashboard_filter_values', {
                dashboard_id: this.options.dashboardId
            });


            if (result.success && result.data && result.data.filters) {
                const filters = result.data.filters;

                // Check if filters object has any values
                const hasFilters = Object.keys(filters).length > 0;


                if (hasFilters) {
                    // Apply values to inputs (pickers are already initialized)
                    this.applyFilterValues(filters, false);

                    // Trigger filter application if auto-apply is enabled
                    if (this.autoApplyEnabled && this.options.onFilterChange) {
                        this.options.onFilterChange(filters);
                    }
                }
            } else {
            }
        } catch (error) {
            console.warn(`${this.options.logPrefix} Failed to load filters:`, error);
        }
    }

    /**
     * Apply saved filter values to the UI inputs
     * @param {Object} filterValues - Filter values to apply
     * @param {boolean} skipPickerUpdate - Skip updating picker display (for before initialization)
     */
    applyFilterValues(filterValues, skipPickerUpdate = false) {
        if (!filterValues || typeof filterValues !== 'object') return;


        const filterItems = this.filtersContainer.querySelectorAll('.filter-input-item');

        filterItems.forEach(item => {
            const filterKey = item.dataset.filterKey;
            if (!filterKey) return;

            const filterKeyWithPrefix = '::' + filterKey;
            const fromKey = filterKeyWithPrefix + '_from';
            const toKey = filterKeyWithPrefix + '_to';

            // Single select
            const select = item.querySelector('select.filter-input');
            if (select && filterValues[filterKeyWithPrefix]) {
                select.value = filterValues[filterKeyWithPrefix];
            }

            // Multi-select dropdown (checkboxes)
            const multiSelectCheckboxes = item.querySelectorAll('.filter-multiselect-options input[type="checkbox"]');
            if (multiSelectCheckboxes.length > 0 && filterValues[filterKeyWithPrefix]) {
                const values = Array.isArray(filterValues[filterKeyWithPrefix])
                    ? filterValues[filterKeyWithPrefix]
                    : [filterValues[filterKeyWithPrefix]];

                multiSelectCheckboxes.forEach(cb => {
                    cb.checked = values.includes(cb.value);
                });
            }

            // Checkbox group
            const checkboxes = item.querySelectorAll('.filter-checkbox-group input[type="checkbox"]');
            if (checkboxes.length > 0 && filterValues[filterKeyWithPrefix]) {
                const values = Array.isArray(filterValues[filterKeyWithPrefix])
                    ? filterValues[filterKeyWithPrefix]
                    : [filterValues[filterKeyWithPrefix]];

                checkboxes.forEach(cb => {
                    cb.checked = values.includes(cb.value);
                });
            }

            // Radio group
            const radios = item.querySelectorAll('.filter-radio-group input[type="radio"]');
            if (radios.length > 0 && filterValues[filterKeyWithPrefix]) {
                radios.forEach(radio => {
                    radio.checked = radio.value === filterValues[filterKeyWithPrefix];
                });
            }

            // Date range picker
            const dateRangePicker = item.querySelector('.dgc-datepicker[data-picker-type="range"], .dgc-datepicker[data-picker-type="main"]');
            if (dateRangePicker) {

                if (filterValues[fromKey] && filterValues[toKey]) {
                    // Set data attributes (vanilla JS)
                    dateRangePicker.dataset.from = filterValues[fromKey];
                    dateRangePicker.dataset.to = filterValues[toKey];

                    // Update daterangepicker plugin instance if initialized
                    if (!skipPickerUpdate && typeof $ !== 'undefined' && typeof moment !== 'undefined') {
                        const $picker = $(dateRangePicker);
                        const pickerInstance = $picker.data('daterangepicker');

                        if (pickerInstance) {
                            const fromMoment = moment(filterValues[fromKey], 'YYYY-MM-DD');
                            const toMoment = moment(filterValues[toKey], 'YYYY-MM-DD');

                            if (fromMoment.isValid() && toMoment.isValid()) {

                                // Set jQuery data attributes (used by DatePickerInit)
                                $picker.data('from', filterValues[fromKey]);
                                $picker.data('to', filterValues[toKey]);

                                // Set the dates in the picker instance
                                pickerInstance.setStartDate(fromMoment);
                                pickerInstance.setEndDate(toMoment);

                                // Update the calendars to reflect the new dates
                                pickerInstance.updateView();
                                pickerInstance.updateCalendars();

                                // Update the input display value
                                const displayValue = fromMoment.format('DD-MM-YYYY') + ' - ' + toMoment.format('DD-MM-YYYY');
                                $picker.val(displayValue);

                            }
                        } else {
                        }
                    }
                }
            }

            // Single date picker
            const singleDatePicker = item.querySelector('.dgc-datepicker[data-picker-type="single"]');
            if (singleDatePicker && filterValues[filterKeyWithPrefix]) {
                singleDatePicker.value = filterValues[filterKeyWithPrefix];
            }

            // Text/number input
            const textInput = item.querySelector('input.filter-input:not(.dgc-datepicker)');
            if (textInput && filterValues[filterKeyWithPrefix]) {
                textInput.value = filterValues[filterKeyWithPrefix];
            }
        });
    }

    /**
     * Destroy filter view - cleanup event listeners
     */
    destroy() {
        // Clear debounce timer
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        // Event listeners will be garbage collected when elements are removed
    }
}

export default FilterView;
