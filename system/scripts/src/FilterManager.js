/**
 * FilterManager - Generic filter management component
 * Can be used for graphs, reports, or any filterable entity
 */

export default class FilterManager {
    constructor(container, options = {}) {
        this.container = container;
        this.entityType = options.entityType || 'graph';
        this.entityId = options.entityId || null;
        this.onChange = options.onChange || (() => {});

        this.filters = [];
        this.filterTypes = [
            { value: 'text', label: 'Text' },
            { value: 'number', label: 'Number' },
            { value: 'date', label: 'Date' },
            { value: 'date_range', label: 'Date Range' },
            { value: 'select', label: 'Select' },
            { value: 'multi_select', label: 'Multi Select' }
        ];
    }

    /**
     * Initialize filter manager
     */
    init() {
        this.render();
    }

    /**
     * Set filters from saved data
     */
    setFilters(filters) {
        this.filters = filters || [];
        this.render();
    }

    /**
     * Get all filters
     */
    getFilters() {
        return this.filters;
    }

    /**
     * Add a new filter
     */
    addFilter(filterData = null) {
        const newFilter = filterData || {
            filter_key: '',
            filter_label: '',
            filter_type: 'text',
            filter_options: '',
            default_value: '',
            is_required: 0,
            sequence: this.filters.length
        };

        this.filters.push(newFilter);
        this.render();
        this.onChange();
    }

    /**
     * Remove a filter by index
     */
    removeFilter(index) {
        this.filters.splice(index, 1);
        this.render();
        this.onChange();
    }

    /**
     * Update a filter property
     */
    updateFilter(index, property, value) {
        if (this.filters[index]) {
            this.filters[index][property] = value;
            this.onChange();
        }
    }

    /**
     * Render the filter manager UI
     */
    render() {
        let html = `
            <div class="filter-manager-header">
                <h4><i class="fas fa-filter"></i> Filters</h4>
            </div>
        `;

        if (this.filters.length === 0) {
            html += `
                <div class="filter-empty">
                    <p>No filters defined</p>
                    <p class="text-small text-muted">
                        Add filters to make your query dynamic
                    </p>
                </div>
            `;
        } else {
            html += `<div class="filter-list">`;
            this.filters.forEach((filter, index) => {
                html += this.renderFilterItem(filter, index);
            });
            html += `</div>`;
        }

        html += `
            <button type="button" class="filter-add-btn" data-action="add">
                <i class="fas fa-plus"></i> Add Filter
            </button>
        `;

        this.container.innerHTML = html;
        this.bindEvents();
    }

    /**
     * Render a single filter item
     */
    renderFilterItem(filter, index) {
        const showOptions = filter.filter_type === 'select' ||
                           filter.filter_type === 'multi_select';

        return `
            <div class="filter-item" data-index="${index}">
                <div class="filter-item-field">
                    <label>Key</label>
                    <input
                        type="text"
                        value="${filter.filter_key || ''}"
                        data-field="filter_key"
                        placeholder=":placeholder"
                    >
                </div>
                <div class="filter-item-field">
                    <label>Label</label>
                    <input
                        type="text"
                        value="${filter.filter_label || ''}"
                        data-field="filter_label"
                        placeholder="Display label"
                    >
                </div>
                <div class="filter-item-field">
                    <label>Type</label>
                    <select data-field="filter_type">
                        ${this.filterTypes.map(t => `
                            <option
                                value="${t.value}"
                                ${filter.filter_type === t.value ? 'selected' : ''}
                            >${t.label}</option>
                        `).join('')}
                    </select>
                </div>
                <div class="filter-item-field">
                    <label>Default</label>
                    <input
                        type="text"
                        value="${filter.default_value || ''}"
                        data-field="default_value"
                        placeholder="Default value"
                    >
                </div>
                <button
                    type="button"
                    class="filter-remove-btn"
                    data-action="remove"
                    title="Remove filter"
                >
                    <i class="fas fa-trash"></i>
                </button>
                ${showOptions ? this.renderOptionsEditor(filter, index) : ''}
            </div>
        `;
    }

