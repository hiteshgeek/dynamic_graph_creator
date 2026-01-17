/**
 * QueryBuilder - SQL query input component
 * Handles query input and testing
 */

export default class QueryBuilder {
    constructor(container, options = {}) {
        this.container = container;
        this.onTest = options.onTest || (() => {});
        this.onError = options.onError || (() => {});
        this.onChange = options.onChange || (() => {});
        this.getFilterValues = options.getFilterValues || (() => ({}));

        this.textarea = null;
        this.testBtn = null;
        this.resultContainer = null;
        this.graphDataSection = null;
        this.graphDataContent = null;
        this.editor = null; // CodeMirror instance

        this.init();
    }

    /**
     * Initialize query builder
     */
    init() {
        this.textarea = this.container.querySelector('.query-editor');
        this.testBtn = this.container.querySelector('.test-query-btn');
        this.formatBtn = this.container.querySelector('.format-query-btn');
        this.copyBtn = this.container.querySelector('.copy-query-btn');
        this.resultContainer = this.container.querySelector('.query-test-result');

        // Find the graph data section (sibling section for displaying query results)
        const graphMain = this.container.closest('.graph-main');
        if (graphMain) {
            this.graphDataSection = graphMain.querySelector('.graph-data-section');
            this.graphDataContent = graphMain.querySelector('.graph-data-content');
        }

        if (this.testBtn) {
            this.testBtn.addEventListener('click', () => this.testQuery());
        }

        if (this.formatBtn) {
            this.formatBtn.addEventListener('click', () => this.formatQuery());
        }

        if (this.copyBtn) {
            this.copyBtn.addEventListener('click', () => this.copyQuery());
        }

        // Initialize CodeMirror if available
        this.initCodeMirror();

        // Add hint about placeholders
        this.addPlaceholderHint();
    }

    /**
     * Initialize CodeMirror editor
     */
    initCodeMirror() {
        if (!this.textarea || typeof CodeMirror === 'undefined') return;

        this.editor = CodeMirror.fromTextArea(this.textarea, {
            mode: 'text/x-sql',
            theme: 'default',
            lineNumbers: true,
            lineWrapping: true,
            indentWithTabs: true,
            smartIndent: true,
            matchBrackets: true,
            autofocus: false,
            extraKeys: {
                'Ctrl-Enter': () => this.testQuery(),
                'Cmd-Enter': () => this.testQuery()
            }
        });

        // Sync CodeMirror with textarea and trigger onChange
        this.editor.on('change', () => {
            this.editor.save();
            this.onChange();
        });

        // Set height
        this.editor.setSize(null, 200);
    }

    /**
     * Add hint about filter placeholders
     */
    addPlaceholderHint() {
        const hint = this.container.querySelector('.query-hint');
        if (!hint) return;

        hint.innerHTML = `
            Use <code>::placeholder_name</code> for filter values.
            Example: <code>WHERE date >= ::date_from AND status IN (::status_ids)</code>
        `;
    }

    /**
     * Copy query to clipboard
     */
    copyQuery() {
        const query = this.getQuery();

        if (!query.trim()) {
            this.showCopyFeedback('Nothing to copy', false);
            return;
        }

        navigator.clipboard.writeText(query).then(() => {
            this.showCopyFeedback('Copied!', true);
        }).catch(() => {
            this.showCopyFeedback('Failed', false);
        });
    }

    /**
     * Show animated copy feedback near button
     */
    showCopyFeedback(message, success) {
        if (!this.copyBtn) return;

        // Remove existing feedback
        const existing = this.copyBtn.querySelector('.copy-feedback');
        if (existing) existing.remove();

        // Create feedback element
        const feedback = document.createElement('span');
        feedback.className = `copy-feedback ${success ? 'success' : 'error'}`;
        feedback.textContent = message;
        this.copyBtn.style.position = 'relative';
        this.copyBtn.appendChild(feedback);

        // Animate and remove
        setTimeout(() => feedback.classList.add('show'), 10);
        setTimeout(() => {
            feedback.classList.remove('show');
            setTimeout(() => feedback.remove(), 200);
        }, 1500);
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
        if (this.editor) {
            this.editor.refresh();
        }
    }

