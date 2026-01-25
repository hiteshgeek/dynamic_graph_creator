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
        this.initSidebarCollapse();
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
        // Filter type custom dropdown
        this.initFilterTypeDropdown();

        // Filter type change - show/hide options section and select config
        const filterType = document.getElementById('filter-type');
        if (filterType) {
            filterType.addEventListener('change', () => {
                this.onFilterTypeChange();
                this.updateDefaultValueSection();
            });
            // Initialize date range info on page load
            this.onFilterTypeChange();
        }

        // Required checkbox change - show/hide default value section
        const requiredCheckbox = document.getElementById('filter-required');
        if (requiredCheckbox) {
            requiredCheckbox.addEventListener('change', () => this.updateDefaultValueSection());
            // Initialize default value section on page load
            // Then re-initialize change tracking AFTER default value UI is rendered
            setTimeout(() => {
                this.updateDefaultValueSection();
                // Re-save initial state now that default value UI is rendered
                this.savedState = this.getCurrentState();
            }, 100);
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
                        // Immediate update for remove action (bypass debounce)
                        this.updateStaticPreviewImmediate();
                    }
                }
            });

            // Auto-update preview when typing in option fields (debounced)
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
            multipleCheckbox.addEventListener('change', () => {
                this.updatePreview();
                this.updateDefaultValueSection(); // Also update default value section
            });
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
     * Initialize custom filter type dropdown
     */
    initFilterTypeDropdown() {
        const dropdown = document.querySelector('.filter-type-dropdown');
        if (!dropdown) return;

        const hiddenInput = dropdown.querySelector('#filter-type');
        const placeholder = dropdown.querySelector('.filter-select-placeholder');
        const options = dropdown.querySelectorAll('.filter-select-option');
        const searchInput = dropdown.querySelector('.select-search');

        // Handle option selection
        options.forEach(option => {
            const radio = option.querySelector('input[type="radio"]');
            const label = option.querySelector('.form-check-label');

            // Click on option selects radio and updates display
            option.addEventListener('click', (e) => {
                if (e.target.tagName !== 'INPUT') {
                    e.preventDefault();
                    if (radio) radio.checked = true;
                }

                const value = option.dataset.value;
                const labelText = label ? label.textContent.trim() : '';
                const iconEl = label ? label.querySelector('i') : null;
                const iconHtml = iconEl ? iconEl.outerHTML : '';

                // Update hidden input
                hiddenInput.value = value;

                // Update placeholder display
                placeholder.innerHTML = iconHtml + this.escapeHtml(labelText);
                placeholder.classList.add('has-selection');

                // Close dropdown
                const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.querySelector('.dropdown-toggle'));
                if (bsDropdown) bsDropdown.hide();

                // Trigger change event on hidden input
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase().trim();
                options.forEach(option => {
                    const label = option.querySelector('.form-check-label')?.textContent.toLowerCase() || '';
                    option.style.display = (searchTerm === '' || label.includes(searchTerm)) ? '' : 'none';
                });
            });

            searchInput.addEventListener('click', (e) => e.stopPropagation());
        }
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

        // Update default value section based on new data source options
        this.updateDefaultValueSection();
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
        // Immediate update for add action
        this.updateStaticPreviewImmediate();
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
     * Auto-update static options preview (debounced for typing)
     */
    updateStaticPreview() {
        const dataSource = document.getElementById('data-source').value;
        if (dataSource !== 'static') return;

        // Debounce to prevent excessive updates while typing
        if (this.staticPreviewDebounce) {
            clearTimeout(this.staticPreviewDebounce);
        }

        this.staticPreviewDebounce = setTimeout(() => {
            this.updateStaticPreviewImmediate();
        }, 150);
    }

    /**
     * Immediate static preview update (no debounce)
     */
    updateStaticPreviewImmediate() {
        const dataSource = document.getElementById('data-source').value;
        if (dataSource !== 'static') return;

        const options = this.getStaticOptions();
        if (options.length > 0) {
            this.showFilterPreview(options);
            // Refresh default value section with updated options
            this.updateDefaultValueSection();
        } else {
            const previewSection = document.getElementById('filter-preview-section');
            if (previewSection) {
                previewSection.style.display = 'none';
            }
            // Still update default value section to show "no options" message
            this.updateDefaultValueSection();
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
                            // Refresh default value section now that options are loaded
                            this.updateDefaultValueSection();
                        }

                        // Store data for pagination
                        this.queryResultHasIsSelected = hasIsSelected;
                    }

                    resultDiv.className = 'query-test-result success';
                    resultDiv.innerHTML = html;

                    // Bind pagination events
                    this.bindQueryPagination();

                    // Mark query test as passed and show toast (only on first page)
                    this.setQueryTestPassed();
                    if (page === 1) {
                        Toast.success(`Query is valid - ${totalCount} row${totalCount !== 1 ? 's' : ''} returned`);
                    }
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

        // Validate data source for filter types that require options
        if (this.typesWithOptions.includes(filterType)) {
            if (dataSource === 'query') {
                // Query data source - must be tested successfully
                const query = this.queryEditor ? this.queryEditor.getValue() : document.getElementById('data-query')?.value;
                if (!query || !query.trim()) {
                    Toast.error('Please enter a SQL query');
                    return;
                }
                if (!this.queryTestPassed) {
                    Toast.error('Please test the query successfully before saving');
                    return;
                }
            } else if (dataSource === 'static') {
                // Static data source - at least one option is required
                if (!this.hasStaticOptions()) {
                    Toast.error('Please add at least one option for static data source');
                    return;
                }
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

        // Collect mandatory widget types
        const mandatoryWidgetTypes = [];
        document.querySelectorAll('.mandatory-widget-type:checked').forEach(checkbox => {
            mandatoryWidgetTypes.push(parseInt(checkbox.value));
        });

        // Get default value (JSON for required filters)
        const isRequired = document.getElementById('filter-required').checked ? 1 : 0;
        const defaultValue = this.getDefaultValueJson();

        // Validate required filter has meaningful default value
        if (isRequired && !this.hasValidDefaultValue(defaultValue)) {
            Toast.error('Default value is required when filter is marked as required');
            return;
        }

        // Validate specific values mode has at least one value selected
        const defaultValueError = this.validateDefaultValue(defaultValue);
        if (defaultValueError) {
            Toast.error(defaultValueError);
            return;
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
            default_value: defaultValue,
            is_required: isRequired,
            is_system: document.getElementById('filter-is-system')?.checked ? 1 : 0,
            mandatory_widget_types: JSON.stringify(mandatoryWidgetTypes)
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

        // Debug: log what's being sent
        console.log('Saving filter data:', data);
        console.log('is_system checkbox:', document.getElementById('filter-is-system'));
        console.log('mandatory checkboxes:', document.querySelectorAll('.mandatory-widget-type:checked'));

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

        // Initialize single select dropdowns with FilterRenderer
        if (typeof FilterRenderer !== 'undefined') {
            FilterRenderer.init(section);
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
                // Single select dropdown with search (using FilterRenderer structure)
                const selectedOption = options.find(opt => opt.is_selected);
                const selectedLabel = selectedOption ? selectedOption.label : '-- Select --';

                previewHtml += `
                    <div class="dropdown filter-select-dropdown" data-filter-name="preview_select">
                        <button class="btn btn-outline-secondary dropdown-toggle filter-select-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <span class="filter-select-placeholder${selectedOption ? ' has-selection' : ''}">${this.escapeHtml(selectedLabel)}</span>
                        </button>
                        <div class="dropdown-menu filter-select-options">
                            <div class="filter-select-header">
                                <input type="text" class="form-control form-control-sm select-search" placeholder="Search...">
                            </div>
                            <div class="dropdown-item filter-select-option" data-value="">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="preview_select" value="" id="preview-select-none" ${!selectedOption ? 'checked' : ''}>
                                    <label class="form-check-label" for="preview-select-none">-- Select --</label>
                                </div>
                            </div>
                            ${options.map((opt, index) => `
                                <div class="dropdown-item filter-select-option" data-value="${this.escapeHtml(opt.value)}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="preview_select" value="${this.escapeHtml(opt.value)}" id="preview-select-${index}" ${opt.is_selected ? 'checked' : ''}>
                                        <label class="form-check-label" for="preview-select-${index}">${this.escapeHtml(opt.label)}</label>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <input type="hidden" class="filter-input" name="preview_select" data-filter-key="preview_select" value="${selectedOption ? this.escapeHtml(selectedOption.value) : ''}">
                    </div>
                `;
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
        // Save initial state using original stored default value
        // (not the dynamically generated one which may differ)
        this.savedState = this.getCurrentState(true);

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
     * @param {boolean} useOriginalDefault - If true, use the original stored default value instead of generating
     */
    getCurrentState(useOriginalDefault = false) {
        // Get mandatory widget types
        const mandatoryWidgetTypes = [];
        document.querySelectorAll('.mandatory-widget-type:checked').forEach(checkbox => {
            mandatoryWidgetTypes.push(parseInt(checkbox.value));
        });

        // For initial state, use the original stored value to avoid false positives
        let defaultValue;
        if (useOriginalDefault) {
            const section = document.getElementById('default-value-section');
            defaultValue = section?.dataset.existingValue || '';
        } else {
            defaultValue = this.getDefaultValueJson() || '';
        }

        return {
            filterKey: document.getElementById('filter-key')?.value || '',
            filterLabel: document.getElementById('filter-label')?.value || '',
            filterType: document.getElementById('filter-type')?.value || '',
            dataSource: document.getElementById('data-source')?.value || '',
            defaultValue: defaultValue,
            isRequired: document.getElementById('filter-required')?.checked || false,
            isSystem: document.getElementById('filter-is-system')?.checked || false,
            mandatoryWidgetTypes: mandatoryWidgetTypes.sort().join(','),
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

        // Dispose existing tooltips before updating HTML
        statusContainer.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            const tooltip = bootstrap.Tooltip.getInstance(el);
            if (tooltip) tooltip.dispose();
        });

        let html = '';

        // Save indicator
        if (this.hasUnsavedChanges) {
            html += '<span class="save-indicator unsaved"><i class="fas fa-circle"></i> Unsaved</span>';
        } else {
            html += '<span class="save-indicator saved"><i class="fas fa-check"></i> Saved</span>';
        }

        // Error indicator (query error)
        if (this.queryError) {
            html += `<span class="status-box status-error" data-bs-toggle="tooltip" data-bs-placement="bottom" title="${this.escapeHtml(this.queryError)}">
                <i class="fas fa-times-circle"></i>
            </span>`;
        }

        // Data source status indicator (only for filter types that have options)
        const dataSource = document.getElementById('data-source')?.value;
        const filterType = document.getElementById('filter-type')?.value;

        // Only show data source icon for types that have options
        if (this.typesWithOptions.includes(filterType)) {
            if (dataSource === 'query') {
                // Query data source
                if (this.queryTestPassed) {
                    html += `<span class="status-box status-success" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Query tested successfully">
                        <i class="fas fa-database"></i>
                    </span>`;
                } else if (!this.queryError) {
                    html += `<span class="status-box status-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Query not tested yet">
                        <i class="fas fa-database"></i>
                    </span>`;
                }
            } else if (dataSource === 'static') {
                // Static data source - check if at least one option exists
                const hasOptions = this.hasStaticOptions();
                if (hasOptions) {
                    html += `<span class="status-box status-success" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Static options configured">
                        <i class="fas fa-list-ul"></i>
                    </span>`;
                } else {
                    html += `<span class="status-box status-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="At least one option is required">
                        <i class="fas fa-list-ul"></i>
                    </span>`;
                }
            }
        }

        statusContainer.innerHTML = html;

        // Initialize Bootstrap tooltips for the new elements
        statusContainer.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el);
        });
    }

    /**
     * Check if there are valid static options
     */
    hasStaticOptions() {
        const options = this.getStaticOptions();
        return options.length > 0 && options.some(opt => opt.value || opt.label);
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
     * Initialize sidebar collapse functionality
     */
    initSidebarCollapse() {
        const sidebar = document.querySelector('.data-filter-form-sidebar');
        const sidebarCard = sidebar?.querySelector('.sidebar-card');
        const header = sidebar?.querySelector('.sidebar-card-header');
        const collapseBtn = sidebar?.querySelector('.collapse-btn');

        if (!sidebar || !sidebarCard) return;

        // Restore collapsed state from localStorage
        const isCollapsed = localStorage.getItem('dataFilterSidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            sidebarCard.classList.add('collapsed');
        }

        // Toggle on header click
        if (header) {
            header.addEventListener('click', (e) => {
                // Don't toggle if clicking a link inside the header
                if (e.target.closest('a')) return;
                this.toggleSidebar();
            });
        }

        // Toggle on collapse button click
        if (collapseBtn) {
            collapseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleSidebar();
            });
        }
    }

    /**
     * Toggle sidebar collapsed state
     */
    toggleSidebar() {
        const sidebar = document.querySelector('.data-filter-form-sidebar');
        const sidebarCard = sidebar?.querySelector('.sidebar-card');

        if (!sidebar || !sidebarCard) return;

        sidebar.classList.toggle('collapsed');
        sidebarCard.classList.toggle('collapsed');

        // Save state to localStorage
        localStorage.setItem('dataFilterSidebarCollapsed', sidebar.classList.contains('collapsed') ? 'true' : 'false');
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // =============================================
    // Default Value Section for Required Filters
    // =============================================

    /**
     * Update default value section visibility and content
     * Section is always visible, but default value is mandatory when filter is required
     */
    updateDefaultValueSection() {
        const section = document.getElementById('default-value-section');
        const content = document.getElementById('default-value-content');
        const isRequired = document.getElementById('filter-required')?.checked;
        const label = section?.querySelector('.form-label');

        if (!section || !content) {
            return;
        }

        // Update label to show required indicator when filter is required
        if (label) {
            const requiredSpan = label.querySelector('.required');
            if (isRequired && !requiredSpan) {
                label.innerHTML = 'Default Value <span class="required">*</span>';
            } else if (!isRequired && requiredSpan) {
                label.innerHTML = 'Default Value';
            }
        }
        const filterType = document.getElementById('filter-type')?.value || 'text';
        this.renderDefaultValueInput(filterType, content);
    }

    /**
     * Render appropriate default value input based on filter type
     */
    renderDefaultValueInput(filterType, container) {
        const section = document.getElementById('default-value-section');

        // Try to get current value from UI first (user may have made changes)
        // Fall back to stored existing value
        let existingValue = '';
        try {
            const currentValue = this.getDefaultValueJson();
            const parsed = JSON.parse(currentValue);
            // Only use current value if it has meaningful content
            if (this.hasValidDefaultValue(currentValue)) {
                existingValue = currentValue;
            } else {
                existingValue = section?.dataset.existingValue || '';
            }
        } catch (e) {
            existingValue = section?.dataset.existingValue || '';
        }

        let parsedValue = null;
        try {
            parsedValue = existingValue ? JSON.parse(existingValue) : null;
        } catch (e) {
            // Legacy plain string value
            parsedValue = existingValue ? { value: existingValue } : null;
        }

        let html = '';

        // Check if select type has "Allow multiple selection" checked
        const isMultipleSelect = filterType === 'select' && document.getElementById('filter-multiple')?.checked;
        // Check if filter is required (to show/hide "Block Until Selected" option)
        const isRequired = document.getElementById('filter-required')?.checked || false;

        switch (filterType) {
            case 'text':
                html = this.renderTextDefaultInput(parsedValue);
                break;
            case 'number':
                html = this.renderNumberDefaultInput(parsedValue);
                break;
            case 'date':
                html = this.renderDateDefaultInput(parsedValue);
                break;
            case 'date_range':
                html = this.renderDateRangeDefaultInput(parsedValue, false, isRequired);
                break;
            case 'main_datepicker':
                html = this.renderDateRangeDefaultInput(parsedValue, true, isRequired);
                break;
            case 'select':
                // Use multi-select input if "Allow multiple selection" is checked
                if (isMultipleSelect) {
                    html = this.renderMultiSelectDefaultInput(parsedValue, isRequired);
                } else {
                    html = this.renderSelectDefaultInput(parsedValue, isRequired);
                }
                break;
            case 'radio':
                html = this.renderSelectDefaultInput(parsedValue, isRequired);
                break;
            case 'multi_select':
                html = this.renderMultiSelectDefaultInput(parsedValue, isRequired);
                break;
            case 'checkbox':
                html = this.renderCheckboxDefaultInput(parsedValue, isRequired);
                break;
            case 'tokeninput':
                html = this.renderTokenInputDefault(parsedValue);
                break;
            default:
                html = this.renderTextDefaultInput(parsedValue);
        }

        container.innerHTML = html;
        this.initDefaultValueInputs();
    }

    /**
     * Render text default value input
     */
    renderTextDefaultInput(parsedValue) {
        const value = parsedValue?.value || '';
        return `<input type="text" class="form-control" id="default-value-text" placeholder="Enter default text value" value="${this.escapeHtml(value)}">`;
    }

    /**
     * Render number default value input
     */
    renderNumberDefaultInput(parsedValue) {
        const value = parsedValue?.value || '';
        return `<input type="number" class="form-control" id="default-value-number" placeholder="Enter default number" value="${this.escapeHtml(value)}">`;
    }

    /**
     * Render date default value input
     */
    renderDateDefaultInput(parsedValue) {
        const value = parsedValue?.value || '';
        return `<input type="text" class="form-control dgc-datepicker" data-picker-type="single" id="default-value-date" placeholder="Select default date" autocomplete="off" value="${this.escapeHtml(value)}">`;
    }

    /**
     * Render date range default value input with mode selection
     */
    renderDateRangeDefaultInput(parsedValue, showPresets = false, isRequired = false) {
        const mode = parsedValue?.mode || 'select_all';
        const preset = parsedValue?.preset || 'Last 7 Days';
        const fromDate = parsedValue?.from || '';
        const toDate = parsedValue?.to || '';

        let html = `
            <div class="default-value-mode-options">
                <div class="form-check">
                    <input class="form-check-input default-mode-radio" type="radio" name="default-mode" value="select_all" id="mode-select-all" ${mode === 'select_all' ? 'checked' : ''}>
                    <label class="form-check-label" for="mode-select-all">
                        <strong>Select All</strong>
                        <small class="d-block text-muted">No date filter will be applied by default</small>
                    </label>
                </div>
        `;

        if (showPresets) {
            html += `
                <div class="form-check">
                    <input class="form-check-input default-mode-radio" type="radio" name="default-mode" value="preset" id="mode-preset" ${mode === 'preset' ? 'checked' : ''}>
                    <label class="form-check-label" for="mode-preset">
                        <strong>Preset Range</strong>
                        <small class="d-block text-muted">Use a predefined date range</small>
                    </label>
                </div>
                <div class="default-preset-options ms-4 mt-2" id="preset-options" style="${mode === 'preset' ? '' : 'display:none;'}">
                    <select class="form-select form-select-sm" id="default-preset">
                        <option value="Today" ${preset === 'Today' ? 'selected' : ''}>Today</option>
                        <option value="Yesterday" ${preset === 'Yesterday' ? 'selected' : ''}>Yesterday</option>
                        <option value="Last 7 Days" ${preset === 'Last 7 Days' ? 'selected' : ''}>Last 7 Days</option>
                        <option value="Last 30 Days" ${preset === 'Last 30 Days' ? 'selected' : ''}>Last 30 Days</option>
                        <option value="This Month" ${preset === 'This Month' ? 'selected' : ''}>This Month</option>
                        <option value="Last Month" ${preset === 'Last Month' ? 'selected' : ''}>Last Month</option>
                        <option value="Year to Date" ${preset === 'Year to Date' ? 'selected' : ''}>Year to Date</option>
                        <option value="This Financial Year" ${preset === 'This Financial Year' ? 'selected' : ''}>This Financial Year</option>
                        <option value="Last Financial Year" ${preset === 'Last Financial Year' ? 'selected' : ''}>Last Financial Year</option>
                    </select>
                </div>
            `;
        }

        html += `
                <div class="form-check">
                    <input class="form-check-input default-mode-radio" type="radio" name="default-mode" value="specific" id="mode-specific" ${mode === 'specific' ? 'checked' : ''}>
                    <label class="form-check-label" for="mode-specific">
                        <strong>Specific Dates</strong>
                        <small class="d-block text-muted">Set fixed default date range</small>
                    </label>
                </div>
                <div class="default-specific-dates ms-4 mt-2" id="specific-dates" style="${mode === 'specific' ? '' : 'display:none;'}">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">From</label>
                            <input type="text" class="form-control form-control-sm dgc-datepicker" data-picker-type="single" id="default-date-from" placeholder="Start date" value="${this.escapeHtml(fromDate)}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">To</label>
                            <input type="text" class="form-control form-control-sm dgc-datepicker" data-picker-type="single" id="default-date-to" placeholder="End date" value="${this.escapeHtml(toDate)}">
                        </div>
                    </div>
                </div>
        `;

        // Only show "Block Until Selected" when filter is required
        if (isRequired) {
            html += `
                <div class="form-check">
                    <input class="form-check-input default-mode-radio" type="radio" name="default-mode" value="block" id="mode-block" ${mode === 'block' ? 'checked' : ''}>
                    <label class="form-check-label" for="mode-block">
                        <strong>Block Until Selected</strong>
                        <small class="d-block text-muted">Query won't run until user selects a date range</small>
                    </label>
                </div>
            `;
        }

        html += `</div>`;

        return html;
    }

    /**
     * Render select/radio default value input
     */
    renderSelectDefaultInput(parsedValue, isRequired = false) {
        const selectedValue = parsedValue?.value || '';
        const mode = parsedValue?.mode || (isRequired ? 'value' : 'none');
        const options = this.getCurrentOptions();

        // Find selected option for display
        const selectedOption = options.find(opt => opt.value === selectedValue);
        const selectedLabel = selectedOption ? selectedOption.label : '-- Select --';

        let html = `
            <div class="default-value-mode-options">
        `;

        // Show "No Default" option only when filter is NOT required
        if (!isRequired) {
            html += `
                <div class="form-check">
                    <input class="form-check-input default-select-mode-radio" type="radio" name="default-select-mode" value="none" id="select-mode-none" ${mode === 'none' ? 'checked' : ''}>
                    <label class="form-check-label" for="select-mode-none">
                        <strong>No Default</strong>
                        <small class="d-block text-muted">No option will be pre-selected by default</small>
                    </label>
                </div>
            `;
        }

        html += `
                <div class="form-check">
                    <input class="form-check-input default-select-mode-radio" type="radio" name="default-select-mode" value="value" id="select-mode-value" ${mode === 'value' ? 'checked' : ''}>
                    <label class="form-check-label" for="select-mode-value">
                        <strong>Default Value</strong>
                        <small class="d-block text-muted">Pre-select a specific option</small>
                    </label>
                </div>
                <div class="default-select-value-wrapper ms-4 mt-2" id="select-value-wrapper" style="${mode === 'value' ? '' : 'display:none;'}">
        `;

        if (options.length === 0) {
            html += `<div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                Configure filter options first (static or query), then return here to set default.
            </div>`;
        } else {
            // Use custom dropdown matching filter preview
            html += `
                <div class="dropdown filter-select-dropdown default-select-dropdown" data-filter-name="default_select">
                    <button class="btn btn-outline-secondary dropdown-toggle filter-select-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <span class="filter-select-placeholder${selectedOption ? ' has-selection' : ''}">${this.escapeHtml(selectedLabel)}</span>
                    </button>
                    <div class="dropdown-menu filter-select-options">
                        <div class="filter-select-header">
                            <input type="text" class="form-control form-control-sm select-search" placeholder="Search...">
                        </div>
                        <div class="dropdown-item filter-select-option" data-value="">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="default_select" value="" id="default-select-none" ${!selectedOption ? 'checked' : ''}>
                                <label class="form-check-label" for="default-select-none">-- Select --</label>
                            </div>
                        </div>
                        ${options.map((opt, index) => `
                            <div class="dropdown-item filter-select-option" data-value="${this.escapeHtml(opt.value)}">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="default_select" value="${this.escapeHtml(opt.value)}" id="default-select-${index}" ${opt.value === selectedValue ? 'checked' : ''}>
                                    <label class="form-check-label" for="default-select-${index}">${this.escapeHtml(opt.label)}</label>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <input type="hidden" class="filter-input" id="default-value-select" name="default_select" data-filter-key="default_select" value="${selectedOption ? this.escapeHtml(selectedValue) : ''}">
                </div>
            `;
        }

        html += `</div>`;

        // Only show "Block Until Selected" when filter is required
        if (isRequired) {
            html += `
                <div class="form-check mt-2">
                    <input class="form-check-input default-select-mode-radio" type="radio" name="default-select-mode" value="block" id="select-mode-block" ${mode === 'block' ? 'checked' : ''}>
                    <label class="form-check-label" for="select-mode-block">
                        <strong>Block Until Selected</strong>
                        <small class="d-block text-muted">Query won't run until user selects an option</small>
                    </label>
                </div>
            `;
        }

        html += `</div>`;

        return html;
    }

    /**
     * Render multi-select default value input (dropdown style)
     */
    renderMultiSelectDefaultInput(parsedValue, isRequired = false) {
        const selectedValues = parsedValue?.values || [];
        const mode = parsedValue?.mode || (isRequired ? 'values' : 'none');
        const options = this.getCurrentOptions();

        // Calculate placeholder text
        const checkedCount = selectedValues.length;
        const placeholderText = checkedCount > 0 ? `${checkedCount} selected` : '-- Select multiple --';

        let html = `
            <div class="default-value-mode-options">
        `;

        // Show "None Selected" option only when filter is NOT required
        if (!isRequired) {
            html += `
                <div class="form-check">
                    <input class="form-check-input default-multi-mode-radio" type="radio" name="default-multi-mode" value="none" id="multi-mode-none" ${mode === 'none' ? 'checked' : ''}>
                    <label class="form-check-label" for="multi-mode-none">
                        <strong>None Selected</strong>
                        <small class="d-block text-muted">No options will be pre-selected by default</small>
                    </label>
                </div>
            `;
        }

        html += `
                <div class="form-check">
                    <input class="form-check-input default-multi-mode-radio" type="radio" name="default-multi-mode" value="all" id="multi-mode-all" ${mode === 'all' ? 'checked' : ''}>
                    <label class="form-check-label" for="multi-mode-all">
                        <strong>Select All</strong>
                        <small class="d-block text-muted">All options will be pre-selected (includes future additions)</small>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input default-multi-mode-radio" type="radio" name="default-multi-mode" value="values" id="multi-mode-values" ${mode === 'values' ? 'checked' : ''}>
                    <label class="form-check-label" for="multi-mode-values">
                        <strong>Specific Values</strong>
                        <small class="d-block text-muted">Pre-select specific options</small>
                    </label>
                </div>
                <div class="default-multi-value-wrapper ms-4 mt-2" id="multi-value-wrapper" style="${mode === 'values' ? '' : 'display:none;'}">
        `;

        if (options.length === 0) {
            html += `<div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                Configure filter options first (static or query), then return here to set default.
            </div>`;
        } else {
            // Use same dropdown structure as filter preview
            html += `
                <div class="dropdown filter-multiselect-dropdown default-multiselect-dropdown" data-filter-name="default_multi">
                    <button class="btn btn-outline-secondary dropdown-toggle filter-multiselect-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <span class="filter-multiselect-placeholder${checkedCount > 0 ? ' has-selection' : ''}">${placeholderText}</span>
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
                                    <input class="form-check-input default-multi-checkbox" type="checkbox" value="${this.escapeHtml(opt.value)}" id="default-cb-${index}" ${selectedValues.includes(opt.value) ? 'checked' : ''}>
                                    <label class="form-check-label" for="default-cb-${index}">${this.escapeHtml(opt.label)}</label>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        html += `</div>`;

        // Only show "Block Until Selected" when filter is required
        if (isRequired) {
            html += `
                <div class="form-check mt-2">
                    <input class="form-check-input default-multi-mode-radio" type="radio" name="default-multi-mode" value="block" id="multi-mode-block" ${mode === 'block' ? 'checked' : ''}>
                    <label class="form-check-label" for="multi-mode-block">
                        <strong>Block Until Selected</strong>
                        <small class="d-block text-muted">Query won't run until user selects at least one option</small>
                    </label>
                </div>
            `;
        }

        html += `</div>`;

        return html;
    }

    /**
     * Render checkbox default value input (inline checkboxes, not dropdown)
     */
    renderCheckboxDefaultInput(parsedValue, isRequired = false) {
        const selectedValues = parsedValue?.values || [];
        const mode = parsedValue?.mode || (isRequired ? 'values' : 'none');
        const options = this.getCurrentOptions();

        let html = `
            <div class="default-value-mode-options">
        `;

        // Show "None Selected" option only when filter is NOT required
        if (!isRequired) {
            html += `
                <div class="form-check">
                    <input class="form-check-input default-multi-mode-radio" type="radio" name="default-multi-mode" value="none" id="multi-mode-none" ${mode === 'none' ? 'checked' : ''}>
                    <label class="form-check-label" for="multi-mode-none">
                        <strong>None Selected</strong>
                        <small class="d-block text-muted">No options will be pre-selected by default</small>
                    </label>
                </div>
            `;
        }

        html += `
                <div class="form-check">
                    <input class="form-check-input default-multi-mode-radio" type="radio" name="default-multi-mode" value="all" id="multi-mode-all" ${mode === 'all' ? 'checked' : ''}>
                    <label class="form-check-label" for="multi-mode-all">
                        <strong>Select All</strong>
                        <small class="d-block text-muted">All options will be pre-selected (includes future additions)</small>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input default-multi-mode-radio" type="radio" name="default-multi-mode" value="values" id="multi-mode-values" ${mode === 'values' ? 'checked' : ''}>
                    <label class="form-check-label" for="multi-mode-values">
                        <strong>Specific Values</strong>
                        <small class="d-block text-muted">Pre-select specific options</small>
                    </label>
                </div>
                <div class="default-multi-value-wrapper ms-4 mt-2" id="multi-value-wrapper" style="${mode === 'values' ? '' : 'display:none;'}">
        `;

        if (options.length === 0) {
            html += `<div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                Configure filter options first (static or query), then return here to set default.
            </div>`;
        } else {
            // Inline checkboxes (not dropdown)
            html += `
                <div class="default-checkbox-inline">
                    <div class="default-checkbox-actions">
                        <button type="button" class="btn btn-link btn-sm p-0 default-cb-select-all">All</button>
                        <span class="divider">|</span>
                        <button type="button" class="btn btn-link btn-sm p-0 default-cb-select-none">None</button>
                    </div>
                    ${options.map((opt, index) => `
                        <div class="form-check">
                            <input class="form-check-input default-multi-checkbox" type="checkbox" value="${this.escapeHtml(opt.value)}" id="default-cb-${index}" ${selectedValues.includes(opt.value) ? 'checked' : ''}>
                            <label class="form-check-label" for="default-cb-${index}">${this.escapeHtml(opt.label)}</label>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        html += `</div>`;

        // Only show "Block Until Selected" when filter is required
        if (isRequired) {
            html += `
                <div class="form-check mt-2">
                    <input class="form-check-input default-multi-mode-radio" type="radio" name="default-multi-mode" value="block" id="multi-mode-block" ${mode === 'block' ? 'checked' : ''}>
                    <label class="form-check-label" for="multi-mode-block">
                        <strong>Block Until Selected</strong>
                        <small class="d-block text-muted">Query won't run until user selects at least one option</small>
                    </label>
                </div>
            `;
        }

        html += `</div>`;

        return html;
    }

    /**
     * Render token input default value
     */
    renderTokenInputDefault(parsedValue) {
        const values = parsedValue?.values || [];
        return `
            <input type="text" class="form-control" id="default-value-tokens"
                   placeholder="Enter comma-separated default values"
                   value="${this.escapeHtml(values.join(', '))}">
            <small class="form-hint d-block mt-1">Enter values separated by commas</small>
        `;
    }

    /**
     * Get current filter options from static or query results
     */
    getCurrentOptions() {
        const dataSource = document.getElementById('data-source')?.value || 'static';

        if (dataSource === 'static') {
            return this.getStaticOptions();
        } else if (this.lastQueryOptions && this.lastQueryOptions.length > 0) {
            return this.lastQueryOptions;
        }

        return [];
    }

    /**
     * Initialize event handlers for default value inputs
     */
    initDefaultValueInputs() {
        // Mode radio buttons for date range
        const modeRadios = document.querySelectorAll('.default-mode-radio');
        modeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                const mode = radio.value;
                const presetOptions = document.getElementById('preset-options');
                const specificDates = document.getElementById('specific-dates');

                if (presetOptions) {
                    presetOptions.style.display = mode === 'preset' ? '' : 'none';
                }
                if (specificDates) {
                    specificDates.style.display = mode === 'specific' ? '' : 'none';
                }

                // When switching to preset mode, sync the preset to datepicker preview
                if (mode === 'preset') {
                    const preset = document.getElementById('default-preset')?.value || 'Last 7 Days';
                    setTimeout(() => this.syncPresetToDatepickerPreview(preset), 50);
                }
            });
        });

        // Mode radio buttons for select (single)
        const selectModeRadios = document.querySelectorAll('.default-select-mode-radio');
        selectModeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                const mode = radio.value;
                const valueWrapper = document.getElementById('select-value-wrapper');
                if (valueWrapper) {
                    valueWrapper.style.display = mode === 'value' ? '' : 'none';
                }
                // Sync preview when mode changes (for "none" mode)
                this.syncDefaultToPreview();
            });
        });

        // Mode radio buttons for multi-select
        const multiModeRadios = document.querySelectorAll('.default-multi-mode-radio');
        multiModeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                const mode = radio.value;
                const valueWrapper = document.getElementById('multi-value-wrapper');
                if (valueWrapper) {
                    valueWrapper.style.display = mode === 'values' ? '' : 'none';
                }
                // Sync preview when mode changes (for "all" mode)
                this.syncDefaultToPreview();
            });
        });

        const section = document.getElementById('default-value-section');
        if (!section) return;

        // Initialize single select custom dropdown with FilterRenderer
        if (typeof FilterRenderer !== 'undefined') {
            FilterRenderer.init(section);
        }

        // Bind single select dropdown to sync with preview
        const singleSelectDropdown = section.querySelector('.default-select-dropdown');
        if (singleSelectDropdown) {
            this.bindDefaultSelectDropdown(singleSelectDropdown);
        }

        // Initialize multiselect dropdown
        const multiselectDropdown = section.querySelector('.default-multiselect-dropdown');
        if (multiselectDropdown) {
            this.bindDefaultMultiselectDropdown(multiselectDropdown);
        }

        // Initialize inline checkbox All/None buttons (for checkbox filter type)
        const inlineCheckboxContainer = section.querySelector('.default-checkbox-inline');
        if (inlineCheckboxContainer) {
            this.bindInlineCheckboxActions(inlineCheckboxContainer);
        }

        // Initialize date pickers if present
        if (typeof DatePickerInit !== 'undefined') {
            DatePickerInit.init(section);
        }

        // Bind preset dropdown to sync with datepicker preview
        const presetDropdown = document.getElementById('default-preset');
        if (presetDropdown) {
            presetDropdown.addEventListener('change', () => {
                this.syncPresetToDatepickerPreview(presetDropdown.value);
            });
        }

        // Sync default values to preview (for pre-existing values)
        // Use setTimeout to ensure preview is rendered before sync
        setTimeout(() => this.syncDefaultToPreview(), 50);

        // Also sync preset to datepicker preview if preset mode is selected
        setTimeout(() => {
            const selectedMode = document.querySelector('input[name="default-mode"]:checked')?.value;
            if (selectedMode === 'preset') {
                const preset = document.getElementById('default-preset')?.value || 'Last 7 Days';
                this.syncPresetToDatepickerPreview(preset);
            }
        }, 100);
    }

    /**
     * Bind events for default value single select dropdown
     */
    bindDefaultSelectDropdown(dropdown) {
        const options = dropdown.querySelectorAll('.filter-select-option');
        options.forEach(option => {
            option.addEventListener('click', () => {
                // Wait for FilterRenderer to update the trigger, then sync to preview
                setTimeout(() => this.syncDefaultToPreview(), 10);
            });
        });
    }

    /**
     * Bind events for default value multiselect dropdown
     */
    bindDefaultMultiselectDropdown(dropdown) {
        const placeholder = dropdown.querySelector('.filter-multiselect-placeholder');
        const optionItems = dropdown.querySelectorAll('.filter-multiselect-option');
        const selectAllBtn = dropdown.querySelector('.multiselect-select-all');
        const selectNoneBtn = dropdown.querySelector('.multiselect-select-none');
        const searchInput = dropdown.querySelector('.multiselect-search');

        // Update placeholder text based on selection
        const updatePlaceholder = () => {
            const checkboxes = dropdown.querySelectorAll('.default-multi-checkbox');
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
                checkbox.addEventListener('change', () => {
                    updatePlaceholder();
                    this.syncDefaultToPreview();
                });
            }
        });

        // Select All button
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
                this.syncDefaultToPreview();
            });
        }

        // Select None button
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
                this.syncDefaultToPreview();
            });
        }

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase().trim();
                optionItems.forEach(item => {
                    const label = item.querySelector('.form-check-label')?.textContent.toLowerCase() || '';
                    item.style.display = (searchTerm === '' || label.includes(searchTerm)) ? '' : 'none';
                });
            });

            searchInput.addEventListener('click', (e) => e.stopPropagation());
        }

        // Update placeholder on init
        updatePlaceholder();
    }

    /**
     * Bind event handlers for inline checkbox All/None buttons (checkbox filter type)
     */
    bindInlineCheckboxActions(container) {
        const selectAllBtn = container.querySelector('.default-cb-select-all');
        const selectNoneBtn = container.querySelector('.default-cb-select-none');
        const checkboxes = container.querySelectorAll('.default-multi-checkbox');

        // Bind checkbox changes
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.syncDefaultToPreview();
            });
        });

        // Select All button
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                checkboxes.forEach(cb => cb.checked = true);
                this.syncDefaultToPreview();
            });
        }

        // Select None button
        if (selectNoneBtn) {
            selectNoneBtn.addEventListener('click', (e) => {
                e.preventDefault();
                checkboxes.forEach(cb => cb.checked = false);
                this.syncDefaultToPreview();
            });
        }
    }

    /**
     * Sync default value selections to filter preview
     */
    syncDefaultToPreview() {
        const filterType = document.getElementById('filter-type')?.value;
        const isMultiple = document.getElementById('filter-multiple')?.checked;
        const previewSection = document.getElementById('filter-preview-section');
        const defaultSection = document.getElementById('default-value-section');

        if (!previewSection || !defaultSection || !filterType) return;

        // Check if this filter type supports options
        const optionTypes = ['select', 'checkbox', 'radio', 'tokeninput', 'multi_select'];
        if (!optionTypes.includes(filterType)) return;

        // Determine if multiselect
        const isMultiSelect = isMultiple || filterType === 'multi_select' || filterType === 'checkbox' || filterType === 'tokeninput';

        if (isMultiSelect) {
            // Check mode selection
            const multiMode = document.querySelector('input[name="default-multi-mode"]:checked')?.value || 'values';
            const isAllMode = multiMode === 'all';
            const isNoneMode = multiMode === 'none';

            // Get selected values from default section (only when in 'values' mode)
            const defaultCheckboxes = defaultSection.querySelectorAll('.default-multi-checkbox');
            const selectedValues = isNoneMode ? [] : Array.from(defaultCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            // Apply to preview multiselect dropdown (for multi_select filter type)
            const previewDropdown = previewSection.querySelector('.filter-multiselect-dropdown');
            if (previewDropdown) {
                const previewCheckboxes = previewDropdown.querySelectorAll('.filter-multiselect-option input[type="checkbox"]');

                if (isAllMode) {
                    // Select all checkboxes in preview
                    previewCheckboxes.forEach(cb => {
                        cb.checked = true;
                    });
                } else {
                    previewCheckboxes.forEach(cb => {
                        cb.checked = selectedValues.includes(cb.value);
                    });
                }

                // Update preview placeholder
                const placeholder = previewDropdown.querySelector('.filter-multiselect-placeholder');
                if (placeholder) {
                    const checkedCount = Array.from(previewCheckboxes).filter(cb => cb.checked).length;
                    if (checkedCount === 0) {
                        placeholder.textContent = '-- Select multiple --';
                        placeholder.classList.remove('has-selection');
                    } else if (isAllMode) {
                        placeholder.textContent = 'All selected';
                        placeholder.classList.add('has-selection');
                    } else {
                        placeholder.textContent = `${checkedCount} selected`;
                        placeholder.classList.add('has-selection');
                    }
                }
            }

            // Apply to preview checkbox group (for checkbox filter type)
            // Preview uses .filter-preview-checkboxes, dashboard uses .filter-checkbox-group
            const previewCheckboxGroup = previewSection.querySelector('.filter-preview-checkboxes, .filter-checkbox-group');
            if (previewCheckboxGroup) {
                const previewCheckboxes = previewCheckboxGroup.querySelectorAll('input[type="checkbox"]');

                if (isAllMode) {
                    // Select all checkboxes in preview
                    previewCheckboxes.forEach(cb => {
                        cb.checked = true;
                    });
                } else {
                    previewCheckboxes.forEach(cb => {
                        cb.checked = selectedValues.includes(cb.value);
                    });
                }
            }
        } else {
            // Single select: check mode first
            const selectMode = document.querySelector('input[name="default-select-mode"]:checked')?.value || 'value';
            const isNoneMode = selectMode === 'none';

            // Get selected value from default section (empty if none mode)
            const defaultDropdown = defaultSection.querySelector('.default-select-dropdown');
            const selectedValue = isNoneMode ? '' : (defaultDropdown?.querySelector('.filter-select-trigger')?.dataset.value || '');

            // Apply to preview single select
            const previewDropdown = previewSection.querySelector('.filter-select-dropdown');
            if (previewDropdown) {
                const trigger = previewDropdown.querySelector('.filter-select-trigger');
                const options = previewDropdown.querySelectorAll('.filter-select-option');

                options.forEach(opt => {
                    opt.classList.remove('selected');
                    const radio = opt.querySelector('input[type="radio"]');

                    if (opt.dataset.value === selectedValue) {
                        opt.classList.add('selected');
                        if (radio) radio.checked = true;
                        if (trigger) {
                            trigger.dataset.value = selectedValue;
                            const triggerText = trigger.querySelector('.filter-select-placeholder');
                            if (triggerText) {
                                const optLabel = opt.querySelector('.form-check-label')?.textContent.trim() || opt.textContent.trim();
                                triggerText.textContent = optLabel || '-- Select --';
                                triggerText.classList.toggle('has-selection', selectedValue !== '');
                            }
                        }
                    } else {
                        if (radio) radio.checked = false;
                    }
                });
            }
        }
    }

    /**
     * Sync preset selection to datepicker preview
     * @param {string} preset - Preset name (e.g., 'Last 7 Days')
     */
    syncPresetToDatepickerPreview(preset) {
        const previewSection = document.getElementById('filter-preview-section');
        if (!previewSection) return;

        // Find the datepicker in preview
        const datepicker = previewSection.querySelector('.dgc-datepicker[data-picker-type="range"], .dgc-datepicker[data-picker-type="main"]');
        if (!datepicker) return;

        // Update the datepicker using jQuery/daterangepicker if available
        if (typeof $ !== 'undefined' && typeof moment !== 'undefined') {
            const $picker = $(datepicker);
            const pickerInstance = $picker.data('daterangepicker');
            if (pickerInstance) {
                const rangesList = pickerInstance.container.find('.ranges li');
                let rangeClicked = false;

                // Find and click the matching range item to properly select it
                rangesList.each(function() {
                    const rangeText = $(this).text().trim();
                    const rangeKey = $(this).attr('data-range-key');
                    if (rangeKey === preset || rangeText === preset) {
                        // Trigger click to properly select the range
                        $(this).trigger('click');
                        rangeClicked = true;
                        return false; // break the loop
                    }
                });

                // If range was clicked, update the input value and data attributes
                if (rangeClicked) {
                    const fromMoment = pickerInstance.startDate;
                    const toMoment = pickerInstance.endDate;

                    // Update display
                    $picker.val(fromMoment.format('DD-MM-YYYY') + ' - ' + toMoment.format('DD-MM-YYYY'));

                    // Update data attributes
                    datepicker.dataset.from = fromMoment.format('YYYY-MM-DD');
                    datepicker.dataset.to = toMoment.format('YYYY-MM-DD');
                }
            }
        }
    }

    /**
     * Resolve preset name to date range
     * @param {string} preset - Preset name
     * @returns {Object} { from: 'YYYY-MM-DD', to: 'YYYY-MM-DD' }
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
     * Get default value as JSON for saving
     * Returns value whether required or not (default value is always optional unless required)
     */
    getDefaultValueJson() {
        const filterType = document.getElementById('filter-type')?.value || 'text';
        const isMultipleSelect = filterType === 'select' && document.getElementById('filter-multiple')?.checked;
        let defaultValue = {};

        switch (filterType) {
            case 'text':
                defaultValue = { value: document.getElementById('default-value-text')?.value || '' };
                break;

            case 'number':
                defaultValue = { value: document.getElementById('default-value-number')?.value || '' };
                break;

            case 'date':
                defaultValue = { value: document.getElementById('default-value-date')?.value || '' };
                break;

            case 'date_range':
            case 'main_datepicker':
                const mode = document.querySelector('input[name="default-mode"]:checked')?.value || 'selected';
                defaultValue = { mode };

                if (mode === 'preset') {
                    defaultValue.preset = document.getElementById('default-preset')?.value || 'Last 7 Days';
                } else if (mode === 'specific') {
                    defaultValue.from = document.getElementById('default-date-from')?.value || '';
                    defaultValue.to = document.getElementById('default-date-to')?.value || '';
                }
                break;

            case 'select':
                // Check if "Allow multiple selection" is checked
                if (isMultipleSelect) {
                    const multiMode = document.querySelector('input[name="default-multi-mode"]:checked')?.value || 'values';
                    if (multiMode === 'none') {
                        defaultValue = { mode: 'none' };
                    } else if (multiMode === 'block') {
                        defaultValue = { mode: 'block' };
                    } else if (multiMode === 'all') {
                        defaultValue = { mode: 'all' };
                    } else {
                        const checkboxes = document.querySelectorAll('.default-multi-checkbox:checked');
                        defaultValue = { mode: 'values', values: Array.from(checkboxes).map(cb => cb.value) };
                    }
                } else {
                    const selectMode = document.querySelector('input[name="default-select-mode"]:checked')?.value || 'value';
                    if (selectMode === 'none') {
                        defaultValue = { mode: 'none' };
                    } else if (selectMode === 'block') {
                        defaultValue = { mode: 'block' };
                    } else {
                        defaultValue = { mode: 'value', value: document.getElementById('default-value-select')?.value || '' };
                    }
                }
                break;

            case 'radio':
                const radioMode = document.querySelector('input[name="default-select-mode"]:checked')?.value || 'value';
                if (radioMode === 'none') {
                    defaultValue = { mode: 'none' };
                } else if (radioMode === 'block') {
                    defaultValue = { mode: 'block' };
                } else {
                    defaultValue = { mode: 'value', value: document.getElementById('default-value-select')?.value || '' };
                }
                break;

            case 'multi_select':
            case 'checkbox':
                const cbMode = document.querySelector('input[name="default-multi-mode"]:checked')?.value || 'values';
                if (cbMode === 'none') {
                    defaultValue = { mode: 'none' };
                } else if (cbMode === 'block') {
                    defaultValue = { mode: 'block' };
                } else if (cbMode === 'all') {
                    defaultValue = { mode: 'all' };
                } else {
                    const checkboxes = document.querySelectorAll('.default-multi-checkbox:checked');
                    defaultValue = { mode: 'values', values: Array.from(checkboxes).map(cb => cb.value) };
                }
                break;

            case 'tokeninput':
                const tokensInput = document.getElementById('default-value-tokens')?.value || '';
                const tokens = tokensInput.split(',').map(t => t.trim()).filter(t => t);
                defaultValue = { values: tokens };
                break;

            default:
                defaultValue = { value: '' };
        }

        return JSON.stringify(defaultValue);
    }

    /**
     * Check if default value has meaningful content
     */
    hasValidDefaultValue(defaultValueJson) {
        if (!defaultValueJson) return false;

        try {
            const parsed = JSON.parse(defaultValueJson);

            // None mode (no default) is valid for non-required filters
            if (parsed.mode === 'none') return true;

            // Block mode is always valid
            if (parsed.mode === 'block') return true;

            // All mode (select all) is always valid
            if (parsed.mode === 'all') return true;

            // Check for value-based defaults
            if (parsed.value !== undefined) {
                return parsed.value !== '';
            }

            // Check for values array (mode === 'values' must have at least one value)
            if (parsed.values !== undefined) {
                return Array.isArray(parsed.values) && parsed.values.length > 0;
            }

            // Date range modes (except 'block') are valid if set
            if (parsed.mode === 'select_all') {
                return true;
            }

            if (parsed.mode === 'preset' && parsed.preset) {
                return true;
            }

            if (parsed.mode === 'specific' && (parsed.from || parsed.to)) {
                return true;
            }

            return false;
        } catch (e) {
            return false;
        }
    }

    /**
     * Validate default value and return specific error message if invalid
     * @returns {string|null} Error message or null if valid
     */
    validateDefaultValue(defaultValueJson) {
        if (!defaultValueJson) return null;

        try {
            const parsed = JSON.parse(defaultValueJson);

            // Check if "Specific Values" mode is selected but no values are chosen
            if (parsed.mode === 'values') {
                if (!parsed.values || !Array.isArray(parsed.values) || parsed.values.length === 0) {
                    return 'Please select at least one value when using "Specific Values" mode';
                }
            }

            // Check if "Default Value" mode for single select but no value is chosen
            if (parsed.mode === 'value') {
                if (!parsed.value || parsed.value === '') {
                    return 'Please select a default value';
                }
            }

            return null;
        } catch (e) {
            return null;
        }
    }
}
