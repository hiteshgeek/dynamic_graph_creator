/**
 * FilterManagerPage - Filter management page controller
 * Handles CRUD operations for graph filters
 */

// Use global helpers from main.js
const Ajax = window.Ajax;
const Loading = window.Loading;
const Toast = window.Toast;

export default class FilterManagerPage {
    constructor(container, options = {}) {
        this.container = container;
        this.graphId = options.graphId || null;

        this.filterModal = null;
        this.deleteModal = null;
        this.filterIdToDelete = null;

        // Filter types that have options
        this.typesWithOptions = ['select', 'multi_select', 'checkbox', 'radio'];

        if (this.container && this.graphId) {
            this.init();
        }
    }

    /**
     * Initialize the filter manager page
     */
    init() {
        this.filterModal = document.getElementById('filter-modal');
        this.deleteModal = document.getElementById('delete-modal');

        this.bindEvents();
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Filter type change - show/hide options
        const filterType = document.getElementById('filter-type');
        if (filterType) {
            filterType.addEventListener('change', () => this.onFilterTypeChange());
        }

        // Add filter button
        const addBtn = this.container.querySelector('.add-filter-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.openFilterModal());
        }

        // Edit filter buttons
        this.container.querySelectorAll('.edit-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const filterId = e.target.closest('.filter-item').dataset.filterId;
                this.loadFilterForEdit(filterId);
            });
        });

        // Delete filter buttons
        this.container.querySelectorAll('.delete-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.filterIdToDelete = e.target.closest('.filter-item').dataset.filterId;
                this.openModal(this.deleteModal);
            });
        });

        // Modal close buttons
        document.querySelectorAll('.modal-close-btn, .modal-cancel-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.closeModal(e.target.closest('.modal-overlay'));
            });
        });

        // Save filter button
        const saveBtn = document.querySelector('.save-filter-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveFilter());
        }

        // Confirm delete button
        const confirmDeleteBtn = document.querySelector('.confirm-delete-btn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                if (this.filterIdToDelete) {
                    this.deleteFilter(this.filterIdToDelete);
                }
            });
        }

        // Add option button
        const addOptionBtn = document.querySelector('.add-option-btn');
        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', () => this.addOptionRow());
        }

        // Remove option buttons (delegated)
        const optionsList = document.querySelector('.filter-options-list');
        if (optionsList) {
            optionsList.addEventListener('click', (e) => {
                if (e.target.closest('.remove-option-btn')) {
                    const row = e.target.closest('.filter-option-item');
                    if (document.querySelectorAll('.filter-option-item').length > 1) {
                        row.remove();
                    }
                }
            });
        }
    }

    /**
     * Handle filter type change - show/hide options section
     */
    onFilterTypeChange() {
        const filterType = document.getElementById('filter-type').value;
        const optionsGroup = document.getElementById('filter-options-group');
        if (optionsGroup) {
            optionsGroup.style.display = this.typesWithOptions.includes(filterType) ? 'block' : 'none';
        }
    }

    /**
     * Open filter modal for add/edit
     */
    openFilterModal(filterData = null) {
        document.getElementById('filter-modal-title').textContent = filterData ? 'Edit Filter' : 'Add Filter';
        document.getElementById('filter-id').value = filterData ? filterData.fid : '';
        document.getElementById('filter-key').value = filterData ? filterData.filter_key : '';
        document.getElementById('filter-label').value = filterData ? filterData.filter_label : '';
        document.getElementById('filter-type').value = filterData ? filterData.filter_type : 'text';
        document.getElementById('filter-default').value = filterData ? (filterData.default_value || '') : '';
        document.getElementById('filter-required').checked = filterData ? filterData.is_required == 1 : false;

        // Handle options visibility
        const optionsGroup = document.getElementById('filter-options-group');
        const filterType = filterData ? filterData.filter_type : 'text';
        optionsGroup.style.display = this.typesWithOptions.includes(filterType) ? 'block' : 'none';

        // Clear and populate options
        const optionsList = document.querySelector('.filter-options-list');
        optionsList.innerHTML = '';

        if (filterData && filterData.filter_options) {
            try {
                const options = JSON.parse(filterData.filter_options);
                if (options.options && options.options.length > 0) {
                    options.options.forEach(opt => {
                        this.addOptionRow(opt.value, opt.label);
                    });
                } else {
                    this.addOptionRow();
                }
            } catch (e) {
                this.addOptionRow();
            }
        } else {
            this.addOptionRow();
        }

        this.openModal(this.filterModal);
    }

    /**
     * Add an option row to the options list
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
    }

    /**
     * Load filter data for editing
     */
    loadFilterForEdit(filterId) {
        Loading.show('Loading filter...');

        Ajax.post('get_filter', { id: filterId })
            .then(result => {
                Loading.hide();
                if (result.success) {
                    this.openFilterModal(result.data);
                } else {
                    Toast.error(result.message || 'Failed to load filter');
                }
            })
            .catch(() => {
                Loading.hide();
                Toast.error('Failed to load filter');
            });
    }

    /**
     * Save filter (create or update)
     */
    saveFilter() {
        const filterType = document.getElementById('filter-type').value;

        // Build options JSON for types that need it
        let options = null;
        if (this.typesWithOptions.includes(filterType)) {
            const optionItems = [];
            document.querySelectorAll('.filter-option-item').forEach(row => {
                const value = row.querySelector('.option-value').value.trim();
                const label = row.querySelector('.option-label').value.trim();
                if (value || label) {
                    optionItems.push({ value: value, label: label || value });
                }
            });
            options = JSON.stringify({ source: 'static', options: optionItems });
        }

        const data = {
            graph_id: this.graphId,
            filter_id: document.getElementById('filter-id').value,
            filter_key: document.getElementById('filter-key').value,
            filter_label: document.getElementById('filter-label').value,
            filter_type: filterType,
            filter_options: options,
            default_value: document.getElementById('filter-default').value,
            is_required: document.getElementById('filter-required').checked ? 1 : 0
        };

        Loading.show('Saving filter...');

        Ajax.post('save_filter', data)
            .then(result => {
                Loading.hide();
                if (result.success) {
                    Toast.success('Filter saved successfully');
                    this.closeModal(this.filterModal);
                    location.reload();
                } else {
                    Toast.error(result.message || 'Failed to save filter');
                }
            })
            .catch(() => {
                Loading.hide();
                Toast.error('Failed to save filter');
            });
    }

    /**
     * Delete a filter
     */
    deleteFilter(filterId) {
        Loading.show('Deleting filter...');

        Ajax.post('delete_filter', { id: filterId })
            .then(result => {
                Loading.hide();
                if (result.success) {
                    Toast.success('Filter deleted');
                    this.closeModal(this.deleteModal);
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

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
