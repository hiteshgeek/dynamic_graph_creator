/**
 * CounterView - Counter view page functionality
 * Handles filter application and counter refresh on view page
 */

import CounterPreview from './CounterPreview.js';
import DatePickerInit from './DatePickerInit.js';
import FilterRenderer from './FilterRenderer.js';
import DataFilterUtils from './DataFilterUtils.js';

const Toast = window.Toast;
const Ajax = window.Ajax;

export default class CounterView {
    constructor(container, options = {}) {
        this.container = container;
        this.counterId = options.counterId;
        this.counterName = options.counterName || 'Counter';
        this.config = options.config || {};
        this.hasFilters = options.hasFilters || false;

        this.preview = null;
        this.autoApply = false;
        this.debounceTimer = null;
    }

    /**
     * Initialize the view
     */
    init() {
        this.initPreview();
        this.initSidebar();
        this.initFilters();
        this.initRefreshButton();
        this.loadCounter();
    }

    /**
     * Initialize counter preview
     */
    initPreview() {
        const previewContainer = this.container.querySelector('.counter-preview-container');
        if (previewContainer) {
            this.preview = new CounterPreview(previewContainer, this.config);
        }
    }

    /**
     * Initialize sidebar collapse
     */
    initSidebar() {
        const sidebar = document.getElementById('counter-view-sidebar');
        const collapseBtn = sidebar?.querySelector('.collapse-btn');

        if (!sidebar || !collapseBtn) return;

        collapseBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('counterViewSidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    /**
     * Initialize filters
     */
    initFilters() {
        if (!this.hasFilters) return;

        const filtersContainer = this.container.querySelector('#counter-filters');
        const applyBtn = this.container.querySelector('.filter-apply-btn');
        const autoApplySwitch = this.container.querySelector('#auto-apply-switch');

        // Initialize filter inputs
        if (filtersContainer) {
            FilterRenderer.init(filtersContainer);
            DatePickerInit.init(filtersContainer);
        }

        // Apply button
        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                this.loadCounter();
            });
        }

        // Auto-apply toggle
        if (autoApplySwitch) {
            // Load saved state
            const savedState = localStorage.getItem('counterViewAutoApply');
            this.autoApply = savedState === 'true';
            autoApplySwitch.checked = this.autoApply;

            autoApplySwitch.addEventListener('change', (e) => {
                this.autoApply = e.target.checked;
                localStorage.setItem('counterViewAutoApply', this.autoApply);
                if (this.autoApply) {
                    this.loadCounter();
                }
            });
        }

        // Filter change listeners for auto-apply
        if (filtersContainer) {
            filtersContainer.addEventListener('change', () => {
                if (this.autoApply) {
                    this.debouncedLoad();
                }
            });

            filtersContainer.addEventListener('input', (e) => {
                if (this.autoApply && e.target.matches('input[type="text"], input[type="number"]')) {
                    this.debouncedLoad();
                }
            });
        }
    }

    /**
     * Initialize refresh button
     */
    initRefreshButton() {
        const refreshBtn = this.container.querySelector('#refresh-counter');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadCounter();
            });
        }
    }

    /**
     * Debounced load for auto-apply
     */
    debouncedLoad() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        this.debounceTimer = setTimeout(() => {
            this.loadCounter();
        }, 500);
    }

    /**
     * Get current filter values
     */
    getFilterValues() {
        const filtersContainer = this.container.querySelector('#counter-filters');
        return DataFilterUtils.getValues(filtersContainer);
    }

    /**
     * Load counter data
     */
    async loadCounter() {
        if (!this.counterId) return;

        if (this.preview) {
            this.preview.showLoading();
        }

        try {
            const filterValues = this.getFilterValues();

            const result = await Ajax.post('preview_counter', {
                id: this.counterId,
                filters: filterValues
            });

            if (result.success && result.data) {
                if (result.data.config) {
                    this.config = { ...this.config, ...result.data.config };
                }

                if (this.preview) {
                    this.preview.setConfig(this.config);

                    if (result.data.counterData && typeof result.data.counterData.value !== 'undefined') {
                        this.preview.setValue(result.data.counterData.value);
                    } else {
                        this.preview.setValue(0);
                    }
                }
            } else {
                Toast.error(result.message || 'Failed to load counter data');
                if (this.preview) {
                    this.preview.setValue(0);
                }
            }
        } catch (error) {
            Toast.error('Failed to load counter data');
            console.error(error);
            if (this.preview) {
                this.preview.setValue(0);
            }
        }
    }
}
