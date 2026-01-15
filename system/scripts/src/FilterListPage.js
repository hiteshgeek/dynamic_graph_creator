/**
 * FilterListPage - Filter list page controller
 * Handles filter listing and deletion
 */

const Ajax = window.Ajax;
const Loading = window.Loading;
const Toast = window.Toast;

export default class FilterListPage {
    constructor(container) {
        this.container = container;
        this.deleteModal = null;
        this.filterIdToDelete = null;

        if (this.container) {
            this.init();
        }
    }

    /**
     * Initialize the filter list page
     */
    init() {
        this.deleteModal = document.getElementById('delete-modal');
        this.bindEvents();
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Delete filter buttons
        this.container.querySelectorAll('.delete-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.filterIdToDelete = e.currentTarget.dataset.id;
                const filterName = this.deleteModal.querySelector('.filter-name');
                if (filterName) {
                    filterName.textContent = e.currentTarget.dataset.label;
                }
                this.openModal(this.deleteModal);
            });
        });

        // Confirm delete button
        const confirmDeleteBtn = document.querySelector('.confirm-delete-btn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => this.deleteFilter());
        }

        // Modal close buttons
        document.querySelectorAll('.modal-close-btn, .modal-cancel-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.closeModal(e.target.closest('.modal-overlay'));
            });
        });
    }

    /**
     * Delete the selected filter
     */
    deleteFilter() {
        if (!this.filterIdToDelete) return;

        Loading.show('Deleting filter...');

        Ajax.post('delete_filter', { id: this.filterIdToDelete })
            .then(result => {
                Loading.hide();
                if (result.success) {
                    Toast.success('Filter deleted');
                    location.reload();
                } else {
                    Toast.error(result.message || 'Failed to delete filter');
                }
            })
            .catch(() => {
                Loading.hide();
                Toast.error('Failed to delete filter');
            });
    }

    /**
     * Open a modal
     */
    openModal(modal) {
        if (modal) {
            modal.classList.add('active');
        }
    }

    /**
     * Close a modal
     */
    closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
        }
    }
}
