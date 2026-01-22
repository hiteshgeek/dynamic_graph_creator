/**
 * QueryBuilder - SQL query input component
 * Handles query input and testing
 */

import CodeMirrorEditor from './CodeMirrorEditor.js';

export default class QueryBuilder {
    constructor(container, options = {}) {
        this.container = container;
        this.onTest = options.onTest || (() => {});
        this.onError = options.onError || (() => {});
        this.onChange = options.onChange || (() => {});
        this.getFilterValues = options.getFilterValues || (() => ({}));
        this.getPlaceholderSettings = options.getPlaceholderSettings || (() => ({}));

        this.textarea = null;
        this.resultContainer = null;
        this.graphDataSection = null;
        this.graphDataContent = null;
        this.editor = null; // CodeMirror instance
        this.codeEditor = null; // CodeMirrorEditor instance

        this.init();
    }

    /**
     * Initialize query builder
     */
    init() {
        this.textarea = this.container.querySelector('.query-editor');
        this.resultContainer = this.container.querySelector('.query-test-result');

        // Find elements for tabbed query display and sample data
        const graphMain = this.container.closest('.graph-main');
        if (graphMain) {
            // Test Query tab elements
            this.testQueryTabItem = graphMain.querySelector('#test-query-tab-item');
            this.testQueryTab = graphMain.querySelector('#test-query-tab');
            this.testQueryContent = graphMain.querySelector('.test-query-content');

            // Sample data section (alongside data mapper)
            this.sampleDataCol = graphMain.querySelector('.sample-data-col');
            this.sampleDataContainer = graphMain.querySelector('.sample-data-container');
        }

        // Initialize CodeMirror if available
        this.initCodeMirror();
    }

    /**
     * Initialize CodeMirror editor using shared CodeMirrorEditor component
     */
    initCodeMirror() {
        if (!this.textarea || typeof CodeMirror === 'undefined') return;

        // Use shared CodeMirrorEditor for consistent styling
        this.codeEditor = new CodeMirrorEditor(this.textarea, {
            copyBtn: true,
            formatBtn: true,
            testBtn: true,
            minHeight: 150,
            hint: 'Use <code>::placeholder_name</code> for filter values. Example: <code>WHERE date &gt;= ::date_from AND status IN (::status_ids)</code>',
            onTest: () => this.testQuery(),
            onChange: () => this.onChange()
        });

        // Keep reference to CodeMirror instance
        this.editor = this.codeEditor.editor;

        // Add keyboard shortcuts
        if (this.editor) {
            this.editor.setOption('extraKeys', {
                'Ctrl-Enter': () => this.testQuery(),
                'Cmd-Enter': () => this.testQuery()
            });
        }

        // Render mandatory filters info after the hint
        this.renderMandatoryFiltersInfo();
    }

    /**
     * Render mandatory filters info section after the hint
     */
    renderMandatoryFiltersInfo() {
        // Get mandatory filters from data attribute
        const mandatoryFiltersData = this.container.dataset.mandatoryFilters;
        if (!mandatoryFiltersData) return;

        let mandatoryFilters;
        try {
            mandatoryFilters = JSON.parse(mandatoryFiltersData);
        } catch (e) {
            return;
        }

        if (!mandatoryFilters || mandatoryFilters.length === 0) return;

        // Find the wrapper where hint was appended
        const wrapper = this.codeEditor?.wrapper;
        if (!wrapper) return;

        // Build the mandatory filters HTML (inline style matching query-hint)
        const filtersHtml = mandatoryFilters.map(filter => {
            const key = filter.filter_key.replace(/^:+/, '');
            return `<span class="mandatory-filter-item"><span class="mandatory-filter-label">${filter.filter_label}</span> <code class="mandatory-placeholder copyable" data-copy="::${key}">::${key}</code></span>`;
        }).join('');

        const infoDiv = document.createElement('div');
        infoDiv.className = 'mandatory-filters-info';
        infoDiv.innerHTML = `<span class="mandatory-filters-header"><i class="fas fa-lock"></i>Mandatory:</span> <span class="mandatory-filters-desc">Following filters must be included in query:</span> <span class="mandatory-filters-list">${filtersHtml}</span>`;

        wrapper.appendChild(infoDiv);

        // Add click-to-copy for mandatory placeholders
        infoDiv.querySelectorAll('.mandatory-placeholder.copyable').forEach(el => {
            el.addEventListener('click', () => {
                const text = el.dataset.copy || el.textContent;
                navigator.clipboard.writeText(text).then(() => {
                    const originalText = el.textContent;
                    el.textContent = 'Copied!';
                    el.classList.add('copied');
                    setTimeout(() => {
                        el.textContent = originalText;
                        el.classList.remove('copied');
                    }, 1000);
                });
            });
        });
    }

    /**
     * Get current query
     */
    getQuery() {
        if (this.editor) {
            return this.editor.getValue();
        }
        return this.textarea ? this.textarea.value : '';
    }

    /**
     * Set query value
     */
    setQuery(query) {
        if (this.editor) {
            this.editor.setValue(query || '');
        } else if (this.textarea) {
            this.textarea.value = query;
        }
    }

    /**
     * Refresh CodeMirror (call after container becomes visible)
     */
    refresh() {
        if (this.codeEditor) {
            this.codeEditor.refresh();
        } else if (this.editor) {
            this.editor.refresh();
        }
    }

