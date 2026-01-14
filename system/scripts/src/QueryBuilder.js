/**
 * QueryBuilder - SQL query input component
 * Handles query input and testing
 */

export default class QueryBuilder {
    constructor(container, options = {}) {
        this.container = container;
        this.onTest = options.onTest || (() => {});
        this.onError = options.onError || (() => {});

        this.textarea = null;
        this.testBtn = null;
        this.resultContainer = null;

        this.init();
    }

    /**
     * Initialize query builder
     */
    init() {
        this.textarea = this.container.querySelector('.query-editor');
        this.testBtn = this.container.querySelector('.test-query-btn');
        this.resultContainer = this.container.querySelector('.query-test-result');

        if (this.testBtn) {
            this.testBtn.addEventListener('click', () => this.testQuery());
        }

        // Add hint about placeholders
        this.addPlaceholderHint();
    }

    /**
     * Add hint about filter placeholders
     */
    addPlaceholderHint() {
        const hint = this.container.querySelector('.query-hint');
        if (!hint) return;

        hint.innerHTML = `
            Use <code>:placeholder_name</code> for filter values.
            Example: <code>WHERE date >= :date_from AND status IN (:status_ids)</code>
        `;
    }

    /**
     * Get current query
     */
    getQuery() {
        return this.textarea ? this.textarea.value : '';
    }

    /**
     * Set query value
     */
    setQuery(query) {
        if (this.textarea) {
            this.textarea.value = query;
        }
    }

    /**
     * Test query and get columns
     */
    async testQuery() {
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
            const result = await Ajax.post('test_query', { query });

            if (result.success) {
                const columns = result.data.columns || [];
                const rows = result.data.rows || [];
                const rowCount = result.data.row_count || 0;
                this.showSuccess(columns, rows, rowCount);
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
    showSuccess(columns, rows = [], rowCount = 0) {
        if (!this.resultContainer) return;

        let html = `
            <div class="query-result-header">
                <i class="fas fa-check-circle"></i>
                Query is valid
                <span class="query-row-count">${rowCount} row${rowCount !== 1 ? 's' : ''} returned</span>
            </div>
        `;

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
     * Extract placeholders from query
     */
    getPlaceholders() {
        const query = this.getQuery();
        const regex = /:([a-zA-Z_][a-zA-Z0-9_]*)/g;
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
