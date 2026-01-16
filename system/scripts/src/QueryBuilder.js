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
     * Format SQL query - put keywords on their own lines
     */
    formatQuery() {
        let query = this.getQuery();
        if (!query.trim()) return;

        // Normalize whitespace first
        query = query.replace(/\s+/g, ' ').trim();

        // Keywords that should be on their own line (keyword only, content on next line)
        const standaloneKeywords = ['SELECT', 'FROM', 'WHERE', 'ORDER BY', 'GROUP BY', 'HAVING', 'LIMIT', 'SET', 'VALUES'];

        // Keywords that stay with their content on same line
        const inlineKeywords = ['AND', 'OR', 'ON', 'OFFSET'];

        // Add line breaks before all major keywords
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
        let inSelectColumns = false;

        lines.forEach((line, index) => {
            const upperLine = line.toUpperCase();

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
                // Check if we're continuing content (like columns after SELECT)
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

        this.setQuery(formatted.join('\n'));
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

        let html = `
            <div class="query-result-header">
                <i class="fas fa-check-circle"></i>
                Query is valid
                <span class="query-row-count">${rowCount} row${rowCount !== 1 ? 's' : ''} returned</span>
            </div>
        `;

        // Add debug query if available
        if (debugQuery) {
            html += `
                <div class="query-debug">
                    <small class="text-muted">Test query (placeholders replaced):</small>
                    <pre class="query-debug-sql">${this.escapeHtml(debugQuery)}</pre>
                </div>
            `;
        }

        // Add sample data table if rows exist
        if (rows && rows.length > 0) {
            html += `
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

        this.resultContainer.className = 'query-test-result success';
        this.resultContainer.innerHTML = html;
        this.resultContainer.style.display = 'block';
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
    }

    /**
     * Hide result container
     */
    hideResult() {
        if (this.resultContainer) {
            this.resultContainer.style.display = 'none';
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
