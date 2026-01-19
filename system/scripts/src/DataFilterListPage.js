/**
 * DataFilterListPage - Data Filter list page controller
 * Handles filter listing and deletion
 */

const Ajax = window.Ajax;
const Loading = window.Loading;
const Toast = window.Toast;

export default class DataFilterListPage {
    constructor(container) {
        this.container = container;
        this.deleteModalElement = null;
        this.filterIdToDelete = null;

        if (this.container) {
            this.init();
        }
    }

    /**
     * Initialize the filter list page
     */
    init() {
        this.deleteModalElement = document.getElementById('delete-modal');
        this.bindEvents();
    }

    /**
     * Get or create the modal instance
     */
    getModal() {
        if (!this.deleteModalElement) return null;
        return bootstrap.Modal.getOrCreateInstance(this.deleteModalElement);
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Check if already bound to avoid duplicate listeners
        if (this.container.dataset.bound) return;
        this.container.dataset.bound = 'true';

        // Use event delegation for delete buttons
        this.container.addEventListener('click', (e) => {
            const deleteBtn = e.target.closest('.delete-filter-btn');
            if (deleteBtn) {
                this.filterIdToDelete = deleteBtn.dataset.id;
                const filterName = this.deleteModalElement.querySelector('.filter-name');
                if (filterName) {
                    filterName.textContent = deleteBtn.dataset.label;
                }
                const modal = this.getModal();
                if (modal) {
                    modal.show();
                }
            }
        });

        // Confirm delete button - check if already bound
        const confirmDeleteBtn = document.querySelector('.confirm-delete-btn');
        if (confirmDeleteBtn && !confirmDeleteBtn.dataset.bound) {
            confirmDeleteBtn.dataset.bound = 'true';
            confirmDeleteBtn.addEventListener('click', () => this.deleteFilter());
        }
    }

    /**
     * Delete the selected filter
     */
    deleteFilter() {
        if (!this.filterIdToDelete) return;

        Loading.show('Deleting filter...');

        Ajax.post('delete_data_filter', { id: this.filterIdToDelete })
            .then(result => {
                Loading.hide();
                const modal = this.getModal();
                if (modal) {
                    modal.hide();
                }
                if (result.success) {
                    Toast.success('Filter deleted');
                    location.reload();
                } else {
                    Toast.error(result.message || 'Failed to delete filter');
                }
            })
            .catch(() => {
                Loading.hide();
                const modal = this.getModal();
                if (modal) {
                    modal.hide();
                }
                Toast.error('Failed to delete filter');
            });
    }
}
