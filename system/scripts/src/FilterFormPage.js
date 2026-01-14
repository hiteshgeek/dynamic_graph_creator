/**
 * FilterFormPage - Filter add/edit form controller
 * Handles filter form functionality including CodeMirror, options management, and save
 */

const Ajax = window.Ajax;
const Loading = window.Loading;
const Toast = window.Toast;

export default class FilterFormPage {
    constructor(container) {
        this.container = container;
        this.queryEditor = null;
        this.typesWithOptions = ['select', 'checkbox', 'radio', 'tokeninput'];

        if (this.container) {
            this.init();
        }
    }

    /**
     * Initialize the filter form page
     */
    init() {
        this.initCodeMirror();
        this.bindEvents();
    }

    /**
     * Initialize CodeMirror for SQL query
     */
    initCodeMirror() {
        const queryTextarea = document.getElementById('data-query');
        if (queryTextarea && typeof CodeMirror !== 'undefined') {
            this.queryEditor = CodeMirror.fromTextArea(queryTextarea, {
                mode: 'text/x-sql',
                theme: 'default',
                lineNumbers: true,
                lineWrapping: true
            });
        }
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Filter type change - show/hide options section and select config
        const filterType = document.getElementById('filter-type');
        if (filterType) {
            filterType.addEventListener('change', () => this.onFilterTypeChange());
        }

        // Data source tab switching
        document.querySelectorAll('.data-source-tab').forEach(tab => {
            tab.addEventListener('click', (e) => this.onDataSourceTabClick(e.currentTarget));
        });

        // Add option button
        const addOptionBtn = document.querySelector('.add-option-btn');
        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', () => this.addOptionRow());
        }

        // Remove option buttons (delegated)
        const optionsList = document.querySelector('.filter-options-list');
        if (optionsList) {
            optionsList.addEventListener('click', (e) => {
                if (e.target.closest('.remove-option-btn')) {
                    const row = e.target.closest('.filter-option-item');
                    if (document.querySelectorAll('.filter-option-item').length > 1) {
                        row.remove();
                    }
                }
            });
        }

        // Test query button
        const testQueryBtn = document.getElementById('test-query-btn');
        if (testQueryBtn) {
            testQueryBtn.addEventListener('click', () => this.testQuery());
        }

