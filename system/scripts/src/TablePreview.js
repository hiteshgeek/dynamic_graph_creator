/**
 * TablePreview - Table preview rendering
 * Displays table with data and handles pagination
 */

export default class TablePreview {
    constructor(container, options = {}) {
        this.container = container;
        this.data = { columns: [], rows: [], total_rows: 0 };
        this.config = {
            columns: [],
            pagination: {
                enabled: true,
                rowsPerPage: 10,
                rowsPerPageOptions: [10, 25, 50, 100]
            },
            style: {
                striped: true,
                bordered: true,
                hover: true,
                density: 'comfortable'
            }
        };

        this.currentPage = 1;
        this.showSkeleton = options.showSkeleton !== false;

        // Dummy data state
        this.isDummyData = false;
        this.dummyDataLabel = null;
    }

    /**
     * Set table data
     */
    setData(data) {
        this.data = data || { columns: [], rows: [], total_rows: 0 };
        this.currentPage = 1;
        this.hideDummyDataLabel();
    }

    /**
     * Set table config
     */
    setConfig(config) {
        this.config = { ...this.config, ...config };
    }

    /**
     * Show loading state
     */
    showLoading() {
        const loadingEl = this.container.querySelector('.table-loading');
        const contentEl = this.container.querySelector('.table-preview-content, .table-content');
        const emptyEl = this.container.querySelector('.table-preview-empty');

        if (loadingEl) loadingEl.style.display = 'flex';
        if (contentEl) contentEl.style.display = 'none';
        if (emptyEl) emptyEl.style.display = 'none';
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        const loadingEl = this.container.querySelector('.table-loading');
        if (loadingEl) loadingEl.style.display = 'none';
    }

    /**
     * Show empty state
     */
    showEmpty(message = 'No data available') {
        this.hideLoading();
        this.hideDummyDataLabel();
        const emptyEl = this.container.querySelector('.table-preview-empty');
        const contentEl = this.container.querySelector('.table-preview-content, .table-content');

        if (emptyEl) {
            emptyEl.style.display = 'flex';
            const msgEl = emptyEl.querySelector('p');
            if (msgEl) msgEl.textContent = message;
        }
        if (contentEl) contentEl.style.display = 'none';
    }

    /**
     * Show error state
     */
    showError(message) {
        this.hideLoading();
        this.hideDummyDataLabel();
        const contentEl = this.container.querySelector('.table-preview-content, .table-content');
        const emptyEl = this.container.querySelector('.table-preview-empty');

        if (emptyEl) emptyEl.style.display = 'none';

        if (contentEl) {
            contentEl.style.display = 'block';
            contentEl.innerHTML = `
                <div class="table-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${this.escapeHtml(message)}</p>
                </div>
            `;
        }
    }

    /**
     * Get paginated data
     */
    getPaginatedData() {
        const { rows } = this.data;
        const { pagination } = this.config;

        if (!pagination.enabled) {
            return { rows, start: 1, end: rows.length, total: rows.length };
        }

        const rowsPerPage = pagination.rowsPerPage || 10;
        const start = (this.currentPage - 1) * rowsPerPage;
        const end = Math.min(start + rowsPerPage, rows.length);
        const paginatedRows = rows.slice(start, end);

        return {
            rows: paginatedRows,
            start: start + 1,
            end,
            total: rows.length,
            totalPages: Math.ceil(rows.length / rowsPerPage)
        };
    }

    /**
     * Build table HTML
     */
    buildTableHTML(paginatedData) {
        const { columns } = this.data;
        const { rows, start, end, total, totalPages } = paginatedData;
        const { style, pagination } = this.config;

        // Build table classes
        const tableClasses = ['dgc-table'];
        if (style.striped) tableClasses.push('table-striped');
        if (style.bordered) tableClasses.push('table-bordered');
        if (style.hover) tableClasses.push('table-hover');
        if (style.density) tableClasses.push(`table-${style.density}`);

        let html = '<div class="table-responsive">';
        html += `<table class="${tableClasses.join(' ')}">`;

        // Header
        html += '<thead><tr>';
        columns.forEach(col => {
            html += `<th>${this.escapeHtml(col)}</th>`;
        });
        html += '</tr></thead>';

        // Body
        html += '<tbody>';
        if (rows.length === 0) {
            html += `<tr><td colspan="${columns.length}" class="text-center text-muted">No data</td></tr>`;
        } else {
            rows.forEach(row => {
                html += '<tr>';
                columns.forEach(col => {
                    const value = row[col] !== null && row[col] !== undefined ? row[col] : '';
                    html += `<td>${this.escapeHtml(String(value))}</td>`;
                });
                html += '</tr>';
            });
        }
        html += '</tbody>';

        html += '</table>';
        html += '</div>';

        // Pagination
        if (pagination.enabled && total > 0) {
            html += this.buildPaginationHTML(start, end, total, totalPages);
        }

        return html;
    }

