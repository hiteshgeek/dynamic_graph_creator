/**
 * FilterRenderer - Centralized filter rendering and initialization component
 * Handles rendering of all filter types and initializes required libraries (daterangepicker, etc.)
 *
 * Usage:
 *   // Render filters into a container
 *   FilterRenderer.render(container, filtersArray);
 *
 *   // Get current filter values
 *   const values = FilterRenderer.getValues(container);
 *
 *   // Re-initialize pickers (after DOM changes)
 *   FilterRenderer.init(container);
 */

class FilterRenderer {
    /**
     * Render filter inputs into a container
     * @param {HTMLElement} container - Container element
     * @param {Array} filters - Array of filter objects
     * @param {Object} options - Rendering options
     * @param {boolean} options.showLabels - Show labels (default: true)
     * @param {boolean} options.compact - Use compact styling (default: false)
     * @param {string} options.wrapperClass - Additional wrapper class
     */
    static render(container, filters, options = {}) {
        if (!container) return;

        const showLabels = options.showLabels !== false;
        const compact = options.compact === true;
        const wrapperClass = options.wrapperClass || '';
        const noWrapper = options.noWrapper === true;
        const showPlaceholderKey = options.showPlaceholderKey !== false;
        const useControlWrapper = options.useControlWrapper !== false;

        if (!filters || filters.length === 0) {
            container.innerHTML = '';
            return;
        }

        let html = '';

        if (!noWrapper) {
            html += `<div class="filter-renderer ${wrapperClass} ${compact ? 'compact' : ''}">`;
        }

        filters.forEach(filter => {
            html += FilterRenderer.renderFilterInput(filter, { showLabels, compact, showPlaceholderKey, useControlWrapper });
        });

        if (!noWrapper) {
            html += '</div>';
        }

        container.innerHTML = html;

        // Initialize pickers after DOM is updated
        FilterRenderer.init(container);
    }

    /**
     * Render a single filter input
     * @param {Object} filter - Filter object with filter_key, filter_type, etc.
     * @param {Object} options - Rendering options
     * @returns {string} HTML string
     */
    static renderFilterInput(filter, options = {}) {
        const showLabels = options.showLabels !== false;
        const compact = options.compact === true;
        const showPlaceholderKey = options.showPlaceholderKey !== false;
        const useControlWrapper = options.useControlWrapper !== false;

        const key = filter.filter_key || '';
        const keyClean = key.replace(/^::/, '');
        const label = filter.filter_label || keyClean;
        const filterType = filter.filter_type || 'text';
        const rawDefaultValue = filter.default_value || '';
        const inputId = `filter-input-${keyClean}`;
        const isRequired = filter.is_required === 1 || filter.is_required === '1';

        // Parse JSON default value for required filters
        const defaultValue = FilterRenderer.resolveDefaultValue(rawDefaultValue, filterType);

        // Get options for select/checkbox/radio types
        const filterOptions = FilterRenderer.parseOptions(filter);

        // Get filter config
        const filterConfig = FilterRenderer.parseConfig(filter.filter_config);
        const isInline = filterConfig.inline === true;

        let inputHtml = '';

        switch (filterType) {
            case 'text':
                inputHtml = `<input type="text" class="form-control ${compact ? 'form-control-sm' : ''}"
                    id="${inputId}" data-filter-key="${keyClean}"
                    value="${FilterRenderer.escapeHtml(defaultValue)}"
                    placeholder="Enter value">`;
                break;

            case 'number':
                inputHtml = `<input type="number" class="form-control ${compact ? 'form-control-sm' : ''}"
                    id="${inputId}" data-filter-key="${keyClean}"
                    value="${FilterRenderer.escapeHtml(defaultValue)}"
                    placeholder="Enter number">`;
                break;

            case 'date':
                inputHtml = `<input type="text" class="form-control form-control-sm filter-input dgc-datepicker"
                    id="${inputId}" name="${keyClean}" data-filter-key="${keyClean}"
                    data-picker-type="single"
                    value="${FilterRenderer.escapeHtml(defaultValue)}"
                    placeholder="Select date" autocomplete="off">`;
                break;

            case 'date_range':
                inputHtml = `<input type="text" class="form-control form-control-sm filter-input dgc-datepicker"
                    id="${inputId}" name="${keyClean}" data-filter-key="${keyClean}"
                    data-picker-type="range"
                    placeholder="Select date range" autocomplete="off">`;
                break;

            case 'main_datepicker':
                inputHtml = `<input type="text" class="form-control form-control-sm filter-input dgc-datepicker"
                    id="${inputId}" name="${keyClean}" data-filter-key="${keyClean}"
                    data-picker-type="main"
                    placeholder="Select date range" autocomplete="off">`;
                break;

            case 'select':
                inputHtml = FilterRenderer.renderSelectInput(keyClean, inputId, filterOptions, defaultValue, compact);
                break;

            case 'multi_select':
                inputHtml = FilterRenderer.renderMultiSelectInput(keyClean, inputId, filterOptions, defaultValue, compact);
                break;

            case 'checkbox':
                inputHtml = FilterRenderer.renderCheckboxInput(keyClean, filterOptions, defaultValue, isInline);
                break;

            case 'radio':
                inputHtml = FilterRenderer.renderRadioInput(keyClean, filterOptions, defaultValue, isInline);
                break;

            case 'tokeninput':
                inputHtml = FilterRenderer.renderTokenInput(keyClean, inputId, filterOptions, defaultValue, compact);
                break;

            default:
                inputHtml = `<input type="text" class="form-control ${compact ? 'form-control-sm' : ''}"
                    id="${inputId}" data-filter-key="${keyClean}"
                    value="${FilterRenderer.escapeHtml(defaultValue)}"
                    placeholder="Enter value">`;
        }

        const labelHtml = showLabels ? `
            <label class="filter-input-label" for="${inputId}">
                ${FilterRenderer.escapeHtml(label)}
                ${isRequired ? '<span class="required">*</span>' : ''}
            </label>
        ` : '';

        const placeholderHtml = showLabels && showPlaceholderKey ? `
            <code class="placeholder-key" title="Use in query">::${keyClean}</code>
        ` : '';

        const inputContent = useControlWrapper
            ? `<div class="filter-input-control">${inputHtml}</div>`
            : inputHtml;

        return `
            <div class="filter-input-item" data-filter-key="${keyClean}" data-filter-type="${filterType}">
                <div class="filter-input-header">
                    ${labelHtml}
                    ${placeholderHtml}
                </div>
                ${inputContent}
            </div>
        `;
    }

