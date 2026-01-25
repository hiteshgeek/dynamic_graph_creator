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

        // Required filter tracking
        this.requiredFilters = {};  // { filterKey: { default_value, filter_type, filter_label } }

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

        // Find filters container (may be null if no filters)
        this.filtersContainer = this.container.querySelector(this.options.filtersContainerSelector);

        // Get UI elements (these exist even without filters)
        this.applyBtn = this.container.querySelector('.filter-apply-btn');
        this.autoApplySwitch = this.container.querySelector('#dashboard-auto-apply-switch');
        this.collapseBtn = this.container.querySelector('.filter-collapse-btn');

        // Initialize pickers FIRST (before collapse state is applied)
        // This ensures datepickers are initialized while visible
        // DatePickerInit skips hidden elements, so we must init before collapsing
        this.initPickers();

        // Initialize bar-specific features (collapse may hide filters)
        this.initBarCollapse();
        this.initBarAutoApply();
        this.initBarListeners();

        // Then load and apply saved filters from session
        // This ensures saved values override the defaults
        // Store the promise so external code can wait for it
        this.filtersLoadedPromise = this.loadFiltersFromSession();

        return this;
    }

    /**
     * Initialize pickers using FilterRenderer (handles datepickers, single selects, multi-selects, etc.)
     */
    initPickers() {
        // Skip if no filters container
        if (!this.filtersContainer) return;

        // Detect and store required filters
        this.detectRequiredFilters();

        // Use FilterRenderer for comprehensive initialization (datepickers, dropdowns, etc.)
        if (typeof window.FilterRenderer !== 'undefined') {
            window.FilterRenderer.init(this.filtersContainer);
        } else if (typeof window.DatePickerInit !== 'undefined') {
            // Fallback to just datepickers
            window.DatePickerInit.init(this.filtersContainer);
        } else {
            console.warn(`${this.options.logPrefix} FilterRenderer and DatePickerInit not available`);
        }
    }

    /**
     * Detect required filters and store their metadata
     */
    detectRequiredFilters() {
        if (!this.filtersContainer) return;

        this.requiredFilters = {};
        const filterItems = this.filtersContainer.querySelectorAll('.filter-input-item[data-is-required="1"]');

        filterItems.forEach(item => {
            const filterKey = item.dataset.filterKey;
            const filterType = item.dataset.filterType;
            const filterLabel = item.querySelector('.filter-input-label')?.textContent || filterKey;
            const defaultValueRaw = item.dataset.defaultValue || '';

            if (filterKey) {
                let defaultValue = null;
                try {
                    defaultValue = defaultValueRaw ? JSON.parse(defaultValueRaw) : null;
                } catch (e) {
                    // Legacy plain string
                    defaultValue = defaultValueRaw ? { value: defaultValueRaw } : null;
                }

                this.requiredFilters[filterKey] = {
                    filter_key: filterKey,
                    filter_type: filterType,
                    filter_label: filterLabel,
                    default_value: defaultValue
                };
            }
        });
    }

    /**
     * Check if a filter is required
     */
    isFilterRequired(filterKey) {
        return !!this.requiredFilters[filterKey];
    }

    /**
     * Get default value for a required filter
     */
    getFilterDefaultValue(filterKey) {
        const filter = this.requiredFilters[filterKey];
        if (!filter || !filter.default_value) return null;
        return filter.default_value;
    }

    /**
     * Apply default values for required filters that are empty
     */
    applyRequiredFilterDefaults() {
        if (!this.filtersContainer) return;

        Object.keys(this.requiredFilters).forEach(filterKey => {
            const filter = this.requiredFilters[filterKey];
            const item = this.filtersContainer.querySelector(`.filter-input-item[data-filter-key="${filterKey}"]`);
            if (!item || !filter.default_value) return;

            // Check if filter currently has a value
            const currentValue = this.getFilterItemValue(item, filterKey);
            if (currentValue && Object.keys(currentValue).some(k => currentValue[k])) {
                return; // Already has a value
            }

            // Apply default value
            this.applyDefaultToFilter(item, filter);
        });
    }

    /**
     * Apply default value to a single filter
     */
    applyDefaultToFilter(item, filter) {
        const filterKey = filter.filter_key;
        const defaultValue = filter.default_value;
        if (!defaultValue) return;

        const filterType = filter.filter_type;

        switch (filterType) {
            case 'date_range':
            case 'main_datepicker':
                this.applyDateRangeDefault(item, filterKey, defaultValue);
                break;

            case 'select':
            case 'radio':
                if (defaultValue.value) {
                    const filterValues = {};
                    filterValues['::' + filterKey] = defaultValue.value;
                    this.applyFilterValuesToItem(item, filterKey, filterValues);
                }
                break;

            case 'multi_select':
            case 'checkbox':
                if (defaultValue.mode === 'all') {
                    // Select all available options
                    const allValues = this.getAllOptionsForFilter(item);
                    if (allValues.length > 0) {
                        const filterValues = {};
                        filterValues['::' + filterKey] = allValues;
                        this.applyFilterValuesToItem(item, filterKey, filterValues);
                    }
                } else if (defaultValue.values && defaultValue.values.length > 0) {
                    const filterValues = {};
                    filterValues['::' + filterKey] = defaultValue.values;
                    this.applyFilterValuesToItem(item, filterKey, filterValues);
                }
                break;

            case 'text':
            case 'number':
            case 'date':
                if (defaultValue.value) {
                    const filterValues = {};
                    filterValues['::' + filterKey] = defaultValue.value;
                    this.applyFilterValuesToItem(item, filterKey, filterValues);
                }
                break;

            case 'tokeninput':
                if (defaultValue.values && defaultValue.values.length > 0) {
                    const filterValues = {};
                    filterValues['::' + filterKey] = defaultValue.values.join(',');
                    this.applyFilterValuesToItem(item, filterKey, filterValues);
                }
                break;
        }
    }

    /**
     * Apply date range default value
     */
    applyDateRangeDefault(item, filterKey, defaultValue) {
        const mode = defaultValue.mode || 'select_all';

        // Skip if mode is 'block' (require user selection)
        if (mode === 'block') {
            return;
        }

        let fromDate = '';
        let toDate = '';

        if (mode === 'select_all') {
            // No filter applied - leave empty
            return;
        } else if (mode === 'specific') {
            fromDate = defaultValue.from || '';
            toDate = defaultValue.to || '';
        } else if (mode === 'preset') {
            // Resolve preset to actual dates
            const resolved = this.resolvePresetToDateRange(defaultValue.preset || 'Last 7 Days');
            fromDate = resolved.from;
            toDate = resolved.to;
        }

        if (fromDate && toDate) {
            const filterValues = {};
            filterValues['::' + filterKey + '_from'] = fromDate;
            filterValues['::' + filterKey + '_to'] = toDate;
            this.applyFilterValuesToItem(item, filterKey, filterValues);
        }
    }

    /**
     * Resolve preset name to date range
     */
    resolvePresetToDateRange(preset) {
        const today = new Date();
        const formatDate = (d) => d.toISOString().split('T')[0];
        let from = new Date(today);
        let to = new Date(today);

        switch (preset) {
            case 'Today':
                break;
            case 'Yesterday':
                from.setDate(from.getDate() - 1);
                to = new Date(from);
                break;
            case 'Last 7 Days':
                from.setDate(from.getDate() - 6);
                break;
            case 'Last 30 Days':
                from.setDate(from.getDate() - 29);
                break;
            case 'This Month':
                from = new Date(today.getFullYear(), today.getMonth(), 1);
                to = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'Last Month':
                from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                to = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
            case 'Year to Date':
                from = new Date(today.getFullYear(), 0, 1);
                break;
            case 'This Financial Year':
                // Financial year: April to March (India)
                if (today.getMonth() >= 3) {
                    from = new Date(today.getFullYear(), 3, 1);
                    to = new Date(today.getFullYear() + 1, 2, 31);
                } else {
                    from = new Date(today.getFullYear() - 1, 3, 1);
                    to = new Date(today.getFullYear(), 2, 31);
                }
                break;
            case 'Last Financial Year':
                if (today.getMonth() >= 3) {
                    from = new Date(today.getFullYear() - 1, 3, 1);
                    to = new Date(today.getFullYear(), 2, 31);
                } else {
                    from = new Date(today.getFullYear() - 2, 3, 1);
                    to = new Date(today.getFullYear() - 1, 2, 31);
                }
                break;
            default:
                from.setDate(from.getDate() - 6);
        }

        return { from: formatDate(from), to: formatDate(to) };
    }

    /**
     * Apply filter values to a specific item
     */
    applyFilterValuesToItem(item, filterKey, filterValues) {
        // Re-use the existing applyFilterValues logic but for a single item
        const tempContainer = document.createElement('div');
        tempContainer.appendChild(item.cloneNode(true));

        // Apply to the actual item
        const filterKeyWithPrefix = '::' + filterKey;
        const fromKey = filterKeyWithPrefix + '_from';
        const toKey = filterKeyWithPrefix + '_to';

        // Date range picker
        const dateRangePicker = item.querySelector('.dgc-datepicker[data-picker-type="range"], .dgc-datepicker[data-picker-type="main"]');
        if (dateRangePicker && filterValues[fromKey] && filterValues[toKey]) {
            dateRangePicker.dataset.from = filterValues[fromKey];
            dateRangePicker.dataset.to = filterValues[toKey];

            if (typeof $ !== 'undefined' && typeof moment !== 'undefined') {
                const $picker = $(dateRangePicker);
                const pickerInstance = $picker.data('daterangepicker');
                if (pickerInstance) {
                    const fromMoment = moment(filterValues[fromKey], 'YYYY-MM-DD');
                    const toMoment = moment(filterValues[toKey], 'YYYY-MM-DD');
                    if (fromMoment.isValid() && toMoment.isValid()) {
                        pickerInstance.setStartDate(fromMoment);
                        pickerInstance.setEndDate(toMoment);
                        $picker.val(fromMoment.format('DD-MM-YYYY') + ' - ' + toMoment.format('DD-MM-YYYY'));
                    }
                }
            }
            return;
        }

        // Other filter types - delegate to applyFilterValues
        const singleItemValues = {};
        Object.keys(filterValues).forEach(key => {
            singleItemValues[key] = filterValues[key];
        });
        this.applyFilterValues(singleItemValues, false);
    }

    /**
     * Validate required filters have values
     * @returns {Object} { valid: boolean, missing: string[] }
     */
    validateRequiredFilters() {
        const missing = [];
        const filterValues = this.getFilterValues();

        Object.keys(this.requiredFilters).forEach(filterKey => {
            const filter = this.requiredFilters[filterKey];
            const filterType = filter.filter_type;

            // Check for date range
            if (filterType === 'date_range' || filterType === 'main_datepicker') {
                const fromKey = '::' + filterKey + '_from';
                const toKey = '::' + filterKey + '_to';

                // Check if mode is 'block' - require user selection
                const defaultValue = filter.default_value;
                if (defaultValue && defaultValue.mode === 'block') {
                    if (!filterValues[fromKey] || !filterValues[toKey]) {
                        missing.push(filter.filter_label);
                    }
                }
            } else {
                const key = '::' + filterKey;
                const value = filterValues[key];

                if (!value || (Array.isArray(value) && value.length === 0)) {
                    missing.push(filter.filter_label);
                }
            }
        });

        return { valid: missing.length === 0, missing };
    }

    /**
     * Initialize Bar collapse/expand functionality
     */
    initBarCollapse() {
        console.log('[FilterView] initBarCollapse called, collapseBtn:', this.collapseBtn);
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
        this.collapseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('[FilterView] Collapse button clicked');
            const isCollapsed = this.container.classList.contains('collapsed');
            const newState = !isCollapsed;
            console.log('[FilterView] Current state:', isCollapsed, '-> New state:', newState);
            this.updateBarCollapseState(newState);
            localStorage.setItem(this.COLLAPSE_KEY, newState ? '1' : '0');
            console.log('[FilterView] Container classes:', this.container.className);
        });
    }

    /**
     * Update Bar collapse state
     */
    updateBarCollapseState(collapsed) {
        const filtersList = this.container.querySelector('.filters-list');

        if (collapsed) {
            this.container.classList.add('collapsed');
            if (this.collapseBtn) this.collapseBtn.title = 'Expand Filters';
            // Apply inline styles as fallback for CSS specificity issues
            if (filtersList) {
                filtersList.style.display = 'none';
            }
            this.container.style.display = 'inline-flex';
            this.container.style.width = 'auto';
        } else {
            this.container.classList.remove('collapsed');
            if (this.collapseBtn) this.collapseBtn.title = 'Collapse Filters';
            // Remove inline styles to restore CSS defaults
            if (filtersList) {
                filtersList.style.display = '';
            }
            this.container.style.display = '';
            this.container.style.width = '';
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

        // Skip filter listeners if no filters container
        if (!this.filtersContainer) return;

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
            // Validate required filters
            const validation = this.validateRequiredFilters();
            if (!validation.valid) {
                // Try to apply defaults for missing required filters
                this.applyRequiredFilterDefaults();

                // Re-validate after applying defaults
                const revalidation = this.validateRequiredFilters();
                if (!revalidation.valid) {
                    // Still invalid - show error
                    if (window.Toast) {
                        window.Toast.error(`Required filter(s) missing: ${revalidation.missing.join(', ')}`);
                    }
                    return;
                }
            }

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
        // Return empty object if no filters container (dashboard has no filters)
        if (!this.filtersContainer) {
            return {};
        }

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
        // Single select dropdown (new searchable dropdown)
        const selectDropdown = item.querySelector('.filter-select-dropdown input[type="hidden"].filter-input');
        if (selectDropdown && selectDropdown.value) {
            const result = {};
            result['::' + filterKey] = selectDropdown.value;
            return result;
        }

        // Single select (legacy select element)
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
     * Get all available option values for a multi-select filter
     * @param {HTMLElement} item - Filter item element
     * @returns {Array} Array of all option values
     */
    getAllOptionsForFilter(item) {
        const values = [];

        // Multi-select dropdown (checkboxes)
        const multiSelectOptions = item.querySelectorAll('.filter-multiselect-options input[type="checkbox"]');
        if (multiSelectOptions.length > 0) {
            multiSelectOptions.forEach(cb => {
                if (cb.value) values.push(cb.value);
            });
            return values;
        }

        // Checkbox group
        const checkboxOptions = item.querySelectorAll('.filter-checkbox-group input[type="checkbox"]');
        if (checkboxOptions.length > 0) {
            checkboxOptions.forEach(cb => {
                if (cb.value) values.push(cb.value);
            });
            return values;
        }

        return values;
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
            // Even without session, apply required filter defaults
            this.applyRequiredFilterDefaults();
            return;
        }

        if (typeof window.Ajax === 'undefined') {
            this.applyRequiredFilterDefaults();
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
                } else {
                    // No session values - apply defaults for required filters
                    this.applyRequiredFilterDefaults();
                }
            } else {
                // No session data - apply defaults for required filters
                this.applyRequiredFilterDefaults();
            }
        } catch (error) {
            console.warn(`${this.options.logPrefix} Failed to load filters:`, error);
            // On error, still apply required filter defaults
            this.applyRequiredFilterDefaults();
        }
    }

    /**
     * Apply saved filter values to the UI inputs
     * @param {Object} filterValues - Filter values to apply
     * @param {boolean} skipPickerUpdate - Skip updating picker display (for before initialization)
     */
    applyFilterValues(filterValues, skipPickerUpdate = false) {
        if (!filterValues || typeof filterValues !== 'object') return;
        if (!this.filtersContainer) return;

        const filterItems = this.filtersContainer.querySelectorAll('.filter-input-item');

        filterItems.forEach(item => {
            const filterKey = item.dataset.filterKey;
            if (!filterKey) return;

            const filterKeyWithPrefix = '::' + filterKey;
            const fromKey = filterKeyWithPrefix + '_from';
            const toKey = filterKeyWithPrefix + '_to';

            // Single select dropdown (new searchable dropdown)
            const selectDropdown = item.querySelector('.filter-select-dropdown');
            if (selectDropdown && filterValues[filterKeyWithPrefix]) {
                const hiddenInput = selectDropdown.querySelector('input[type="hidden"].filter-input');
                const radio = selectDropdown.querySelector(`input[type="radio"][value="${filterValues[filterKeyWithPrefix]}"]`);
                const trigger = selectDropdown.querySelector('.filter-select-trigger');
                const placeholder = trigger?.querySelector('.filter-select-placeholder');

                if (hiddenInput) {
                    hiddenInput.value = filterValues[filterKeyWithPrefix];
                }
                if (radio) {
                    radio.checked = true;
                    if (placeholder && radio.nextElementSibling) {
                        placeholder.textContent = radio.nextElementSibling.textContent;
                        // Add has-selection class when value is restored
                        if (filterValues[filterKeyWithPrefix]) {
                            placeholder.classList.add('has-selection');
                        }
                    }
                }
            }

            // Single select (legacy select element)
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
