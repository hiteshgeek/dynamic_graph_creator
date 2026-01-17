/**
 * DataMapper - Column to chart axis mapping component
 * Maps query columns to chart data requirements
 */

export default class DataMapper {
    constructor(container, options = {}) {
        this.container = container;
        this.onChange = options.onChange || (() => {});

        this.graphType = 'bar';
        this.columns = [];
        this.mapping = {};

        this.init();
    }

    /**
     * Initialize data mapper
     */
    init() {
        this.render();
    }

    /**
     * Set graph type and update UI
     */
    setGraphType(type) {
        this.graphType = type;
        this.mapping = {};
        this.render();
    }

    /**
     * Set available columns
     */
    setColumns(columns) {
        this.columns = columns || [];
        this.render();
    }

    /**
     * Set mapping values
     * @param {Object} mapping - The mapping object
     * @param {boolean} triggerChange - Whether to trigger onChange callback (default: true)
     */
    setMapping(mapping, triggerChange = true) {
        this.mapping = mapping || {};
        this.render();
        if (triggerChange) {
            this.onChange();
        }
    }

    /**
     * Get current mapping
     */
    getMapping() {
        return this.mapping;
    }

    /**
     * Render the mapper UI
     */
    render() {
        const fields = this.getFieldsForType();

        let html = `
            <div class="graph-section-header">
                <h3><i class="fas fa-project-diagram"></i> Data Mapping</h3>
            </div>
        `;

        if (this.columns.length === 0) {
            html += `
                <div class="data-mapper-empty">
                    <p class="text-muted text-small">
                        Test your query to see available columns
                    </p>
                </div>
            `;
        } else {
            html += `
                <div class="data-mapper-fields">
                    ${fields.map(field => this.renderField(field)).join('')}
                </div>
                <div class="columns-preview">
                    <strong>Available columns:</strong>
                    ${this.columns.map(col => `
                        <span class="column-tag">${col}</span>
                    `).join('')}
                </div>
            `;
        }

        this.container.innerHTML = html;
        this.bindEvents();
    }

    /**
     * Get mapping fields based on graph type
     */
    getFieldsForType() {
        switch (this.graphType) {
            case 'bar':
            case 'line':
                return [
                    {
                        key: 'x_column',
                        label: 'X-Axis Column',
                        description: 'Column for category labels',
                        type: 'select'
                    },
                    {
                        key: 'x_axis_title',
                        label: 'X-Axis Title',
                        description: 'Label for X-axis',
                        type: 'text',
                        placeholder: 'e.g., Month, Category'
                    },
                    {
                        key: 'y_column',
                        label: 'Y-Axis Column',
                        description: 'Column for numeric values',
                        type: 'select'
                    },
                    {
                        key: 'y_axis_title',
                        label: 'Y-Axis Title',
                        description: 'Label for Y-axis',
                        type: 'text',
                        placeholder: 'e.g., Sales, Count'
                    }
                ];
            case 'pie':
                return [
                    {
                        key: 'name_column',
                        label: 'Name (Slices)',
                        description: 'Column for slice names',
                        type: 'select'
                    },
                    {
                        key: 'value_column',
                        label: 'Value (Size)',
                        description: 'Column for slice values',
                        type: 'select'
                    }
                ];
            default:
                return [];
        }
    }

    /**
     * Render a single mapping field
     */
    renderField(field) {
        const value = this.mapping[field.key] || '';
        const fieldType = field.type || 'select';

        let inputHtml = '';

        const fieldId = `mapper-${field.key}`;

        if (fieldType === 'select') {
            inputHtml = `
                <select
                    class="form-select"
                    id="${fieldId}"
                    data-field="${field.key}"
                >
                    <option value="">-- Select column --</option>
                    ${this.columns.map(col => `
                        <option
                            value="${col}"
                            ${col === value ? 'selected' : ''}
                        >${col}</option>
                    `).join('')}
                </select>
            `;
        } else if (fieldType === 'text') {
            inputHtml = `
                <input
                    type="text"
                    class="form-control"
                    id="${fieldId}"
                    data-field="${field.key}"
                    value="${this.escapeHtml(value)}"
                    placeholder="${field.placeholder || ''}"
                />
            `;
        }

        return `
            <div class="data-mapper-field ${fieldType === 'text' ? 'text-field' : ''}">
                <label for="${fieldId}" title="${field.description}">${field.label}</label>
                ${inputHtml}
            </div>
        `;
    }

    /**
     * Escape HTML for attribute values
     */
    escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    /**
     * Bind change events
     */
    bindEvents() {
        const selects = this.container.querySelectorAll('select[data-field]');
        const inputs = this.container.querySelectorAll('input[data-field]');

        selects.forEach(select => {
            select.addEventListener('change', (e) => {
                const field = e.target.dataset.field;
                const value = e.target.value;

                if (value) {
                    this.mapping[field] = value;
                } else {
                    delete this.mapping[field];
                }

                this.onChange();
            });
        });

        inputs.forEach(input => {
            input.addEventListener('input', (e) => {
                const field = e.target.dataset.field;
                const value = e.target.value.trim();

                if (value) {
                    this.mapping[field] = value;
                } else {
                    delete this.mapping[field];
                }

                this.onChange();
            });
        });
    }
}