    /**
     * Render select input (searchable dropdown)
     */
    static renderSelectInput(keyClean, inputId, options, defaultValue, compact) {
        const selectedOption = options.find(opt => {
            const value = opt.value !== undefined ? opt.value : opt;
            return value == defaultValue;
        });

        const selectedLabel = selectedOption
            ? (selectedOption.label !== undefined ? selectedOption.label : selectedOption.value || selectedOption)
            : '-- Select --';

        const optionsHtml = options.map((opt, index) => {
            const value = opt.value !== undefined ? opt.value : opt;
            const label = opt.label !== undefined ? opt.label : value;
            const isSelected = value == defaultValue;
            const optId = `select-${keyClean}-${index}`;

            return `
                <div class="dropdown-item filter-select-option" data-value="${FilterRenderer.escapeHtml(value)}">
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                            name="${keyClean}"
                            value="${FilterRenderer.escapeHtml(value)}"
                            id="${optId}"
                            ${isSelected ? 'checked' : ''}>
                        <label class="form-check-label" for="${optId}">${FilterRenderer.escapeHtml(label)}</label>
                    </div>
                </div>
            `;
        }).join('');

        return `
            <div class="dropdown filter-select-dropdown" data-filter-name="${keyClean}">
                <button class="btn btn-outline-secondary dropdown-toggle filter-select-trigger ${compact ? 'btn-sm' : ''}"
                    type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <span class="filter-select-placeholder">${FilterRenderer.escapeHtml(selectedLabel)}</span>
                </button>
                <div class="dropdown-menu filter-select-options">
                    <div class="filter-select-header">
                        <input type="text" class="form-control form-control-sm select-search" placeholder="Search...">
                    </div>
                    <div class="dropdown-item filter-select-option" data-value="">
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="${keyClean}"
                                value=""
                                id="select-${keyClean}-none"
                                ${!defaultValue ? 'checked' : ''}>
                            <label class="form-check-label" for="select-${keyClean}-none">-- Select --</label>
                        </div>
                    </div>
                    ${optionsHtml}
                </div>
                <input type="hidden" class="filter-input" id="${inputId}" name="${keyClean}"
                    data-filter-key="${keyClean}" value="${FilterRenderer.escapeHtml(defaultValue)}">
            </div>
        `;
    }