    /**
     * Build pagination HTML
     */
    buildPaginationHTML(start, end, total, totalPages) {
        let html = '<div class="table-pagination">';

        html += `<div class="pagination-info">Showing ${start} to ${end} of ${total} entries</div>`;

        html += '<div class="pagination-controls">';

        // Previous button
        html += `<button type="button" class="btn btn-sm btn-outline-secondary pagination-prev" ${this.currentPage === 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i>
        </button>`;

        // Page indicator
        html += `<span class="pagination-page">Page ${this.currentPage} of ${totalPages}</span>`;

        // Next button
        html += `<button type="button" class="btn btn-sm btn-outline-secondary pagination-next" ${this.currentPage >= totalPages ? 'disabled' : ''}>
            <i class="fas fa-chevron-right"></i>
        </button>`;

        html += '</div>';
        html += '</div>';

        return html;
    }

    /**
     * Bind pagination events
     */
    bindPaginationEvents() {
        const prevBtn = this.container.querySelector('.pagination-prev');
        const nextBtn = this.container.querySelector('.pagination-next');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.render();
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                const { pagination } = this.config;
                const totalPages = Math.ceil(this.data.rows.length / (pagination.rowsPerPage || 10));
                if (this.currentPage < totalPages) {
                    this.currentPage++;
                    this.render();
                }
            });
        }
    }

    /**
     * Escape HTML special characters
     */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Show table with dummy data
     */
    showDummyData() {
        this.data = this.getDummyData();
        this.isDummyData = true;
        this.render();
        this.showDummyDataLabel();
    }

    /**
     * Get dummy data for table preview
     */
    getDummyData() {
        return {
            columns: ['ID', 'Name', 'Email', 'Status', 'Created'],
            rows: [
                { ID: 1, Name: 'John Smith', Email: 'john@example.com', Status: 'Active', Created: '2024-01-15' },
                { ID: 2, Name: 'Jane Doe', Email: 'jane@example.com', Status: 'Active', Created: '2024-01-16' },
                { ID: 3, Name: 'Bob Wilson', Email: 'bob@example.com', Status: 'Pending', Created: '2024-01-17' },
                { ID: 4, Name: 'Alice Brown', Email: 'alice@example.com', Status: 'Active', Created: '2024-01-18' },
                { ID: 5, Name: 'Charlie Davis', Email: 'charlie@example.com', Status: 'Inactive', Created: '2024-01-19' }
            ],
            total_rows: 5
        };
    }

    /**
     * Show dummy data label
     */
    showDummyDataLabel() {
        this.hideDummyDataLabel();

        this.dummyDataLabel = document.createElement('div');
        this.dummyDataLabel.className = 'dummy-data-label';
        this.dummyDataLabel.innerHTML = '<i class="fas fa-info-circle"></i> Sample Data';
        this.container.appendChild(this.dummyDataLabel);
    }

    /**
     * Hide dummy data label
     */
    hideDummyDataLabel() {
        if (this.dummyDataLabel) {
            this.dummyDataLabel.remove();
            this.dummyDataLabel = null;
        }
        this.isDummyData = false;
    }

    /**
     * Render table
     */
    render() {
        this.hideLoading();

        const { rows, columns } = this.data;
        const emptyEl = this.container.querySelector('.table-preview-empty');
        const contentEl = this.container.querySelector('.table-preview-content, .table-content');

        if (!rows || rows.length === 0 || !columns || columns.length === 0) {
            this.showEmpty();
            return;
        }

        if (emptyEl) emptyEl.style.display = 'none';

        if (contentEl) {
            contentEl.style.display = 'block';
            const paginatedData = this.getPaginatedData();
            contentEl.innerHTML = this.buildTableHTML(paginatedData);
            this.bindPaginationEvents();
        }
    }
}

// Export for global access
window.TablePreview = TablePreview;
