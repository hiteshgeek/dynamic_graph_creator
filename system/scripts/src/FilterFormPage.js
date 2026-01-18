/**
 * FilterFormPage - Filter add/edit form controller
 * Handles filter form functionality including CodeMirror, options management, and save
 */

import CodeMirrorEditor from './CodeMirrorEditor.js';

const Ajax = window.Ajax;
const Loading = window.Loading;
const Toast = window.Toast;

export default class FilterFormPage {
    constructor(container) {
        this.container = container;
        this.queryEditor = null;
        this.typesWithOptions = ['select', 'checkbox', 'radio', 'tokeninput'];
        this.lastQueryOptions = null; // Store query results for preview updates

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
        this.initPreview();
    }

    /**
     * Initialize preview on page load (for edit mode)
     */
    initPreview() {
        const filterType = document.getElementById('filter-type')?.value;
        const dataSource = document.getElementById('data-source')?.value;

        // Show preview for date types immediately (they don't need options)
        if (filterType === 'date' || filterType === 'date_range') {
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

        // Filter key input - validate on input
        const filterKeyInput = document.getElementById('filter-key');
        if (filterKeyInput) {
            filterKeyInput.addEventListener('input', () => this.validateFilterKey());
        }

        // Save filter button
        const saveBtn = document.querySelector('.save-filter-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveFilter();
            });
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
        const previewSection = document.getElementById('filter-preview-section');

        if (dataSourceSection) {
            dataSourceSection.style.display = this.typesWithOptions.includes(filterType) ? 'block' : 'none';
        }

        if (checkboxRadioConfigSection) {
            checkboxRadioConfigSection.style.display = (filterType === 'checkbox' || filterType === 'radio') ? 'block' : 'none';
        }

        if (selectConfigSection) {
            selectConfigSection.style.display = filterType === 'select' ? 'block' : 'none';
        }

        // Show preview for date types immediately (they don't need options)
        if (filterType === 'date' || filterType === 'date_range') {
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
        if (source === 'query' && this.codeEditor) {
            this.codeEditor.refresh();
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
     */
    testQuery() {
        // Format query before testing
        if (this.codeEditor) {
            this.codeEditor.format();
        }

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
                    let html = `
                        <div class="query-result-header">
                            <i class="fas fa-check-circle"></i>
                            Query is valid
                            <span class="query-row-count">${options.length} row${options.length !== 1 ? 's' : ''} returned</span>
                        </div>
                    `;

                    // Show warnings if any
                    if (result.data.warnings && result.data.warnings.length > 0) {
                        html += '<div class="query-warnings"><ul>';
                        result.data.warnings.forEach(w => {
                            html += `<li>${w}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    // Store options for later preview updates
                    this.lastQueryOptions = options;

                    if (options.length > 0) {
                        const hasIsSelected = result.data.hasIsSelected || false;
                        const colCount = hasIsSelected ? 3 : 2;
                        html += `
                            <div class="query-sample-data">
                                <div class="query-result-table-wrapper">
                                    <table class="query-result-table">
                                        <thead>
                                            <tr><th>Value</th><th>Label</th>${hasIsSelected ? '<th>Selected</th>' : ''}</tr>
                                        </thead>
                                        <tbody>
                        `;
                        options.slice(0, 10).forEach(opt => {
                            const selectedIcon = opt.is_selected ? '<i class="fas fa-check text-success"></i>' : '-';
                            html += `<tr><td>${this.escapeHtml(opt.value) || '-'}</td><td>${this.escapeHtml(opt.label) || '-'}</td>${hasIsSelected ? `<td>${selectedIcon}</td>` : ''}</tr>`;
                        });
                        if (options.length > 10) {
                            html += `<tr><td colspan="${colCount}" class="text-muted">... and ${options.length - 10} more</td></tr>`;
                        }
                        html += '</tbody></table></div></div>';

                        // Show filter preview in dedicated container
                        this.showFilterPreview(options);
                    }

                    resultDiv.className = 'query-test-result success';
                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.className = 'query-test-result error';
                    resultDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${result.message || 'Query failed'}`;
                }

                resultDiv.style.display = 'block';
            })
            .catch(() => {
                Loading.hide();
                Toast.error('Failed to test query');
            });
    }

    /**
     * Validate filter key format (real-time validation)
     * @returns {boolean} True if valid
     */
    validateFilterKey() {
        const filterKeyInput = document.getElementById('filter-key');
        if (!filterKeyInput) return true;

        const value = filterKeyInput.value.trim();

        // Empty is handled by required validation
        if (!value) {
            filterKeyInput.classList.remove('is-invalid');
            return true;
        }

        // Only alphanumeric and underscores allowed
        if (!/^[a-zA-Z0-9_]+$/.test(value)) {
            filterKeyInput.classList.add('is-invalid');
            return false;
        }

        filterKeyInput.classList.remove('is-invalid');
        return true;
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

        // Validate filter key format (only alphanumeric and underscores allowed)
        if (!/^[a-zA-Z0-9_]+$/.test(filterKey)) {
            Toast.error('Filter key can only contain letters, numbers, and underscores');
            document.getElementById('filter-key').classList.add('is-invalid');
            document.getElementById('filter-key').focus();
            return;
        }

        // Remove invalid class if validation passes
        document.getElementById('filter-key').classList.remove('is-invalid');

        if (!filterLabel) {
            Toast.error('Filter label is required');
            return;
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
            // Date range picker
            previewHtml += `
                <input type="text" class="form-control dgc-datepicker" data-picker-type="range" placeholder="Select date range" autocomplete="off" id="filter-preview-date-range">
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

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
