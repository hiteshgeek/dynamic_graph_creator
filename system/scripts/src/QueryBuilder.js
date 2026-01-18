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

            // Build test query content with CodeMirror
            const queryHtml = `
                <div class="debug-query-wrapper">
                    <button type="button" class="btn btn-sm btn-outline-secondary copy-debug-query-btn" title="Copy SQL">
                        <i class="fas fa-copy"></i>
                    </button>
                    <textarea class="query-debug-textarea" style="display:none;">${this.escapeHtml(debugQuery)}</textarea>
                </div>
            `;

            this.testQueryContent.innerHTML = queryHtml;

            // Initialize CodeMirror for debug query display
            if (typeof CodeMirror !== 'undefined') {
                const debugTextarea = this.testQueryContent.querySelector('.query-debug-textarea');
                if (debugTextarea) {
                    this.debugEditor = CodeMirror.fromTextArea(debugTextarea, {
                        mode: 'text/x-sql',
                        theme: 'default',
                        lineNumbers: true,
                        lineWrapping: true,
                        readOnly: true
                    });
                    this.debugEditor.setSize(null, 'auto');

                    // Refresh CodeMirror when tab becomes visible
                    if (this.testQueryTab) {
                        this.testQueryTab.addEventListener('shown.bs.tab', () => {
                            if (this.debugEditor) {
                                this.debugEditor.refresh();
                            }
                        });
                    }
                }

                // Bind copy button for debug query
                const copyDebugBtn = this.testQueryContent.querySelector('.copy-debug-query-btn');
                if (copyDebugBtn) {
                    copyDebugBtn.addEventListener('click', () => {
                        navigator.clipboard.writeText(debugQuery).then(() => {
                            this.showDebugCopyFeedback(copyDebugBtn, 'Copied!', true);
                        }).catch(() => {
                            this.showDebugCopyFeedback(copyDebugBtn, 'Copied!', false);
                        });
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
     * Show animated copy feedback for debug query button
     */
    showDebugCopyFeedback(button, message, success) {
        if (!button) return;

        // Remove existing feedback
        const existing = button.querySelector('.copy-feedback');
        if (existing) existing.remove();

        // Create feedback element
        const feedback = document.createElement('span');
        feedback.className = `copy-feedback ${success ? 'success' : 'error'}`;
        feedback.textContent = message;
        button.style.position = 'relative';
        button.appendChild(feedback);

        // Animate and remove
        setTimeout(() => feedback.classList.add('show'), 10);
        setTimeout(() => {
            feedback.classList.remove('show');
            setTimeout(() => feedback.remove(), 200);
        }, 1500);
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