    /**
     * Render multi-select input (dropdown with checkboxes)
     */
    static renderMultiSelectInput(keyClean, inputId, options, defaultValue, compact) {
        const defaultValues = Array.isArray(defaultValue) ? defaultValue :
            (defaultValue ? defaultValue.split(',') : []);

        const optionsHtml = options.map((opt, index) => {
            const value = opt.value !== undefined ? opt.value : opt;
            const label = opt.label !== undefined ? opt.label : value;
            const isSelected = opt.is_selected || defaultValues.includes(String(value));
            const optId = `multiselect-${keyClean}-${index}`;

            return `
                <div class="dropdown-item filter-multiselect-option">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                            name="${keyClean}[]"
                            value="${FilterRenderer.escapeHtml(value)}"
                            id="${optId}"
                            ${isSelected ? 'checked' : ''}>
                        <label class="form-check-label" for="${optId}">${FilterRenderer.escapeHtml(label)}</label>
                    </div>
                </div>
            `;
        }).join('');

        return `
            <div class="dropdown filter-multiselect-dropdown" data-filter-name="${keyClean}">
                <button class="btn btn-outline-secondary dropdown-toggle filter-multiselect-trigger ${compact ? 'btn-sm' : ''}"
                    type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <span class="filter-multiselect-placeholder">-- Select multiple --</span>
                </button>
                <div class="dropdown-menu filter-multiselect-options">
                    <div class="filter-multiselect-header">
                        <div class="filter-multiselect-actions">
                            <button type="button" class="btn btn-link btn-sm multiselect-select-all">All</button>
                            <span class="filter-multiselect-divider">|</span>
                            <button type="button" class="btn btn-link btn-sm multiselect-select-none">None</button>
                        </div>
                        <input type="text" class="form-control form-control-sm multiselect-search" placeholder="Search...">
                    </div>
                    ${optionsHtml}
                </div>
            </div>
        `;
    }

    /**
     * Render checkbox group
     */
    static renderCheckboxInput(keyClean, options, defaultValue, isInline) {
        const defaultValues = Array.isArray(defaultValue) ? defaultValue :
            (defaultValue ? defaultValue.split(',') : []);

        const optionsHtml = options.map((opt, index) => {
            const value = opt.value !== undefined ? opt.value : opt;
            const label = opt.label !== undefined ? opt.label : value;
            const isSelected = opt.is_selected || defaultValues.includes(String(value));
            const optId = `checkbox-${keyClean}-${index}`;

            return `
                <div class="form-check ${isInline ? 'form-check-inline' : ''}">
                    <input class="form-check-input" type="checkbox"
                        name="${keyClean}[]"
                        value="${FilterRenderer.escapeHtml(value)}"
                        id="${optId}"
                        ${isSelected ? 'checked' : ''}>
                    <label class="form-check-label" for="${optId}">${FilterRenderer.escapeHtml(label)}</label>
                </div>
            `;
        }).join('');

        return `<div class="filter-checkbox-group ${isInline ? 'inline' : ''}">${optionsHtml}</div>`;
    }

    /**
     * Render radio group
     */
    static renderRadioInput(keyClean, options, defaultValue, isInline) {
        const optionsHtml = options.map((opt, index) => {
            const value = opt.value !== undefined ? opt.value : opt;
            const label = opt.label !== undefined ? opt.label : value;
            const isSelected = opt.is_selected || value == defaultValue;
            const optId = `radio-${keyClean}-${index}`;

            return `
                <div class="form-check ${isInline ? 'form-check-inline' : ''}">
                    <input class="form-check-input" type="radio"
                        name="${keyClean}"
                        value="${FilterRenderer.escapeHtml(value)}"
                        id="${optId}"
                        ${isSelected ? 'checked' : ''}>
                    <label class="form-check-label" for="${optId}">${FilterRenderer.escapeHtml(label)}</label>
                </div>
            `;
        }).join('');

        return `<div class="filter-radio-group ${isInline ? 'inline' : ''}">${optionsHtml}</div>`;
    }