    /**
     * Test query and get columns
     */
    async testQuery() {
        // Auto-format query before testing
        if (this.codeEditor) {
            this.codeEditor.format();
        }

        const query = this.getQuery();

        if (!query.trim()) {
            this.showError('Please enter a SQL query');
            this.onError('Please enter a SQL query');
            return;
        }

        // Show loading state on the test button in CodeMirrorEditor toolbar
        const testBtn = this.codeEditor?.wrapper?.querySelector('.code-editor-toolbar .btn-primary');
        if (testBtn) {
            testBtn.disabled = true;
            testBtn.innerHTML = '<span class="spinner"></span> Testing...';
        }

        try {
            // Get filter values and placeholder settings from sidebar
            const filterValues = this.getFilterValues();
            const placeholderSettings = this.getPlaceholderSettings();
            const result = await Ajax.post('test_query', {
                query,
                filters: filterValues,
                placeholder_settings: placeholderSettings
            });

            if (result.success) {
                const columns = result.data.columns || [];
                const rows = result.data.rows || [];
                const rowCount = result.data.row_count || 0;
                const debugQuery = result.data.debug_query || '';
                this.showSuccess(columns, rows, rowCount, debugQuery);
                this.onTest(columns);
            } else {
                this.showError(result.message);
                this.onError(result.message);
            }
        } catch (error) {
            const message = 'Failed to test query';
            this.showError(message);
            this.onError(message);
        } finally {
            // Reset button
            if (testBtn) {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i class="fas fa-play"></i> Test Query';
            }
        }
    }

    /**
     * Show success result with columns and sample data
     */
    showSuccess(columns, rows = [], rowCount = 0, debugQuery = '') {
        if (!this.resultContainer) return;

        // Simple success message in query section
        const statusHtml = `
            <div class="query-result-header">
                <i class="fas fa-check-circle"></i>
                Query is valid
                <span class="query-row-count">${rowCount} row${rowCount !== 1 ? 's' : ''} returned</span>
            </div>
        `;

        this.resultContainer.className = 'query-test-result success';
        this.resultContainer.innerHTML = statusHtml;
        this.resultContainer.style.display = 'block';

        // Show test query in Test Query tab
        if (this.testQueryTabItem && this.testQueryContent && debugQuery) {
            // Show the Test Query tab
            this.testQueryTabItem.style.display = '';

            // Clear previous content and create textarea for CodeMirrorEditor
            this.testQueryContent.innerHTML = `<textarea class="query-debug-textarea">${this.escapeHtml(debugQuery)}</textarea>`;

            // Destroy previous debug editor if exists
            if (this.debugCodeEditor) {
                this.debugCodeEditor.destroy();
            }

            // Use shared CodeMirrorEditor for consistent styling (same as SQL Query)
            const debugTextarea = this.testQueryContent.querySelector('.query-debug-textarea');
            if (debugTextarea) {
                this.debugCodeEditor = new CodeMirrorEditor(debugTextarea, {
                    copyBtn: true,
                    formatBtn: false,
                    testBtn: false,
                    readOnly: true,
                    minHeight: 100
                });

                // Keep reference to CodeMirror instance
                this.debugEditor = this.debugCodeEditor.editor;

                // Refresh CodeMirror when tab becomes visible
                if (this.testQueryTab) {
                    this.testQueryTab.addEventListener('shown.bs.tab', () => {
                        if (this.debugCodeEditor) {
                            this.debugCodeEditor.refresh();
                        }
                    });
                }
            }
        }

        // Show data table alongside Data Mapping section
        if (this.sampleDataCol && this.sampleDataContainer && rows && rows.length > 0) {
            const tableHtml = `
                <div class="graph-section-header">
                    <h3><i class="fas fa-table"></i> Query Results</h3>
                    <small class="text-muted">${rowCount} row${rowCount !== 1 ? 's' : ''} returned</small>
                </div>
                <div class="query-result-table-wrapper">
                    <table class="query-result-table">
                        <thead>
                            <tr>
                                ${columns.map(col => `<th>${this.escapeHtml(col)}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${rows.map(row => `
                                <tr>
                                    ${columns.map(col => `<td>${this.escapeHtml(row[col])}</td>`).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            this.sampleDataContainer.innerHTML = tableHtml;
            this.sampleDataCol.style.display = '';
        } else if (this.sampleDataCol) {
            this.sampleDataCol.style.display = 'none';
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(value) {
        if (value === null || value === undefined) return '';
        const str = String(value);
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Show error result
     */
    showError(message) {
        if (!this.resultContainer) return;

        this.resultContainer.className = 'query-test-result error';
        this.resultContainer.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            ${message}
        `;
        this.resultContainer.style.display = 'block';

        // Hide graph data section on error
        if (this.graphDataSection) {
            this.graphDataSection.style.display = 'none';
        }
    }

    /**
     * Hide result container
     */
    hideResult() {
        if (this.resultContainer) {
            this.resultContainer.style.display = 'none';
        }
        if (this.graphDataSection) {
            this.graphDataSection.style.display = 'none';
        }
    }

    /**
     * Extract placeholders from query (::placeholder_name syntax)
     */
    getPlaceholders() {
        const query = this.getQuery();
        const regex = /::([a-zA-Z_][a-zA-Z0-9_]*)/g;
        const placeholders = [];
        let match;

        while ((match = regex.exec(query)) !== null) {
            if (!placeholders.includes(match[0])) {
                placeholders.push(match[0]);
            }
        }

        return placeholders;
    }
}
