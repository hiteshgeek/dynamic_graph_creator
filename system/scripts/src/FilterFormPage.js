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

        // Copy query button
        const copyQueryBtn = document.getElementById('copy-query-btn');
        if (copyQueryBtn) {
            copyQueryBtn.addEventListener('click', () => this.copyQuery());
        }

        // Format query button
        const formatQueryBtn = document.getElementById('format-query-btn');
        if (formatQueryBtn) {
            formatQueryBtn.addEventListener('click', () => this.formatQuery());
        }

        // Test query button
        const testQueryBtn = document.getElementById('test-query-btn');
        if (testQueryBtn) {
            testQueryBtn.addEventListener('click', () => this.testQuery());
        }

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
    }

    /**
     * Handle filter type change
     */
    onFilterTypeChange() {
        const filterType = document.getElementById('filter-type').value;
        const dataSourceSection = document.getElementById('data-source-section');
        const selectConfigSection = document.getElementById('select-config-section');
        const checkboxRadioConfigSection = document.getElementById('checkbox-radio-config-section');

        if (dataSourceSection) {
            dataSourceSection.style.display = this.typesWithOptions.includes(filterType) ? 'block' : 'none';
        }

        if (checkboxRadioConfigSection) {
            checkboxRadioConfigSection.style.display = (filterType === 'checkbox' || filterType === 'radio') ? 'block' : 'none';
        }

        if (selectConfigSection) {
            selectConfigSection.style.display = filterType === 'select' ? 'block' : 'none';
        }

        // Update preview when filter type changes
        this.updatePreview();
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

        // Update filter preview based on data source
        this.updatePreviewOnSourceChange(source);
    }

    /**
     * Update filter preview when switching data source
     */
    updatePreviewOnSourceChange(source) {
        const previewContainer = document.getElementById('filter-preview-container');
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
            } else if (previewContainer) {
                previewContainer.style.display = 'none';
            }
        } else {
            // Switching to query - hide preview until query is tested
            if (previewContainer) {
                previewContainer.style.display = 'none';
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
            <button type="button" class="btn btn-sm btn-outline remove-option-btn">
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
            const container = document.getElementById('filter-preview-container');
            if (container) {
                container.style.display = 'none';
            }
        }
    }

    /**
     * Copy SQL query to clipboard
     */
    copyQuery() {
        const query = this.queryEditor ? this.queryEditor.getValue() : document.getElementById('data-query').value;
        const btn = document.getElementById('copy-query-btn');

        if (!query.trim()) {
            this.showCopyFeedback(btn, 'Nothing to copy', false);
            return;
        }

        navigator.clipboard.writeText(query).then(() => {
            this.showCopyFeedback(btn, 'Copied!', true);
        }).catch(() => {
            this.showCopyFeedback(btn, 'Failed', false);
        });
    }

    /**
     * Show animated copy feedback near button
     */
    showCopyFeedback(btn, message, success) {
        if (!btn) return;

        // Remove existing feedback
        const existing = btn.querySelector('.copy-feedback');
        if (existing) existing.remove();

        // Create feedback element
        const feedback = document.createElement('span');
        feedback.className = `copy-feedback ${success ? 'success' : 'error'}`;
        feedback.textContent = message;
        btn.style.position = 'relative';
        btn.appendChild(feedback);

        // Animate and remove
        setTimeout(() => feedback.classList.add('show'), 10);
        setTimeout(() => {
            feedback.classList.remove('show');
            setTimeout(() => feedback.remove(), 200);
        }, 1500);
    }

    /**
     * Format SQL query - put keywords on their own lines
     */
    formatQuery() {
        let query = this.queryEditor ? this.queryEditor.getValue() : document.getElementById('data-query').value;
        if (!query.trim()) return;

        // Normalize whitespace first
        query = query.replace(/\s+/g, ' ').trim();

        // Keywords that should be on their own line (keyword only, content on next line)
        const standaloneKeywords = ['SELECT', 'FROM', 'WHERE', 'ORDER BY', 'GROUP BY', 'HAVING', 'LIMIT', 'SET', 'VALUES'];

        // All keywords to add line breaks before
        const allKeywords = [
            'SELECT', 'FROM', 'WHERE', 'AND', 'OR', 'ORDER BY', 'GROUP BY',
            'HAVING', 'LIMIT', 'OFFSET', 'JOIN', 'LEFT JOIN', 'RIGHT JOIN',
            'INNER JOIN', 'OUTER JOIN', 'CROSS JOIN', 'ON', 'SET', 'VALUES',
            'INSERT INTO', 'UPDATE', 'DELETE FROM', 'UNION', 'UNION ALL'
        ];

        allKeywords.forEach(keyword => {
            const regex = new RegExp('\\s+(' + keyword.replace(/ /g, '\\s+') + ')\\b', 'gi');
            query = query.replace(regex, '\n$1');
        });

        // Handle SELECT at the start
        if (/^SELECT\s+/i.test(query)) {
            query = query.replace(/^SELECT\s+/i, 'SELECT\n    ');
        }

        // Put content after standalone keywords on new line with indent
        standaloneKeywords.forEach(keyword => {
            const regex = new RegExp('\\n(' + keyword.replace(/ /g, '\\s+') + ')\\s+', 'gi');
            query = query.replace(regex, '\n$1\n    ');
        });

        // Handle commas - put each item on new line
        query = query.replace(/,\s*/g, ',\n    ');

        // Indent AND/OR
        query = query.replace(/\n(AND|OR)\s+/gi, '\n    $1 ');

        // Clean up multiple newlines
        query = query.replace(/\n\s*\n/g, '\n');

        // Trim each line and rebuild
        query = query.split('\n').map(line => line.trim()).filter(line => line).join('\n');

        // Re-add proper indentation
        const lines = query.split('\n');
        const formatted = [];

        lines.forEach((line, index) => {
            // Check if this is a standalone keyword line
            const isKeywordLine = standaloneKeywords.some(kw =>
                new RegExp('^' + kw.replace(/ /g, '\\s*') + '$', 'i').test(line)
            );

            // Check if previous line was a standalone keyword
            const prevLine = index > 0 ? lines[index - 1].toUpperCase().trim() : '';
            const prevIsKeyword = standaloneKeywords.some(kw =>
                new RegExp('^' + kw.replace(/ /g, '\\s*') + '$', 'i').test(prevLine)
            );

            // Check for AND/OR lines
            const isAndOr = /^(AND|OR)\s/i.test(line);

            if (isKeywordLine) {
                formatted.push(line.toUpperCase());
            } else if (prevIsKeyword || isAndOr) {
                formatted.push('    ' + line);
            } else if (!isKeywordLine && index > 0) {
                const lastFormatted = formatted[formatted.length - 1];
                if (lastFormatted && lastFormatted.startsWith('    ')) {
                    formatted.push('    ' + line);
                } else {
                    formatted.push(line);
                }
            } else {
                formatted.push(line);
            }
        });

        const result = formatted.join('\n');
        if (this.queryEditor) {
            this.queryEditor.setValue(result);
        } else {
            document.getElementById('data-query').value = result;
        }

        // Show success message
        const resultDiv = document.getElementById('query-result');
        if (resultDiv) {
            resultDiv.className = 'query-test-result success';
            resultDiv.innerHTML = `
                <div class="query-result-header">
                    <i class="fas fa-check-circle"></i>
                    SQL formatted successfully
                </div>
            `;
            resultDiv.style.display = 'block';
        }
        Toast.success('SQL formatted');
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
                        html += `
                            <div class="query-sample-data">
                                <div class="query-result-table-wrapper">
                                    <table class="query-result-table">
                                        <thead>
                                            <tr><th>Value</th><th>Label</th></tr>
                                        </thead>
                                        <tbody>
                        `;
                        options.slice(0, 10).forEach(opt => {
                            html += `<tr><td>${this.escapeHtml(opt.value) || '-'}</td><td>${this.escapeHtml(opt.label) || '-'}</td></tr>`;
                        });
                        if (options.length > 10) {
                            html += `<tr><td colspan="2" class="text-muted">... and ${options.length - 10} more</td></tr>`;
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
     * Show filter preview in dedicated container
     */
    showFilterPreview(options) {
        const container = document.getElementById('filter-preview-container');
        if (!container) return;

        container.innerHTML = this.renderFilterPreview(options);
        container.style.display = 'block';

        // Bind multiselect dropdown toggle
        this.bindMultiselectDropdown(container);
    }

    /**
     * Bind click events for multiselect dropdown toggle
     */
    bindMultiselectDropdown(container) {
        const trigger = container.querySelector('.filter-multiselect-trigger');
        const optionsPanel = container.querySelector('.filter-multiselect-options');

        if (!trigger || !optionsPanel) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            optionsPanel.classList.toggle('open');
            trigger.querySelector('i').classList.toggle('fa-chevron-down');
            trigger.querySelector('i').classList.toggle('fa-chevron-up');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                optionsPanel.classList.remove('open');
                const icon = trigger.querySelector('i');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
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
                <div class="filter-preview-header">
                    <i class="fas fa-filter"></i> Filter Preview
                </div>
                <div class="filter-preview-content">
                    <label class="filter-preview-label">${this.escapeHtml(filterLabel)}</label>
        `;

        // Render different preview based on filter type
        if (filterType === 'select') {
            if (isMultiple) {
                // Multi-select dropdown with checkboxes
                previewHtml += `
                    <div class="filter-multiselect-dropdown">
                        <div class="filter-multiselect-trigger">
                            <span class="filter-multiselect-placeholder">-- Select multiple --</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-multiselect-options">
                            ${options.map(opt => `
                                <label class="filter-multiselect-option">
                                    <input type="checkbox" value="${this.escapeHtml(opt.value)}">
                                    <span>${this.escapeHtml(opt.label)}</span>
                                </label>
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
            options.forEach(opt => {
                previewHtml += `
                    <label class="filter-preview-checkbox">
                        <input type="checkbox" name="preview_checkbox" value="${this.escapeHtml(opt.value)}">
                        <span>${this.escapeHtml(opt.label)}</span>
                    </label>
                `;
            });
            previewHtml += '</div>';
        } else if (filterType === 'radio') {
            // Radio buttons
            const inlineClass = isInline ? ' inline' : '';
            previewHtml += `<div class="filter-preview-radios${inlineClass}">`;
            options.forEach(opt => {
                previewHtml += `
                    <label class="filter-preview-radio">
                        <input type="radio" name="preview_radio" value="${this.escapeHtml(opt.value)}">
                        <span>${this.escapeHtml(opt.label)}</span>
                    </label>
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