    /**
     * Render tokeninput
     */
    static renderTokenInput(keyClean, inputId, options, defaultValue, compact) {
        // Token input renders as a text input that will be enhanced by tokeninput library
        return `
            <input type="text" class="form-control ${compact ? 'form-control-sm' : ''} dgc-tokeninput"
                id="${inputId}" data-filter-key="${keyClean}"
                data-options='${JSON.stringify(options)}'
                value="${FilterRenderer.escapeHtml(defaultValue)}"
                placeholder="Type to search...">
        `;
    }

    /**
     * Initialize all filter components (date pickers, dropdowns, etc.)
     * Main entry point for filter initialization
     * @param {HTMLElement} container - Container to search for filter components
     */
    static init(container) {
        if (!container) return;

        // Initialize date pickers - check both local and global scope
        const DatePickerClass = (typeof DatePickerInit !== 'undefined') ? DatePickerInit : window.DatePickerInit;

        if (DatePickerClass) {
            DatePickerClass.init(container);
        } else if (typeof $ !== 'undefined' && typeof $.fn.daterangepicker !== 'undefined') {
            // Fallback: manually init daterangepickers if DatePickerInit not available
            FilterRenderer.initDatePickersFallback(container);
        }

        // Initialize single select dropdowns
        FilterRenderer.initSelects(container);

        // Initialize multi-select dropdowns
        FilterRenderer.initMultiSelects(container);

        // Initialize tokeninput if available
        FilterRenderer.initTokenInputs(container);
    }

    /**
     * Alias for init() - kept for backward compatibility
     * @param {HTMLElement} container - Container to search for pickers
     */
    static initPickers(container) {
        FilterRenderer.init(container);
    }