        // Save filter button
        const saveBtn = document.querySelector('.save-filter-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveFilter());
        }
    }

    /**
     * Handle filter type change
     */
    onFilterTypeChange() {
        const filterType = document.getElementById('filter-type').value;
        const dataSourceSection = document.getElementById('data-source-section');
        const selectConfigSection = document.getElementById('select-config-section');

        if (dataSourceSection) {
            dataSourceSection.style.display = this.typesWithOptions.includes(filterType) ? 'block' : 'none';
        }

        if (selectConfigSection) {
            selectConfigSection.style.display = filterType === 'select' ? 'block' : 'none';
        }
    }

    /**
     * Handle data source tab click
     */
    onDataSourceTabClick(tab) {
        const source = tab.dataset.source;
        document.getElementById('data-source').value = source;

        // Update active tab
        document.querySelectorAll('.data-source-tab').forEach(t => {
            t.classList.remove('active');
        });
        tab.classList.add('active');

        // Show/hide sections
        document.getElementById('static-options-section').style.display = source === 'static' ? 'block' : 'none';
        document.getElementById('query-options-section').style.display = source === 'query' ? 'block' : 'none';

        // Refresh CodeMirror when switching to query
        if (source === 'query' && this.queryEditor) {
            this.queryEditor.refresh();
        }
    }

    /**
     * Add an option row
     */
    addOptionRow(value = '', label = '') {
        const optionsList = document.querySelector('.filter-options-list');
        const row = document.createElement('div');
        row.className = 'filter-option-item';
        row.innerHTML = `
            <input type="text" class="form-control option-value" placeholder="Value" value="${this.escapeHtml(value)}">
            <input type="text" class="form-control option-label" placeholder="Label" value="${this.escapeHtml(label)}">
            <button type="button" class="btn btn-sm btn-outline remove-option-btn">
                <i class="fas fa-times"></i>
            </button>
        `;
        optionsList.appendChild(row);
    }

    /**
     * Test the SQL query
     */
    testQuery() {
        const query = this.queryEditor ? this.queryEditor.getValue() : document.getElementById('data-query').value;

        if (!query.trim()) {
            Toast.error('Please enter a query');
            return;
        }

        Loading.show('Testing query...');

        Ajax.post('test_filter_query', { query: query })
            .then(result => {
                Loading.hide();
                const resultDiv = document.getElementById('query-result');

                if (result.success) {
                    const options = result.data.options || [];
                    let html = `<div class="alert alert-success"><strong>Query valid!</strong> Found ${options.length} options.</div>`;

                    // Show warnings if any
                    if (result.data.warnings && result.data.warnings.length > 0) {
                        html += '<div class="alert alert-warning"><ul class="mb-0">';
                        result.data.warnings.forEach(w => {
                            html += `<li>${w}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    if (options.length > 0) {
                        html += '<table class="table table-sm"><thead><tr><th>Value</th><th>Label</th></tr></thead><tbody>';
                        options.slice(0, 10).forEach(opt => {
                            html += `<tr><td>${opt.value || '-'}</td><td>${opt.label || '-'}</td></tr>`;
                        });
                        if (options.length > 10) {
                            html += `<tr><td colspan="2" class="text-muted">... and ${options.length - 10} more</td></tr>`;
                        }
                        html += '</tbody></table>';
                    }

                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger">${result.message || 'Query failed'}</div>`;
                }

                resultDiv.style.display = 'block';
            })
            .catch(() => {
                Loading.hide();
                Toast.error('Failed to test query');
            });
    }

    /**
     * Save the filter
     */
    saveFilter() {
        let filterKey = document.getElementById('filter-key').value.trim();
        const filterLabel = document.getElementById('filter-label').value.trim();
        const filterType = document.getElementById('filter-type').value;
        const dataSource = document.getElementById('data-source').value;

        // Validation
        if (!filterKey) {
            Toast.error('Filter key is required');
            return;
        }

        if (!filterLabel) {
            Toast.error('Filter label is required');
            return;
        }

        // Ensure filter key starts with :
        if (filterKey.charAt(0) !== ':') {
            filterKey = ':' + filterKey;
        }

        // Handle select with multiple option
        let actualFilterType = filterType;
        const multipleCheckbox = document.getElementById('filter-multiple');
        if (filterType === 'select' && multipleCheckbox && multipleCheckbox.checked) {
            actualFilterType = 'multi_select';
        }

        const data = {
            filter_id: document.getElementById('filter-id').value,
            filter_key: filterKey,
            filter_label: filterLabel,
            filter_type: actualFilterType,
            data_source: this.typesWithOptions.includes(filterType) ? dataSource : 'static',
            data_query: '',
            static_options: '',
            default_value: document.getElementById('filter-default').value,
            is_required: document.getElementById('filter-required').checked ? 1 : 0
        };

        // Get options based on data source
        if (this.typesWithOptions.includes(filterType)) {
            if (dataSource === 'query') {
                data.data_query = this.queryEditor ? this.queryEditor.getValue() : document.getElementById('data-query').value;
            } else {
                const optionItems = [];
                document.querySelectorAll('.filter-option-item').forEach(row => {
                    const value = row.querySelector('.option-value').value.trim();
                    const label = row.querySelector('.option-label').value.trim();
                    if (value || label) {
                        optionItems.push({ value: value, label: label || value });
                    }
                });
                data.static_options = JSON.stringify(optionItems);
            }
        }

        Loading.show('Saving filter...');

        Ajax.post('save_filter', data)
            .then(result => {
                Loading.hide();
                if (result.success) {
                    Toast.success('Filter saved successfully');
                    window.location.href = '?urlq=filters';
                } else {
                    Toast.error(result.message || 'Failed to save filter');
                }
            })
            .catch(() => {
                Loading.hide();
                Toast.error('Failed to save filter');
            });
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
