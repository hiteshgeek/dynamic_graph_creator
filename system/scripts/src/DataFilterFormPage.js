/**
 * DataFilterFormPage - Data Filter add/edit form controller
 * Handles filter form functionality including CodeMirror, options management, and save
 */

import CodeMirrorEditor from './CodeMirrorEditor.js';

const Ajax = window.Ajax;
const Loading = window.Loading;
const Toast = window.Toast;
const FormValidator = window.FormValidator;

export default class DataFilterFormPage {
    constructor(container) {
        this.container = container;
        this.queryEditor = null;
        this.typesWithOptions = ['select', 'checkbox', 'radio', 'tokeninput'];
        this.lastQueryOptions = null; // Store query results for preview updates

        // State tracking
        this.hasUnsavedChanges = false;
        this.queryTestPassed = false;
        this.queryError = null;
        this.isEditMode = false;
        this.savedState = null;

        if (this.container) {
            this.init();
        }
    }

    /**
     * Initialize the filter form page
     */
    init() {
        this.isEditMode = !!document.getElementById('filter-id')?.value;
        this.initCodeMirror();
        this.initFormValidation();
        this.bindEvents();
        this.initPreview();
        this.initChangeTracking();
        this.updateStatusIndicators();
    }

    /**
     * Initialize form validation
     */
    initFormValidation() {
        const form = document.getElementById('filter-form');
        if (!form) return;

        this.formValidator = new FormValidator(form, {
            rules: {
                'filter-key': {
                    required: true,
                    pattern: /^[a-zA-Z0-9_]+$/
                },
                'filter-label': {
                    required: true
                }
            },
            messages: {
                'filter-key': {
                    required: 'Filter key is required',
                    pattern: 'Only letters, numbers, and underscores allowed'
                },
                'filter-label': {
                    required: 'Label is required'
                }
            },
            validateOnBlur: true,
            validateOnInput: true
        });
    }

    /**
     * Initialize preview on page load (for edit mode)
     */
    initPreview() {
        const filterType = document.getElementById('filter-type')?.value;
        const dataSource = document.getElementById('data-source')?.value;

        // Show preview for date types immediately (they don't need options)
        if (filterType === 'date' || filterType === 'date_range' || filterType === 'main_datepicker') {
            this.showFilterPreview([]);
            return;
        }

        if (dataSource === 'static') {
            // Show static preview immediately if options exist
            this.updateStaticPreview();
        } else if (dataSource === 'query') {
            // Auto-run test query if there's a query (edit mode)
            const query = this.queryEditor ? this.queryEditor.getValue() : document.getElementById('data-query')?.value;
            if (query && query.trim()) {
                // Small delay to ensure CodeMirror is ready
                setTimeout(() => this.testQuery(), 100);
            }
        }
    }

    /**
     * Initialize CodeMirror for SQL query using reusable CodeMirrorEditor
     */
    initCodeMirror() {
        const queryTextarea = document.getElementById('data-query');
        if (queryTextarea && typeof CodeMirror !== 'undefined') {
            this.codeEditor = new CodeMirrorEditor(queryTextarea, {
                copyBtn: true,
                formatBtn: true,
                testBtn: true,
                minHeight: 100,
                hint: 'Query must return <code>value</code> and <code>label</code> columns. Optional: add <code>is_selected</code> column (1/0) to pre-select options.',
                onTest: () => this.testQuery(),
                onFormat: (query) => this.formatQueryString(query)
            });
            // Keep reference for backward compatibility
            this.queryEditor = this.codeEditor.editor;
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
            // Initialize date range info on page load
            this.onFilterTypeChange();
        }

        // Filter key change - update date range placeholder examples
        const filterKey = document.getElementById('filter-key');
        if (filterKey) {
            filterKey.addEventListener('input', () => this.updateDateRangePlaceholders());
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

        // Remove option buttons (delegated) and auto-update preview on input
        const optionsList = document.querySelector('.filter-options-list');
        if (optionsList) {
            optionsList.addEventListener('click', (e) => {
                if (e.target.closest('.remove-option-btn')) {
                    const row = e.target.closest('.filter-option-item');
                    if (document.querySelectorAll('.filter-option-item').length > 1) {
                        row.remove();
                        this.updateStaticPreview();
                    }
                }
            });

            // Auto-update preview when typing in option fields
            optionsList.addEventListener('input', () => this.updateStaticPreview());
        }

        // Note: Copy, Format, and Test buttons are now handled by CodeMirrorEditor

        // Inline checkbox - update preview when changed
        const inlineCheckbox = document.getElementById('filter-inline');
        if (inlineCheckbox) {
            inlineCheckbox.addEventListener('change', () => this.updatePreview());
        }

        // Multiple selection checkbox - update preview when changed
        const multipleCheckbox = document.getElementById('filter-multiple');
        if (multipleCheckbox) {
            multipleCheckbox.addEventListener('change', () => this.updatePreview());
        }

        // Filter label input - update preview when changed
        const filterLabelInput = document.getElementById('filter-label');
        if (filterLabelInput) {
            filterLabelInput.addEventListener('input', () => this.updatePreview());
        }

        // Save filter button
        const saveBtn = document.querySelector('.save-filter-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveFilter();
            });
        }