    /**
     * Format SQL query - phpMyAdmin style formatting
     * Keywords on their own line, content indented below
     */
    formatQuery() {
        let query = this.getQuery();
        if (!query.trim()) return;

        // Normalize whitespace first
        query = query.replace(/\s+/g, ' ').trim();

        // Major clauses - keyword on its own line, content indented below
        const majorClauses = [
            'SELECT', 'FROM', 'WHERE', 'ORDER BY', 'GROUP BY', 'HAVING',
            'LIMIT', 'SET', 'VALUES', 'JOIN', 'LEFT JOIN', 'RIGHT JOIN',
            'INNER JOIN', 'OUTER JOIN', 'CROSS JOIN', 'INSERT INTO',
            'UPDATE', 'DELETE FROM', 'UNION ALL', 'UNION'
        ];

        // Sort by length descending to match longer keywords first
        majorClauses.sort((a, b) => b.length - a.length);

        // Add line break before each major clause keyword
        majorClauses.forEach(clause => {
            const regex = new RegExp('\\s+(' + clause.replace(/ /g, '\\s+') + ')\\b', 'gi');
            query = query.replace(regex, '\n$1');
        });

        // Now separate keyword from its content (keyword on own line, content indented)
        majorClauses.forEach(clause => {
            const regex = new RegExp('^(' + clause.replace(/ /g, '\\s+') + ')\\s+(.+)$', 'gim');
            query = query.replace(regex, '$1\n\t$2');
        });

        // Handle commas - each item on new line with indent
        query = query.replace(/,\s*/g, ',\n\t');

        // Handle AND/OR - each on new line with indent
        query = query.replace(/\s+(AND)\s+/gi, '\n\t$1 ');
        query = query.replace(/\s+(OR)\s+/gi, '\n\t$1 ');

        // Clean up: trim lines and remove empty lines
        const lines = query.split('\n')
            .map(line => line.trim())
            .filter(line => line.length > 0);

        // Rebuild with proper indentation
        const formattedLines = [];
        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];

            // Check if line is a major clause keyword only
            const isKeywordOnly = majorClauses.some(clause => {
                const regex = new RegExp('^' + clause.replace(/ /g, '\\s*') + '$', 'i');
                return regex.test(line);
            });

            if (isKeywordOnly) {
                // Uppercase the keyword
                formattedLines.push(line.toUpperCase());
            } else {
                // Check if line starts with AND/OR
                if (/^(AND|OR)\s/i.test(line)) {
                    line = line.replace(/^(AND|OR)\s/i, (m) => m.toUpperCase());
                    formattedLines.push('\t' + line);
                } else {
                    // Content line - indent it
                    formattedLines.push('\t' + line);
                }
            }
        }

        this.setQuery(formattedLines.join('\n'));
    }

    /**
     * Test query and get columns
     */
    async testQuery() {
        // Auto-format query before testing
        this.formatQuery();

        const query = this.getQuery();

        if (!query.trim()) {
            this.showError('Please enter a SQL query');
            this.onError('Please enter a SQL query');
            return;
        }

        // Show loading state
        if (this.testBtn) {
            this.testBtn.disabled = true;
            this.testBtn.innerHTML = '<span class="spinner"></span> Testing...';
        }

        try {
            // Get filter values from sidebar
            const filterValues = this.getFilterValues();
            const result = await Ajax.post('test_query', { query, filters: filterValues });

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
            if (this.testBtn) {
                this.testBtn.disabled = false;
                this.testBtn.innerHTML = '<i class="fas fa-play"></i> Test Query';
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

        // Show debug query and data table in separate Graph Data section
        if (this.graphDataSection && this.graphDataContent) {
            let dataHtml = '';

            // Add debug query textarea (CodeMirror will replace it) - no wrapper needed
            if (debugQuery) {
                dataHtml += `<textarea class="query-debug-textarea" style="display:none;">${this.escapeHtml(debugQuery)}</textarea>`;
            }

            // Add sample data table if rows exist
            if (rows && rows.length > 0) {
                dataHtml += `
                    <div class="query-sample-data">
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
                    </div>
                `;
            }

            if (dataHtml) {
                // Update section header with subtitle
                const sectionHeader = this.graphDataSection.querySelector('.graph-section-header');
                if (sectionHeader) {
                    sectionHeader.innerHTML = `
                        <h3><i class="fas fa-table"></i> Graph Data</h3>
                        <small class="text-muted">Test query (placeholders replaced)</small>
                    `;
                }

                this.graphDataContent.innerHTML = dataHtml;
                this.graphDataSection.style.display = 'block';

                // Initialize CodeMirror for debug query display
                if (debugQuery && typeof CodeMirror !== 'undefined') {
                    const debugTextarea = this.graphDataContent.querySelector('.query-debug-textarea');
                    if (debugTextarea) {
                        const debugEditor = CodeMirror.fromTextArea(debugTextarea, {
                            mode: 'text/x-sql',
                            theme: 'default',
                            lineNumbers: true,
                            lineWrapping: true,
                            readOnly: true
                        });
                        // Auto-adjust height based on content
                        const lineCount = debugQuery.split('\n').length;
                        const height = Math.min(Math.max(lineCount * 22, 80), 200);
                        debugEditor.setSize(null, height);
                    }
                }
            } else {
                this.graphDataSection.style.display = 'none';
            }
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