    /**
     * Render options editor for select types
     */
    renderOptionsEditor(filter, index) {
        let options = [];
        try {
            options = filter.filter_options ?
                JSON.parse(filter.filter_options) : [];
        } catch (e) {
            options = [];
        }

        if (!Array.isArray(options)) {
            options = options.options || [];
        }

        return `
            <div class="filter-options-editor" style="grid-column: 1 / -1;">
                <label class="text-small text-muted">Options (value:label)</label>
                <div class="filter-options-list" data-index="${index}">
                    ${options.map((opt, optIndex) => `
                        <div class="filter-option-item">
                            <input
                                type="text"
                                value="${opt.value || ''}"
                                placeholder="Value"
                                data-opt-index="${optIndex}"
                                data-opt-field="value"
                            >
                            <input
                                type="text"
                                value="${opt.label || ''}"
                                placeholder="Label"
                                data-opt-index="${optIndex}"
                                data-opt-field="label"
                            >
                            <button
                                type="button"
                                data-action="remove-option"
                                data-opt-index="${optIndex}"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <div class="filter-option-add">
                    <input
                        type="text"
                        placeholder="New value"
                        class="new-option-value"
                    >
                    <input
                        type="text"
                        placeholder="New label"
                        class="new-option-label"
                    >
                    <button
                        type="button"
                        class="btn btn-sm btn-secondary"
                        data-action="add-option"
                    >
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
        // Add filter button
        this.container.querySelector('[data-action="add"]')
            ?.addEventListener('click', () => this.addFilter());

        // Remove filter buttons
        this.container.querySelectorAll('[data-action="remove"]')
            .forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const item = e.target.closest('.filter-item');
                    const index = parseInt(item.dataset.index);
                    this.removeFilter(index);
                });
            });

        // Field inputs
        this.container.querySelectorAll('.filter-item input, .filter-item select')
            .forEach(input => {
                if (input.dataset.field) {
                    input.addEventListener('change', (e) => {
                        const item = e.target.closest('.filter-item');
                        const index = parseInt(item.dataset.index);
                        const field = e.target.dataset.field;

                        this.updateFilter(index, field, e.target.value);

                        // Re-render if type changed to show/hide options
                        if (field === 'filter_type') {
                            this.render();
                        }
                    });
                }
            });

        // Option editors
        this.bindOptionEvents();
    }

    /**
     * Bind option editor events
     */
    bindOptionEvents() {
        // Add option buttons
        this.container.querySelectorAll('[data-action="add-option"]')
            .forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const editor = e.target.closest('.filter-options-editor');
                    const item = e.target.closest('.filter-item');
                    const index = parseInt(item.dataset.index);

                    const valueInput = editor.querySelector('.new-option-value');
                    const labelInput = editor.querySelector('.new-option-label');

                    if (valueInput.value.trim()) {
                        this.addOption(index, {
                            value: valueInput.value.trim(),
                            label: labelInput.value.trim() || valueInput.value.trim()
                        });
                        valueInput.value = '';
                        labelInput.value = '';
                    }
                });
            });

        // Remove option buttons
        this.container.querySelectorAll('[data-action="remove-option"]')
            .forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const item = e.target.closest('.filter-item');
                    const index = parseInt(item.dataset.index);
                    const optIndex = parseInt(e.target.closest('button').dataset.optIndex);
                    this.removeOption(index, optIndex);
                });
            });

        // Option field changes
        this.container.querySelectorAll('[data-opt-field]')
            .forEach(input => {
                input.addEventListener('change', (e) => {
                    const item = e.target.closest('.filter-item');
                    const index = parseInt(item.dataset.index);
                    const optIndex = parseInt(e.target.dataset.optIndex);
                    const field = e.target.dataset.optField;
                    this.updateOption(index, optIndex, field, e.target.value);
                });
            });
    }

    /**
     * Add an option to a filter
     */
    addOption(filterIndex, option) {
        let options = this.getFilterOptions(filterIndex);
        options.push(option);
        this.setFilterOptions(filterIndex, options);
        this.render();
    }

    /**
     * Remove an option from a filter
     */
    removeOption(filterIndex, optionIndex) {
        let options = this.getFilterOptions(filterIndex);
        options.splice(optionIndex, 1);
        this.setFilterOptions(filterIndex, options);
        this.render();
    }

    /**
     * Update an option
     */
    updateOption(filterIndex, optionIndex, field, value) {
        let options = this.getFilterOptions(filterIndex);
        if (options[optionIndex]) {
            options[optionIndex][field] = value;
            this.setFilterOptions(filterIndex, options);
        }
    }

    /**
     * Get filter options as array
     */
    getFilterOptions(index) {
        try {
            const raw = this.filters[index].filter_options;
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : (parsed.options || []);
        } catch (e) {
            return [];
        }
    }

    /**
     * Set filter options
     */
    setFilterOptions(index, options) {
        this.filters[index].filter_options = JSON.stringify(options);
        this.onChange();
    }

    /**
     * Render filter inputs for viewing/applying (used in view page)
     */
    static renderFilterInputs(container, filters) {
        if (!filters || filters.length === 0) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="filter-inputs">';

        filters.forEach(filter => {
            html += FilterManager.renderFilterInput(filter);
        });

        html += `
            <button type="button" class="btn btn-primary filter-apply-btn">
                <i class="fas fa-check"></i> Apply Filters
            </button>
        </div>`;

        container.innerHTML = html;
    }

    /**
     * Render a single filter input
     */
    static renderFilterInput(filter) {
        const key = filter.filter_key;
        const label = filter.filter_label || key;
        const defaultVal = filter.default_value || '';

        let inputHtml = '';

        switch (filter.filter_type) {
            case 'date':
                inputHtml = `<input type="date" data-filter-key="${key}" value="${defaultVal}">`;
                break;

            case 'date_range':
                inputHtml = `
                    <div class="filter-input-group-date-range">
                        <input type="date" data-filter-key="${key}_from" placeholder="From">
                        <input type="date" data-filter-key="${key}_to" placeholder="To">
                    </div>
                `;
                break;

            case 'number':
                inputHtml = `<input type="number" data-filter-key="${key}" value="${defaultVal}">`;
                break;

            case 'select':
            case 'multi_select':
                const options = FilterManager.parseOptions(filter.filter_options);
                const multiple = filter.filter_type === 'multi_select' ? 'multiple' : '';
                inputHtml = `
                    <select data-filter-key="${key}" ${multiple}>
                        <option value="">-- Select --</option>
                        ${options.map(o => `
                            <option value="${o.value}">${o.label}</option>
                        `).join('')}
                    </select>
                `;
                break;

            default:
                inputHtml = `<input type="text" data-filter-key="${key}" value="${defaultVal}">`;
        }

        return `
            <div class="filter-input-group">
                <label>${label}</label>
                ${inputHtml}
            </div>
        `;
    }

    /**
     * Parse filter options JSON
     */
    static parseOptions(optionsJson) {
        try {
            const parsed = optionsJson ? JSON.parse(optionsJson) : [];
            return Array.isArray(parsed) ? parsed : (parsed.options || []);
        } catch (e) {
            return [];
        }
    }
}