    /**
     * Fallback date picker initialization
     */
    static initDatePickersFallback(container) {
        const DISPLAY_FORMAT = 'DD-MM-YYYY';
        const DATA_FORMAT = 'YYYY-MM-DD';

        const pickers = container.querySelectorAll('.dgc-datepicker');
        pickers.forEach(input => {
            if (input.dataset.daterangepickerInit === 'true') return;
            // Skip hidden elements - daterangepicker doesn't work well on hidden inputs
            if (input.offsetParent === null) return;
            const filterItem = input.closest('.filter-input-item');
            if (filterItem && filterItem.style.display === 'none') return;

            const pickerType = input.dataset.pickerType || 'single';
            const $input = $(input);

            if (pickerType === 'single') {
                $input.daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    autoUpdateInput: false,
                    locale: { format: DISPLAY_FORMAT, cancelLabel: 'Clear' }
                });

                $input.on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format(DISPLAY_FORMAT));
                    this.dataset.value = picker.startDate.format(DATA_FORMAT);
                    this.dispatchEvent(new Event('change', { bubbles: true }));
                });
            } else {
                // Range or main picker
                const options = {
                    autoUpdateInput: false,
                    startDate: moment().subtract(6, 'days'),
                    endDate: moment(),
                    locale: { format: DISPLAY_FORMAT, separator: ' - ', cancelLabel: 'Clear' },
                    alwaysShowCalendars: true,
                    opens: 'left'
                };

                // Add preset ranges for main picker
                if (pickerType === 'main') {
                    options.ranges = {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    };
                }

                $input.daterangepicker(options);

                $input.on('apply.daterangepicker', function(ev, picker) {
                    const startDateDisplay = picker.startDate.format(DISPLAY_FORMAT);
                    const endDateDisplay = picker.endDate.format(DISPLAY_FORMAT);
                    const startDateData = picker.startDate.format(DATA_FORMAT);
                    const endDateData = picker.endDate.format(DATA_FORMAT);
                    $(this).val(startDateDisplay + ' - ' + endDateDisplay);
                    this.dataset.from = startDateData;
                    this.dataset.to = endDateData;
                    this.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }

            $input.on('cancel.daterangepicker', function() {
                $(this).val('');
                delete this.dataset.value;
                delete this.dataset.from;
                delete this.dataset.to;
                this.dispatchEvent(new Event('change', { bubbles: true }));
            });

            input.dataset.daterangepickerInit = 'true';
        });
    }

    /**
     * Initialize single select dropdowns
     */
    static initSelects(container) {
        const dropdowns = container.querySelectorAll('.filter-select-dropdown');

        dropdowns.forEach(dropdown => {
            if (dropdown.dataset.selectInit === 'true') return;

            const trigger = dropdown.querySelector('.filter-select-trigger');
            const radios = dropdown.querySelectorAll('.form-check-input[type="radio"]');
            const search = dropdown.querySelector('.select-search');
            const hiddenInput = dropdown.querySelector('input[type="hidden"].filter-input');

            // Update placeholder text and hidden input value
            const updateSelection = (selectedRadio) => {
                const placeholder = trigger.querySelector('.filter-select-placeholder');
                const label = selectedRadio ? selectedRadio.nextElementSibling.textContent : '-- Select --';
                const value = selectedRadio ? selectedRadio.value : '';

                placeholder.textContent = label;

                // Add/remove has-selection class based on whether a value is selected
                if (value) {
                    placeholder.classList.add('has-selection');
                } else {
                    placeholder.classList.remove('has-selection');
                }

                if (hiddenInput) {
                    hiddenInput.value = value;
                    // Trigger change event for FilterView listeners
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };

            // Radio change
            radios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.checked) {
                        updateSelection(radio);
                        // Close dropdown after selection
                        const bsDropdown = bootstrap.Dropdown.getInstance(trigger);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }
                });
            });

            // Search filter
            if (search) {
                search.addEventListener('input', () => {
                    const term = search.value.toLowerCase();
                    dropdown.querySelectorAll('.filter-select-option').forEach(opt => {
                        const label = opt.querySelector('.form-check-label').textContent.toLowerCase();
                        opt.style.display = label.includes(term) ? '' : 'none';
                    });
                });

                // Clear search when dropdown opens
                trigger.addEventListener('shown.bs.dropdown', () => {
                    search.value = '';
                    dropdown.querySelectorAll('.filter-select-option').forEach(opt => {
                        opt.style.display = '';
                    });
                    search.focus();
                });
            }

            dropdown.dataset.selectInit = 'true';
        });
    }

    /**
     * Initialize multi-select dropdowns
     */
    static initMultiSelects(container) {
        const dropdowns = container.querySelectorAll('.filter-multiselect-dropdown');

        dropdowns.forEach(dropdown => {
            if (dropdown.dataset.multiSelectInit === 'true') return;

            const trigger = dropdown.querySelector('.filter-multiselect-trigger');
            const checkboxes = dropdown.querySelectorAll('.form-check-input');
            const selectAll = dropdown.querySelector('.multiselect-select-all');
            const selectNone = dropdown.querySelector('.multiselect-select-none');
            const search = dropdown.querySelector('.multiselect-search');

            // Update placeholder text
            const updatePlaceholder = () => {
                const checked = dropdown.querySelectorAll('.form-check-input:checked');
                const placeholder = trigger.querySelector('.filter-multiselect-placeholder');
                if (checked.length === 0) {
                    placeholder.textContent = '-- Select multiple --';
                } else if (checked.length === 1) {
                    placeholder.textContent = checked[0].nextElementSibling.textContent;
                } else {
                    placeholder.textContent = `${checked.length} selected`;
                }
            };

            // Checkbox change
            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    updatePlaceholder();
                    // Dispatch change event on dropdown for external listeners
                    dropdown.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });

            // Select all
            if (selectAll) {
                selectAll.addEventListener('click', (e) => {
                    e.preventDefault();
                    checkboxes.forEach(cb => { cb.checked = true; });
                    updatePlaceholder();
                    dropdown.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }

            // Select none
            if (selectNone) {
                selectNone.addEventListener('click', (e) => {
                    e.preventDefault();
                    checkboxes.forEach(cb => { cb.checked = false; });
                    updatePlaceholder();
                    dropdown.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }

            // Search filter
            if (search) {
                search.addEventListener('input', () => {
                    const term = search.value.toLowerCase();
                    dropdown.querySelectorAll('.filter-multiselect-option').forEach(opt => {
                        const label = opt.querySelector('.form-check-label').textContent.toLowerCase();
                        opt.style.display = label.includes(term) ? '' : 'none';
                    });
                });
            }

            // Initial state
            updatePlaceholder();

            dropdown.dataset.multiSelectInit = 'true';
        });
    }

    /**
     * Initialize tokeninput fields
     */
    static initTokenInputs(container) {
        // Only if tokeninput library is available
        if (typeof $ === 'undefined' || typeof $.fn.tokenInput === 'undefined') return;

        const inputs = container.querySelectorAll('.dgc-tokeninput');
        inputs.forEach(input => {
            if (input.dataset.tokenInputInit === 'true') return;

            let options = [];
            try {
                options = JSON.parse(input.dataset.options || '[]');
            } catch (e) {}

            $(input).tokenInput(options, {
                theme: 'facebook',
                preventDuplicates: true,
                onAdd: () => input.dispatchEvent(new Event('change', { bubbles: true })),
                onDelete: () => input.dispatchEvent(new Event('change', { bubbles: true }))
            });

            input.dataset.tokenInputInit = 'true';
        });
    }

    /**
     * Get filter values from a container
     * @param {HTMLElement} container - Container with filter inputs
     * @returns {Object} Object with filter values keyed by filter key
     */
    static getValues(container) {
        if (!container) return {};

        const values = {};

        // Text, number, select inputs
        container.querySelectorAll('input[data-filter-key], select[data-filter-key]').forEach(input => {
            const key = '::' + input.dataset.filterKey;

            if (input.classList.contains('dgc-datepicker')) {
                // Date picker - check for range values
                if (input.dataset.from && input.dataset.to) {
                    values[key] = {
                        from: input.dataset.from,
                        to: input.dataset.to
                    };
                } else if (input.value) {
                    values[key] = input.value;
                }
            } else if (input.tagName === 'SELECT' && input.multiple) {
                // Multi-select
                values[key] = Array.from(input.selectedOptions).map(o => o.value);
            } else {
                values[key] = input.value;
            }
        });

        // Checkbox groups
        container.querySelectorAll('.filter-checkbox-group').forEach(group => {
            const item = group.closest('.filter-input-item');
            if (!item) return;
            const key = '::' + item.dataset.filterKey;
            values[key] = Array.from(group.querySelectorAll('input:checked')).map(cb => cb.value);
        });

        // Radio groups
        container.querySelectorAll('.filter-radio-group').forEach(group => {
            const item = group.closest('.filter-input-item');
            if (!item) return;
            const key = '::' + item.dataset.filterKey;
            const checked = group.querySelector('input:checked');
            values[key] = checked ? checked.value : '';
        });

        // Multi-select dropdowns
        container.querySelectorAll('.filter-multiselect-dropdown').forEach(dropdown => {
            const item = dropdown.closest('.filter-input-item');
            if (!item) return;
            const key = '::' + item.dataset.filterKey;
            values[key] = Array.from(dropdown.querySelectorAll('input:checked')).map(cb => cb.value);
        });

        return values;
    }

    /**
     * Set filter values in a container
     * @param {HTMLElement} container - Container with filter inputs
     * @param {Object} values - Object with filter values keyed by filter key
     */
    static setValues(container, values) {
        if (!container || !values) return;

        Object.entries(values).forEach(([key, value]) => {
            const keyClean = key.replace(/^::/, '');
            const item = container.querySelector(`[data-filter-key="${keyClean}"]`);
            if (!item) return;

            const filterType = item.closest('.filter-input-item')?.dataset.filterType;

            if (item.classList.contains('dgc-datepicker')) {
                // Date picker
                if (typeof value === 'object' && value.from && value.to) {
                    item.value = `${value.from} - ${value.to}`;
                    item.dataset.from = value.from;
                    item.dataset.to = value.to;
                } else {
                    item.value = value || '';
                }
            } else if (item.tagName === 'SELECT') {
                item.value = value || '';
            } else if (item.tagName === 'INPUT') {
                item.value = value || '';
            }
        });

        // Handle checkbox groups
        Object.entries(values).forEach(([key, value]) => {
            if (!Array.isArray(value)) return;

            const keyClean = key.replace(/^::/, '');
            const itemContainer = container.querySelector(`.filter-input-item[data-filter-key="${keyClean}"]`);
            if (!itemContainer) return;

            // Checkboxes
            const checkboxes = itemContainer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => {
                cb.checked = value.includes(cb.value);
            });

            // Update multi-select placeholder if present
            const dropdown = itemContainer.querySelector('.filter-multiselect-dropdown');
            if (dropdown) {
                const trigger = dropdown.querySelector('.filter-multiselect-trigger');
                const placeholder = trigger?.querySelector('.filter-multiselect-placeholder');
                if (placeholder) {
                    const checked = dropdown.querySelectorAll('.form-check-input:checked');
                    if (checked.length === 0) {
                        placeholder.textContent = '-- Select multiple --';
                    } else if (checked.length === 1) {
                        placeholder.textContent = checked[0].nextElementSibling.textContent;
                    } else {
                        placeholder.textContent = `${checked.length} selected`;
                    }
                }
            }
        });

        // Handle radio groups
        Object.entries(values).forEach(([key, value]) => {
            if (Array.isArray(value)) return;

            const keyClean = key.replace(/^::/, '');
            const itemContainer = container.querySelector(`.filter-input-item[data-filter-key="${keyClean}"]`);
            if (!itemContainer) return;

            const radios = itemContainer.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => {
                radio.checked = radio.value === value;
            });
        });
    }

    /**
     * Parse filter options from various formats
     */
    static parseOptions(filter) {
        // Options might be in filter.options (from toArray) or need to be parsed
        if (filter.options && Array.isArray(filter.options)) {
            return filter.options;
        }

        // Try parsing static_options
        if (filter.static_options) {
            try {
                const parsed = JSON.parse(filter.static_options);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {}
        }

        // Try parsing filter_options
        if (filter.filter_options) {
            try {
                const parsed = JSON.parse(filter.filter_options);
                return Array.isArray(parsed) ? parsed : (parsed.options || []);
            } catch (e) {}
        }

        return [];
    }

    /**
     * Parse filter config
     */
    static parseConfig(configStr) {
        if (!configStr) return {};
        try {
            return JSON.parse(configStr);
        } catch (e) {
            return {};
        }
    }

    /**
     * Resolve default value from JSON structure
     * @param {string} rawDefaultValue - Raw default value (may be JSON or plain string)
     * @param {string} filterType - Filter type
     * @returns {string|Array} Resolved default value for use in input
     */
    static resolveDefaultValue(rawDefaultValue, filterType) {
        if (!rawDefaultValue) return '';

        // Try to parse as JSON
        let parsed = null;
        try {
            parsed = JSON.parse(rawDefaultValue);
        } catch (e) {
            // Not JSON, return as-is (legacy plain string)
            return rawDefaultValue;
        }

        if (!parsed || typeof parsed !== 'object') {
            return rawDefaultValue;
        }

        // Extract value based on filter type
        switch (filterType) {
            case 'date_range':
            case 'main_datepicker':
                // For date range, return empty (DatePickerInit will handle from data attributes)
                // The actual default should be applied via FilterView or separately
                return '';

            case 'multi_select':
            case 'checkbox':
            case 'tokeninput':
                // Return array of values
                return parsed.values || [];

            case 'text':
            case 'number':
            case 'date':
            case 'select':
            case 'radio':
            default:
                // Return single value
                return parsed.value || '';
        }
    }

    /**
     * Escape HTML entities
     */
    static escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Show specific filter inputs (by key)
     * @param {HTMLElement} container - Container element
     * @param {Array} keys - Array of filter keys to show (without ::)
     */
    static showFilters(container, keys) {
        if (!container) return;

        container.querySelectorAll('.filter-input-item').forEach(item => {
            const key = item.dataset.filterKey;
            item.style.display = keys.includes(key) ? '' : 'none';
        });

        // Re-init pickers for newly visible filters
        FilterRenderer.init(container);
    }

    /**
     * Hide all filter inputs
     */
    static hideAllFilters(container) {
        if (!container) return;
        container.querySelectorAll('.filter-input-item').forEach(item => {
            item.style.display = 'none';
        });
    }

    /**
     * Destroy picker instances (cleanup)
     */
    static destroyPickers(container) {
        if (!container) return;

        // Destroy daterangepickers
        if (typeof $ !== 'undefined' && typeof $.fn.daterangepicker !== 'undefined') {
            container.querySelectorAll('.dgc-datepicker').forEach(input => {
                const $input = $(input);
                if ($input.data('daterangepicker')) {
                    $input.data('daterangepicker').remove();
                    input.dataset.daterangepickerInit = 'false';
                }
            });
        }

        // Reset multi-select init flags
        container.querySelectorAll('.filter-multiselect-dropdown').forEach(dropdown => {
            dropdown.dataset.multiSelectInit = 'false';
        });
    }
}

// Export for module bundler
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FilterRenderer;
}