        // System placeholder click to copy to clipboard
        document.querySelectorAll('.system-placeholder-item').forEach(item => {
            item.addEventListener('click', () => this.copySystemPlaceholder(item.dataset.placeholder));
        });

        // System placeholder search
        const searchInput = document.getElementById('system-placeholder-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.filterSystemPlaceholders(e.target.value));
        }

        // Initialize Bootstrap tooltips for system placeholders
        this.initSystemPlaceholderTooltips();
    }

    /**
     * Initialize Bootstrap tooltips for system placeholder items
     */
    initSystemPlaceholderTooltips() {
        const tooltipTriggerList = document.querySelectorAll('.system-placeholder-item[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * Copy system placeholder to clipboard
     */
    copySystemPlaceholder(placeholder) {
        if (!placeholder) return;

        navigator.clipboard.writeText(placeholder).then(() => {
            Toast.success(`Copied ${placeholder} to clipboard`);
        }).catch(() => {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = placeholder;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            Toast.success(`Copied ${placeholder} to clipboard`);
        });
    }

    /**
     * Filter system placeholders based on search query
     */
    filterSystemPlaceholders(query) {
        const items = document.querySelectorAll('.system-placeholder-item');
        const emptyMessage = document.getElementById('system-placeholders-empty');
        const searchLower = query.toLowerCase().trim();
        let visibleCount = 0;

        items.forEach(item => {
            if (!searchLower) {
                item.classList.remove('hidden');
                visibleCount++;
                return;
            }

            const label = item.dataset.label || '';
            const key = item.dataset.key || '';
            const description = item.dataset.description || '';

            const matches = label.includes(searchLower) ||
                           key.includes(searchLower) ||
                           description.includes(searchLower);

            if (matches) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        // Show/hide empty message
        if (emptyMessage) {
            emptyMessage.classList.toggle('show', visibleCount === 0 && searchLower);
        }
    }

    /**
     * Initialize or update the generated query CodeMirror editor
     */
    updateGeneratedQueryEditor(query) {
        const textarea = document.getElementById('generated-query-code');
        const generatedTab = document.getElementById('generated-query-tab');
        if (!textarea) return;

        // Store query for later use
        this.pendingGeneratedQuery = query;

        // Initialize CodeMirrorEditor if not already done
        if (!this.generatedQueryCodeEditor) {
            this.generatedQueryCodeEditor = new CodeMirrorEditor(textarea, {
                copyBtn: true,
                formatBtn: false,
                testBtn: false,
                minHeight: 100,
                readOnly: true
            });
            this.generatedQueryEditor = this.generatedQueryCodeEditor.editor;

            // Refresh CodeMirror when tab becomes visible - this is the key fix
            if (generatedTab) {
                generatedTab.addEventListener('shown.bs.tab', () => {
                    // Set value and format when tab is visible
                    if (this.pendingGeneratedQuery) {
                        this.generatedQueryEditor.setValue(this.pendingGeneratedQuery);
                        this.generatedQueryCodeEditor.format();
                    }
                    this.generatedQueryEditor.refresh();
                });
            }
        } else {
            // If already initialized and tab is visible, update immediately
            const tabPane = document.getElementById('generated-query-pane');
            if (tabPane && tabPane.classList.contains('show')) {
                this.generatedQueryEditor.setValue(query);
                this.generatedQueryCodeEditor.format();
                this.generatedQueryEditor.refresh();
            }
        }
    }

    /**
     * Handle filter type change
     */
    onFilterTypeChange() {
        const filterType = document.getElementById('filter-type').value;
        const dataSourceSection = document.getElementById('data-source-section');
        const selectConfigSection = document.getElementById('select-config-section');
        const checkboxRadioConfigSection = document.getElementById('checkbox-radio-config-section');
        const dateRangeInfo = document.getElementById('date-range-info');
        const previewSection = document.getElementById('filter-preview-section');

        const showDataSource = this.typesWithOptions.includes(filterType);

        if (dataSourceSection) {
            dataSourceSection.style.display = showDataSource ? 'block' : 'none';
        }

        // Show/hide right panel for data source options
        const dataSourcePanel = document.getElementById('data-source-panel');
        if (dataSourcePanel) {
            dataSourcePanel.style.display = showDataSource ? '' : 'none';
        }

        if (checkboxRadioConfigSection) {
            checkboxRadioConfigSection.style.display = (filterType === 'checkbox' || filterType === 'radio') ? 'block' : 'none';
        }

        if (selectConfigSection) {
            selectConfigSection.style.display = filterType === 'select' ? 'block' : 'none';
        }

        // Show/hide date range info for date range types
        const isDateRange = filterType === 'date_range' || filterType === 'main_datepicker';
        if (dateRangeInfo) {
            dateRangeInfo.style.display = isDateRange ? 'block' : 'none';
            if (isDateRange) {
                this.updateDateRangePlaceholders();
            }
        }

        // Show preview for date types immediately (they don't need options)
        if (filterType === 'date' || filterType === 'date_range' || filterType === 'main_datepicker') {
            this.showFilterPreview([]);
        } else if (!this.typesWithOptions.includes(filterType)) {
            // Hide preview for non-option types like text/number
            if (previewSection) {
                previewSection.style.display = 'none';
            }
        } else {
            // Update preview when filter type changes for option-based types
            this.updatePreview();
        }
    }

    /**
     * Update date range placeholder examples based on filter key
     */
    updateDateRangePlaceholders() {
        const filterKey = document.getElementById('filter-key')?.value || 'filter_key';
        const fromPlaceholder = document.getElementById('date-range-from-placeholder');
        const toPlaceholder = document.getElementById('date-range-to-placeholder');

        if (fromPlaceholder) {
            fromPlaceholder.textContent = `::${filterKey}_from`;
        }
        if (toPlaceholder) {
            toPlaceholder.textContent = `::${filterKey}_to`;
        }

        // Update the example query too
        const dateRangeInfo = document.getElementById('date-range-info');
        if (dateRangeInfo) {
            const small = dateRangeInfo.querySelector('small');
            if (small) {
                small.innerHTML = `Example: <code>WHERE created_ts BETWEEN ::${filterKey}_from AND ::${filterKey}_to</code>`;
            }
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

        // Show/hide sections in right column
        document.getElementById('static-options-section').style.display = source === 'static' ? 'block' : 'none';
        document.getElementById('query-options-section').style.display = source === 'query' ? 'block' : 'none';

        // Refresh CodeMirror when switching to query
        if (source === 'query' && this.codeEditor) {
            setTimeout(() => this.codeEditor.refresh(), 50);
        }

        // Update filter preview based on data source
        this.updatePreviewOnSourceChange(source);
    }

    /**
     * Update filter preview when switching data source
     */
    updatePreviewOnSourceChange(source) {
        const previewSection = document.getElementById('filter-preview-section');
        const queryResultDiv = document.getElementById('query-result');

        if (source === 'static') {
            // Hide query results when switching to static
            if (queryResultDiv) {
                queryResultDiv.style.display = 'none';
            }

            // Show static preview if there are options
            const options = this.getStaticOptions();
            if (options.length > 0) {
                this.showFilterPreview(options);
            } else if (previewSection) {
                previewSection.style.display = 'none';
            }
        } else {
            // Switching to query - hide preview until query is tested
            if (previewSection) {
                previewSection.style.display = 'none';
            }
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
            <button type="button" class="btn btn-sm btn-outline-secondary remove-option-btn">
                <i class="fas fa-times"></i>
            </button>
        `;
        optionsList.appendChild(row);
        this.updateStaticPreview();
    }

    /**
     * Update preview based on current data source
     */
    updatePreview() {
        const dataSource = document.getElementById('data-source').value;

        if (dataSource === 'static') {
            this.updateStaticPreview();
        } else if (dataSource === 'query' && this.lastQueryOptions && this.lastQueryOptions.length > 0) {
            // Re-render preview with cached query options
            this.showFilterPreview(this.lastQueryOptions);
        }
    }

    /**
     * Auto-update static options preview
     */
    updateStaticPreview() {
        const dataSource = document.getElementById('data-source').value;
        if (dataSource !== 'static') return;

        const options = this.getStaticOptions();
        if (options.length > 0) {
            this.showFilterPreview(options);
        } else {
            const section = document.getElementById('filter-preview-section');
            if (section) {
                section.style.display = 'none';
            }
        }
    }

    /**
     * Format SQL query string - returns formatted query
     * Used by CodeMirrorEditor's onFormat callback
     * @param {string} query - The SQL query to format
     * @returns {string} Formatted SQL query
     */
    formatQueryString(query) {
        if (!query || !query.trim()) return query;

        // Use CodeMirrorEditor's built-in formatter
        // This method is called by the editor, so just return the formatted result
        return this.codeEditor ? this.codeEditor.formatSQL(query) : query;
    }

    /**
     * Test the SQL query
     * @param {number} page - Page number (default 1)
     */
    testQuery(page = 1) {
        // Format query before testing (only on first page)
        if (page === 1 && this.codeEditor) {
            this.codeEditor.format();
        }

        const query = this.queryEditor ? this.queryEditor.getValue() : document.getElementById('data-query').value;

        if (!query.trim()) {
            Toast.error('Please enter a query');
            return;
        }

        Loading.show(page === 1 ? 'Testing query...' : 'Loading page...');

        Ajax.post('test_data_filter_query', { query: query, page: page })
            .then(result => {
                Loading.hide();
                const resultDiv = document.getElementById('query-result');

                if (result.success) {
                    const options = result.data.options || [];
                    const totalCount = result.data.totalCount || options.length;
                    const currentPage = result.data.page || 1;
                    const totalPages = result.data.totalPages || 1;
                    const pageSize = result.data.pageSize || 100;

                    let html = `
                        <div class="query-result-header">
                            <i class="fas fa-check-circle"></i>
                            Query is valid
                            <span class="query-row-count">${totalCount} total row${totalCount !== 1 ? 's' : ''}</span>
                        </div>
                    `;

                    // Show Generated Query tab and populate it (only on first page)
                    if (page === 1 && result.data.resolvedQuery) {
                        const generatedTabItem = document.getElementById('generated-query-tab-item');
                        if (generatedTabItem) {
                            generatedTabItem.style.display = '';
                        }
                        this.updateGeneratedQueryEditor(result.data.resolvedQuery);
                    }

                    // Show warnings if any (only on first page)
                    if (page === 1 && result.data.warnings && result.data.warnings.length > 0) {
                        html += '<div class="query-warnings"><ul>';
                        result.data.warnings.forEach(w => {
                            html += `<li>${w}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    // Store options for later preview updates (accumulate across pages for preview)
                    if (page === 1) {
                        this.lastQueryOptions = options;
                    }

                    if (options.length > 0) {
                        const hasIsSelected = result.data.hasIsSelected || false;
                        const columns = result.data.columns || ['value', 'label'];
                        const rows = result.data.rows || [];

                        html += `
                            <div class="query-sample-data">
                                <div class="query-result-table-wrapper">
                                    <table class="query-result-table" id="query-result-table">
                                        <thead>
                                            <tr>${columns.map(col => `<th>${this.escapeHtml(col)}</th>`).join('')}</tr>
                                        </thead>
                                        <tbody id="query-result-tbody">
                        `;
                        rows.forEach(row => {
                            html += '<tr>';
                            columns.forEach(col => {
                                let cellValue = row[col];
                                // Special handling for is_selected column
                                if (col === 'is_selected') {
                                    const isSelected = cellValue === 1 || cellValue === '1' || cellValue === true;
                                    cellValue = isSelected ? '<i class="fas fa-check text-success"></i>' : '-';
                                    html += `<td>${cellValue}</td>`;
                                } else {
                                    html += `<td>${this.escapeHtml(String(cellValue ?? '')) || '-'}</td>`;
                                }
                            });
                            html += '</tr>';
                        });
                        html += '</tbody></table></div>';

                        // Pagination info
                        const startRecord = (currentPage - 1) * pageSize + 1;
                        const endRecord = Math.min(currentPage * pageSize, totalCount);
                        html += `<div class="query-result-info">`;
                        html += `<span class="query-result-count">Showing ${startRecord}-${endRecord} of ${totalCount} records</span>`;

                        // Pagination controls
                        if (totalPages > 1) {
                            html += `<div class="query-result-pagination" id="query-result-pagination">`;
                            html += `<button type="button" class="btn btn-sm btn-outline-secondary query-page-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                            html += `<span class="query-page-info">Page ${currentPage} of ${totalPages}</span>`;
                            html += `<button type="button" class="btn btn-sm btn-outline-secondary query-page-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                            html += `</div>`;
                        }
                        html += `</div></div>`;

                        // Show filter preview in dedicated container (only on first page)
                        if (page === 1) {
                            this.showFilterPreview(options);
                        }

                        // Store data for pagination
                        this.queryResultHasIsSelected = hasIsSelected;
                    }

                    resultDiv.className = 'query-test-result success';
                    resultDiv.innerHTML = html;

                    // Bind pagination events
                    this.bindQueryPagination();

                    // Mark query test as passed
                    this.setQueryTestPassed();
                } else {
                    resultDiv.className = 'query-test-result error';
                    resultDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${result.message || 'Query failed'}`;

                    // Set query error and show toast
                    this.setQueryError(result.message || 'Query failed');
                    Toast.error(result.message || 'Query failed');
                }

                resultDiv.style.display = 'block';
            })
            .catch(() => {
                Loading.hide();
                Toast.error('Failed to test query');
                this.setQueryError('Failed to test query');
            });
    }

    /**
     * Bind pagination events for query result table
     */
    bindQueryPagination() {
        const pagination = document.getElementById('query-result-pagination');
        if (!pagination) return;

        pagination.querySelectorAll('.query-page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const page = parseInt(btn.dataset.page, 10);
                if (page > 0) {
                    this.testQuery(page);
                }
            });
        });
    }

    /**
     * Get all system placeholder keys from the DOM
     * @returns {string[]} Array of system placeholder keys (without :: prefix)
     */
    getSystemPlaceholderKeys() {
        const keys = [];
        document.querySelectorAll('.system-placeholder-item[data-placeholder]').forEach(item => {
            // data-placeholder contains "::key", extract just the key part
            const placeholder = item.dataset.placeholder || '';
            const key = placeholder.replace(/^::/, '');
            if (key) {
                keys.push(key.toLowerCase());
            }
        });
        return keys;
    }

    /**
     * Check if filter key conflicts with system placeholders
     * @param {string} filterKey - The filter key to check (without :: prefix)
     * @returns {string|null} Error message if conflict found, null otherwise
     */
    checkSystemPlaceholderConflict(filterKey) {
        const systemKeys = this.getSystemPlaceholderKeys();
        const filterKeyLower = filterKey.toLowerCase();

        for (const sysKey of systemKeys) {
            // Check if filter key exactly matches a system placeholder
            if (filterKeyLower === sysKey) {
                return `Filter key "${filterKey}" conflicts with system placeholder "::${sysKey}"`;
            }
            // Check if filter key is a substring of a system placeholder
            if (sysKey.includes(filterKeyLower)) {
                return `Filter key "${filterKey}" is a substring of system placeholder "::${sysKey}"`;
            }
            // Check if system placeholder is a substring of filter key
            if (filterKeyLower.includes(sysKey)) {
                return `Filter key "${filterKey}" contains system placeholder "::${sysKey}" as substring`;
            }
        }

        return null;
    }

    /**
     * Save the filter
     */
    saveFilter() {
        // Use FormValidator for validation
        if (this.formValidator && !this.formValidator.validate()) {
            Toast.error('Please correct the errors in the form');
            return;
        }

        let filterKey = document.getElementById('filter-key').value.trim();
        const filterLabel = document.getElementById('filter-label').value.trim();
        const filterType = document.getElementById('filter-type').value;
        const dataSource = document.getElementById('data-source').value;

        // Check for system placeholder conflicts before saving
        const conflictError = this.checkSystemPlaceholderConflict(filterKey);
        if (conflictError) {
            Toast.error(conflictError);
            document.getElementById('filter-key').focus();
            return;
        }

        // Check if query data source is selected but query hasn't been tested successfully
        if (this.typesWithOptions.includes(filterType) && dataSource === 'query') {
            const query = this.queryEditor ? this.queryEditor.getValue() : document.getElementById('data-query')?.value;
            if (!query || !query.trim()) {
                Toast.error('Please enter a SQL query');
                return;
            }
            if (!this.queryTestPassed) {
                Toast.error('Please test the query successfully before saving');
                return;
            }
        }

        // Automatically prepend :: to the filter key
        filterKey = '::' + filterKey;

        // Handle select with multiple option
        let actualFilterType = filterType;
        const multipleCheckbox = document.getElementById('filter-multiple');
        if (filterType === 'select' && multipleCheckbox && multipleCheckbox.checked) {
            actualFilterType = 'multi_select';
        }

        // Build filter config
        const filterConfig = {};
        const inlineCheckbox = document.getElementById('filter-inline');
        if (inlineCheckbox && inlineCheckbox.checked) {
            filterConfig.inline = true;
        }

        const data = {
            filter_id: document.getElementById('filter-id').value,
            filter_key: filterKey,
            filter_label: filterLabel,
            filter_type: actualFilterType,
            data_source: this.typesWithOptions.includes(filterType) ? dataSource : 'static',
            data_query: '',
            static_options: '',
            filter_config: JSON.stringify(filterConfig),
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

        Ajax.post('save_data_filter', data)
            .then(result => {
                Loading.hide();
                if (result.success) {
                    // Mark as saved before redirect to prevent "unsaved changes" warning
                    this.markAsSaved();
                    Toast.success('Filter saved successfully');
                    window.location.href = '?urlq=data-filter';
                } else {
                    Toast.error(result.message || 'Failed to save filter');
                }
            })
            .catch((error) => {
                Loading.hide();
                Toast.error('Failed to save filter: ' + (error.message || 'Unknown error'));
            });
    }

    /**
     * Get static options from form
     */
    getStaticOptions() {
        const options = [];
        document.querySelectorAll('.filter-option-item').forEach(row => {
            const value = row.querySelector('.option-value').value.trim();
            const label = row.querySelector('.option-label').value.trim();
            if (value || label) {
                options.push({ value: value, label: label || value });
            }
        });
        return options;
    }

    /**
     * Show filter preview in dedicated section
     */
    showFilterPreview(options) {
        const section = document.getElementById('filter-preview-section');
        if (!section) return;

        // Remove existing preview if any
        const existingPreview = section.querySelector('.filter-preview');
        if (existingPreview) {
            existingPreview.remove();
        }

        // Append new preview
        section.insertAdjacentHTML('beforeend', this.renderFilterPreview(options));

        // Show the section
        section.style.display = 'block';

        // Bind multiselect dropdown toggle
        this.bindMultiselectDropdown(section);

        // Initialize date pickers if present
        if (typeof DatePickerInit !== 'undefined') {
            DatePickerInit.init(section);
        }
    }

    /**
     * Bind click events for multiselect dropdown toggle
     */
    bindMultiselectDropdown(container) {
        const dropdown = container.querySelector('.filter-multiselect-dropdown');
        const trigger = container.querySelector('.filter-multiselect-trigger');
        const optionsPanel = container.querySelector('.filter-multiselect-options');
        const placeholder = container.querySelector('.filter-multiselect-placeholder');

        if (!trigger || !optionsPanel) return;

        const optionItems = dropdown.querySelectorAll('.filter-multiselect-option');
        const selectAllBtn = dropdown.querySelector('.multiselect-select-all');
        const selectNoneBtn = dropdown.querySelector('.multiselect-select-none');
        const searchInput = dropdown.querySelector('.multiselect-search');

        // Update placeholder text based on selection
        const updatePlaceholder = () => {
            const checkboxes = dropdown.querySelectorAll('.filter-multiselect-option input[type="checkbox"]');
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

            if (checkedCount === 0) {
                placeholder.textContent = '-- Select multiple --';
                placeholder.classList.remove('has-selection');
            } else {
                placeholder.textContent = `${checkedCount} selected`;
                placeholder.classList.add('has-selection');
            }
        };

        // Bind checkbox changes
        optionItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.addEventListener('change', updatePlaceholder);
            }
        });

        // Select All button (only selects visible items)
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                optionItems.forEach(item => {
                    if (item.style.display !== 'none') {
                        const cb = item.querySelector('input[type="checkbox"]');
                        if (cb) cb.checked = true;
                    }
                });
                updatePlaceholder();
            });
        }

        // Select None button (only deselects visible items)
        if (selectNoneBtn) {
            selectNoneBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                optionItems.forEach(item => {
                    if (item.style.display !== 'none') {
                        const cb = item.querySelector('input[type="checkbox"]');
                        if (cb) cb.checked = false;
                    }
                });
                updatePlaceholder();
            });
        }

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase().trim();
                optionItems.forEach(item => {
                    const label = item.querySelector('.form-check-label')?.textContent.toLowerCase() || '';
                    if (searchTerm === '' || label.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            // Prevent dropdown from closing when clicking search input
            searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            optionsPanel.classList.toggle('open');
            const icon = trigger.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                optionsPanel.classList.remove('open');
                const icon = trigger.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
        });

        // Update placeholder on init to reflect any pre-selected options (from is_selected)
        updatePlaceholder();
    }

    /**
     * Render filter preview based on filter type and options
     */
    renderFilterPreview(options) {
        const filterType = document.getElementById('filter-type').value;
        const filterLabel = document.getElementById('filter-label').value || 'Filter';
        const isMultiple = document.getElementById('filter-multiple')?.checked || false;
        const isInline = document.getElementById('filter-inline')?.checked || false;

        let previewHtml = `
            <div class="filter-preview">
                <div class="filter-preview-content">
                    <label class="filter-preview-label">${this.escapeHtml(filterLabel)}</label>
        `;

        // Render different preview based on filter type
        if (filterType === 'date') {
            // Single date picker
            previewHtml += `
                <input type="text" class="form-control dgc-datepicker" data-picker-type="single" placeholder="Select date" autocomplete="off" id="filter-preview-date">
            `;
        } else if (filterType === 'date_range') {
            // Date range picker (basic - no presets)
            previewHtml += `
                <input type="text" class="form-control dgc-datepicker" data-picker-type="range" placeholder="Select date range" autocomplete="off" id="filter-preview-date-range">
            `;
        } else if (filterType === 'main_datepicker') {
            // Main datepicker with preset ranges (Today, Yesterday, Last 7 Days, etc.)
            previewHtml += `
                <input type="text" class="form-control dgc-datepicker" data-picker-type="main" placeholder="Select date range" autocomplete="off" id="filter-preview-main-datepicker">
            `;
        } else if (filterType === 'select') {
            if (isMultiple) {
                // Multi-select dropdown with checkboxes using Bootstrap dropdown
                previewHtml += `
                    <div class="dropdown filter-multiselect-dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle filter-multiselect-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
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
                            ${options.map((opt, index) => `
                                <div class="dropdown-item filter-multiselect-option">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="${this.escapeHtml(opt.value)}" id="preview-multiselect-${index}" ${opt.is_selected ? 'checked' : ''}>
                                        <label class="form-check-label" for="preview-multiselect-${index}">${this.escapeHtml(opt.label)}</label>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                // Single select dropdown
                previewHtml += '<select class="form-control filter-preview-select">';
                previewHtml += '<option value="">-- Select --</option>';
                options.forEach(opt => {
                    previewHtml += `<option value="${this.escapeHtml(opt.value)}">${this.escapeHtml(opt.label)}</option>`;
                });
                previewHtml += '</select>';
            }
        } else if (filterType === 'checkbox') {
            // Checkboxes
            const inlineClass = isInline ? ' inline' : '';
            previewHtml += `<div class="filter-preview-checkboxes${inlineClass}">`;
            options.forEach((opt, index) => {
                previewHtml += `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="preview_checkbox" value="${this.escapeHtml(opt.value)}" id="preview-checkbox-${index}" ${opt.is_selected ? 'checked' : ''}>
                        <label class="form-check-label" for="preview-checkbox-${index}">${this.escapeHtml(opt.label)}</label>
                    </div>
                `;
            });
            previewHtml += '</div>';
        } else if (filterType === 'radio') {
            // Radio buttons
            const inlineClass = isInline ? ' inline' : '';
            previewHtml += `<div class="filter-preview-radios${inlineClass}">`;
            options.forEach((opt, index) => {
                previewHtml += `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="preview_radio" value="${this.escapeHtml(opt.value)}" id="preview-radio-${index}" ${opt.is_selected ? 'checked' : ''}>
                        <label class="form-check-label" for="preview-radio-${index}">${this.escapeHtml(opt.label)}</label>
                    </div>
                `;
            });
            previewHtml += '</div>';
        } else if (filterType === 'tokeninput') {
            // Token input preview (simplified)
            previewHtml += `
                <div class="filter-preview-tokeninput">
                    <select class="form-control" multiple>
                        ${options.map(opt => `<option value="${this.escapeHtml(opt.value)}">${this.escapeHtml(opt.label)}</option>`).join('')}
                    </select>
                    <small class="text-muted">${options.length} options available</small>
                </div>
            `;
        }

        previewHtml += '</div></div>';
        return previewHtml;
    }

    // =============================================
    // Change Tracking & Status Indicators
    // =============================================

    /**
     * Initialize change tracking
     */
    initChangeTracking() {
        // Save initial state
        this.savedState = this.getCurrentState();

        // Track changes on form inputs
        const form = document.getElementById('filter-form');
        if (form) {
            form.addEventListener('input', () => this.checkForChanges());
            form.addEventListener('change', () => this.checkForChanges());
        }

        // Track CodeMirror changes
        if (this.queryEditor) {
            this.queryEditor.on('change', () => {
                this.checkForChanges();
                // Reset query test status when query changes
                this.queryTestPassed = false;
                this.queryError = null;
                this.updateStatusIndicators();
            });
        }

        // Track static option changes
        document.querySelectorAll('.filter-options-list').forEach(list => {
            list.addEventListener('input', () => this.checkForChanges());
        });

        // Warn before leaving page with unsaved changes
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });

        // Warn when clicking navigation links
        document.querySelectorAll('a[href]').forEach(link => {
            if (!link.classList.contains('no-unsaved-warning') &&
                !link.href.includes('javascript:') &&
                !link.getAttribute('href').startsWith('#')) {
                link.addEventListener('click', (e) => {
                    if (this.hasUnsavedChanges) {
                        if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                            e.preventDefault();
                        }
                    }
                });
            }
        });
    }

    /**
     * Get current form state for comparison
     */
    getCurrentState() {
        return {
            filterKey: document.getElementById('filter-key')?.value || '',
            filterLabel: document.getElementById('filter-label')?.value || '',
            filterType: document.getElementById('filter-type')?.value || '',
            dataSource: document.getElementById('data-source')?.value || '',
            defaultValue: document.getElementById('filter-default')?.value || '',
            isRequired: document.getElementById('filter-required')?.checked || false,
            isMultiple: document.getElementById('filter-multiple')?.checked || false,
            isInline: document.getElementById('filter-inline')?.checked || false,
            query: this.queryEditor ? this.queryEditor.getValue() : (document.getElementById('data-query')?.value || ''),
            staticOptions: this.getStaticOptionsString()
        };
    }

    /**
     * Get static options as string for comparison
     */
    getStaticOptionsString() {
        const options = [];
        document.querySelectorAll('.filter-option-item').forEach(row => {
            const value = row.querySelector('.option-value')?.value || '';
            const label = row.querySelector('.option-label')?.value || '';
            options.push(`${value}:${label}`);
        });
        return options.join('|');
    }

    /**
     * Check for changes and update state
     */
    checkForChanges() {
        const currentState = this.getCurrentState();
        const hasChanges = JSON.stringify(currentState) !== JSON.stringify(this.savedState);
        this.setUnsavedChanges(hasChanges);
    }

    /**
     * Set unsaved changes state
     */
    setUnsavedChanges(hasChanges) {
        this.hasUnsavedChanges = hasChanges;
        this.updateStatusIndicators();
    }

    /**
     * Update status indicators in page header
     */
    updateStatusIndicators() {
        const statusContainer = document.querySelector('.page-header-right .status-indicators');
        if (!statusContainer) return;

        let html = '';

        // Save indicator
        if (this.hasUnsavedChanges) {
            html += '<span class="save-indicator unsaved"><i class="fas fa-circle"></i> Unsaved</span>';
        } else {
            html += '<span class="save-indicator saved"><i class="fas fa-check"></i> Saved</span>';
        }

        // Error indicator (query error)
        if (this.queryError) {
            html += `<span class="status-box status-error" title="${this.escapeHtml(this.queryError)}">
                <i class="fas fa-times-circle"></i>
            </span>`;
        }

        // Query test status indicator (only for query data source)
        const dataSource = document.getElementById('data-source')?.value;
        const filterType = document.getElementById('filter-type')?.value;
        if (dataSource === 'query' && this.typesWithOptions.includes(filterType)) {
            if (this.queryTestPassed) {
                html += `<span class="status-box status-success" title="Query tested successfully">
                    <i class="fas fa-database"></i>
                </span>`;
            } else if (!this.queryError) {
                html += `<span class="status-box status-warning" title="Query not tested yet">
                    <i class="fas fa-database"></i>
                </span>`;
            }
        }

        statusContainer.innerHTML = html;
    }

    /**
     * Set query error
     */
    setQueryError(error) {
        this.queryError = error;
        this.queryTestPassed = false;
        this.updateStatusIndicators();
    }

    /**
     * Clear query error and mark as tested
     */
    setQueryTestPassed() {
        this.queryError = null;
        this.queryTestPassed = true;
        this.updateStatusIndicators();
    }

    /**
     * Mark state as saved
     */
    markAsSaved() {
        this.savedState = this.getCurrentState();
        this.hasUnsavedChanges = false;
        this.updateStatusIndicators();
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
