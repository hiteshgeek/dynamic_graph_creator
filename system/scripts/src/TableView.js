/**
 * TableView - Table view page controller
 * Handles filter application and table rendering on view page
 */

import TablePreview from './TablePreview.js';

const Toast = window.Toast;
const Ajax = window.Ajax;

export default class TableView {
    constructor(container, options = {}) {
        this.container = container;
        this.tableId = options.tableId;
        this.config = options.config || {};
        this.filters = {};
        this.autoApply = false;

        this.preview = null;
        this.filterDebounceTimer = null;
    }

    /**
     * Initialize table view
     */
    init() {
        this.initPreview();
        this.initFilters();
        this.initRefreshButton();
        this.initSidebarCollapse();

        // Load initial data
        this.loadTable();
    }

    /**
     * Initialize table preview
     */
    initPreview() {
        const previewContainer = this.container.querySelector('.table-preview-container, .table-view-container');
        if (previewContainer) {
            this.preview = new TablePreview(previewContainer, {
                showSkeleton: true
            });
            if (this.config) {
                this.preview.setConfig(this.config);
            }
        }
    }

    /**
     * Initialize filter handling
     */
    initFilters() {
        const filtersCard = this.container.querySelector('.graph-view-filters');
        if (!filtersCard) return;

        // Filter inputs
        const filterInputs = filtersCard.querySelectorAll('.filter-input');
        filterInputs.forEach(input => {
            input.addEventListener('change', () => this.onFilterChange());
            input.addEventListener('input', () => {
                if (this.autoApply) {
                    this.debounceFilterChange();
                }
            });
        });

        // Select dropdown filters
        const selectOptions = filtersCard.querySelectorAll('.filter-select-option');
        selectOptions.forEach(option => {
            option.addEventListener('click', () => {
                const dropdown = option.closest('.filter-select-dropdown');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                const trigger = dropdown.querySelector('.filter-select-trigger');
                const radio = option.querySelector('input[type="radio"]');

                if (radio) {
                    radio.checked = true;
                }

                if (hiddenInput) {
                    hiddenInput.value = option.dataset.value || '';
                }

                // Update display text
                const label = option.querySelector('.form-check-label');
                if (trigger && label) {
                    trigger.querySelector('.filter-select-placeholder').textContent = label.textContent;
                }

                // Close dropdown
                const bsDropdown = bootstrap.Dropdown.getInstance(trigger);
                if (bsDropdown) {
                    bsDropdown.hide();
                }

                this.onFilterChange();
            });
        });

        // Search in select dropdowns
        const searchInputs = filtersCard.querySelectorAll('.select-search');
        searchInputs.forEach(searchInput => {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const dropdown = searchInput.closest('.filter-select-options');
                const options = dropdown.querySelectorAll('.filter-select-option');

                options.forEach(option => {
                    const label = option.querySelector('.form-check-label');
                    if (label) {
                        const text = label.textContent.toLowerCase();
                        option.style.display = text.includes(query) ? '' : 'none';
                    }
                });
            });

            // Prevent dropdown from closing when clicking search
            searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Apply button
        const applyBtn = filtersCard.querySelector('.filter-apply-btn');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => this.loadTable());
        }

        // Auto-apply toggle
        const autoApplySwitch = filtersCard.querySelector('#auto-apply-switch');
        if (autoApplySwitch) {
            autoApplySwitch.addEventListener('change', (e) => {
                this.autoApply = e.target.checked;
                if (this.autoApply) {
                    this.loadTable();
                }
            });
        }

        // Initialize datepickers
        this.initDatepickers();
    }

    /**
     * Initialize datepickers
     */
    initDatepickers() {
        if (typeof window.initializeDGCDatepickers === 'function') {
            window.initializeDGCDatepickers(this.container);
        }
    }

    /**
     * Handle filter change
     */
    onFilterChange() {
        if (this.autoApply) {
            this.loadTable();
        }
    }

    /**
     * Debounce filter changes for auto-apply
     */
    debounceFilterChange() {
        clearTimeout(this.filterDebounceTimer);
        this.filterDebounceTimer = setTimeout(() => {
            this.loadTable();
        }, 300);
    }

    /**
     * Get current filter values
     */
    getFilterValues() {
        const filtersCard = this.container.querySelector('.graph-view-filters');
        if (!filtersCard) return {};

        const filters = {};
        const filterItems = filtersCard.querySelectorAll('.filter-input-item');

        filterItems.forEach(item => {
            const filterKey = item.dataset.filterKey;
            if (!filterKey) return;

            // Handle different input types
            const hiddenInput = item.querySelector('input[type="hidden"].filter-input');
            const textInput = item.querySelector('input[type="text"].filter-input, input[type="number"].filter-input');
            const datepicker = item.querySelector('.dgc-datepicker');

            if (hiddenInput) {
                filters[filterKey] = hiddenInput.value;
            } else if (datepicker) {
                // Handle datepicker
                const pickerType = datepicker.dataset.pickerType;
                if (pickerType === 'range' || pickerType === 'main') {
                    const startDate = datepicker.dataset.startDate;
                    const endDate = datepicker.dataset.endDate;
                    if (startDate) filters[filterKey + '_from'] = startDate;
                    if (endDate) filters[filterKey + '_to'] = endDate;
                } else {
                    filters[filterKey] = datepicker.value;
                }
            } else if (textInput) {
                filters[filterKey] = textInput.value;
            }
        });

        return filters;
    }

    /**
     * Load table data
     */
    async loadTable() {
        if (!this.preview) return;

        this.preview.showLoading();

        const filters = this.getFilterValues();

        try {
            const result = await Ajax.post('preview_table', {
                id: this.tableId,
                filters: filters
            });

            if (result.success && result.data) {
                if (result.data.config) {
                    this.preview.setConfig(result.data.config);
                }
                if (result.data.tableData) {
                    this.preview.setData(result.data.tableData);
                    this.preview.render();
                } else {
                    this.preview.showEmpty('No data returned');
                }
            } else {
                this.preview.showError(result.message || 'Failed to load table');
            }
        } catch (error) {
            this.preview.showError('Failed to load table data');
            console.error('Table load failed:', error);
        }
    }

    /**
     * Initialize refresh button
     */
    initRefreshButton() {
        const refreshBtn = this.container.querySelector('#refresh-table');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadTable());
        }
    }

    /**
     * Initialize sidebar collapse functionality
     */
    initSidebarCollapse() {
        const sidebar = document.getElementById('graph-view-sidebar');
        const collapseBtn = sidebar ? sidebar.querySelector('.collapse-btn') : null;

        if (sidebar && collapseBtn) {
            collapseBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('graphViewSidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }
    }
}

// Export for global access
window.TableView = TableView;
