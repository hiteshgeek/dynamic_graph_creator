/**
 * GraphView - Graph viewing page controller
 * Handles graph display and filter application
 */

import GraphPreview from './GraphPreview.js';
import GraphExporter from './GraphExporter.js';
import DataFilterUtils from './DataFilterUtils.js';

// Use global helpers from main.js
const Ajax = window.Ajax;
const Loading = window.Loading;
const Toast = window.Toast;


export default class GraphView {
    constructor(container, options = {}) {
        this.container = container;
        this.graphId = options.graphId || null;
        this.graphType = options.graphType || 'bar';
        this.graphName = options.graphName || 'Chart';
        this.config = options.config || {};

        this.preview = null;
        this.exporter = null;

        this.hasFilters = options.hasFilters || false;

        if (this.container && this.graphId) {
            this.init();
        }
    }

    /**
     * Initialize graph view
     */
    init() {
        this.initFilters();
        this.bindEvents();
        this.initSidebarCollapse();

        // Restore auto-apply setting from localStorage
        this.restoreAutoApplySetting();

        // Always initialize preview and load graph data
        // Filter values will be automatically picked up from pre-selected options (is_selected)
        this.initPreview();
        this.initExporter();
        this.loadGraphData();
    }

    /**
     * Initialize preview component
     */
    initPreview() {
        const previewContainer = this.container.querySelector('.graph-preview-container');
        if (previewContainer && !this.preview) {
            this.preview = new GraphPreview(previewContainer);
            this.preview.setType(this.graphType);
            this.preview.setConfig(this.config);
        }
    }

    /**
     * Show message prompting user to apply filters
     */
    showFilterMessage() {
        const previewContainer = this.container.querySelector('.graph-preview-container');
        if (previewContainer) {
            previewContainer.innerHTML = `
                <div class="graph-filter-message">
                    <i class="fas fa-filter"></i>
                    <p>Please select filter values and click <strong>Apply Filters</strong> to view the chart.</p>
                </div>
            `;
        }
    }

    /**
     * Initialize filter components (date pickers, multi-select dropdowns, etc.)
     * Uses FilterRenderer for centralized initialization
     */
    initFilters() {
        const filtersContainer = this.container.querySelector('#graph-filters');
        if (!filtersContainer) return;

        // Use FilterRenderer for initialization (handles datepickers, multi-selects, etc.)
        if (typeof FilterRenderer !== 'undefined') {
            FilterRenderer.initPickers(filtersContainer);

            // Listen for filter changes from FilterRenderer-initialized components
            filtersContainer.addEventListener('change', () => {
                this.onFilterChange();
            });
        } else if (typeof DatePickerInit !== 'undefined') {
            // Fallback to just datepickers
            DatePickerInit.init(filtersContainer);
        }
    }

    /**
     * Initialize exporter component
     */
    initExporter() {
        const exportContainer = this.container.querySelector('#export-chart-container');
        if (!exportContainer) return;

        this.exporter = new GraphExporter({
            filename: this.graphName,
            container: exportContainer,
            graphId: this.graphId,
            onSaveSuccess: (data) => {
                console.log('Snapshot saved:', data);
            }
        });

        // Update chart reference when preview renders
        if (this.preview) {
            this.preview.onRender(() => {
                if (this.preview.chart) {
                    this.exporter.setChart(this.preview.chart);
                    this.exporter.setFilename(this.graphName);
                }
            });
        }
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Apply filters button
        this.applyBtn = this.container.querySelector('.filter-apply-btn');
        if (this.applyBtn) {
            this.applyBtn.addEventListener('click', () => this.loadGraphData());
        }

        // Auto-apply switch
        this.autoApplySwitch = this.container.querySelector('#auto-apply-switch');
        if (this.autoApplySwitch) {
            this.autoApplySwitch.addEventListener('change', () => this.toggleAutoApply());
        }

        // Bind filter inputs for auto-apply
        this.bindFilterInputs();
    }

    /**
     * Bind filter inputs for auto-apply functionality
     */
    bindFilterInputs() {
        // Bind standard filter inputs
        const filterInputs = this.container.querySelectorAll('.filter-input');
        filterInputs.forEach(input => {
            // For select, checkbox, radio - trigger on change
            if (input.tagName === 'SELECT' || input.type === 'checkbox' || input.type === 'radio') {
                input.addEventListener('change', () => this.onFilterChange());
            } else {
                // For text/number inputs - trigger on change (blur) and debounced input
                input.addEventListener('change', () => this.onFilterChange());
                input.addEventListener('input', () => this.onFilterInputDebounced());
            }
        });

        // Bind checkbox group inputs
        const checkboxInputs = this.container.querySelectorAll('.filter-checkbox-group input[type="checkbox"], .filter-radio-group input[type="radio"]');
        checkboxInputs.forEach(input => {
            input.addEventListener('change', () => this.onFilterChange());
        });

        // Bind multi-select checkbox inputs
        const multiSelectInputs = this.container.querySelectorAll('.filter-multiselect-options input[type="checkbox"]');
        multiSelectInputs.forEach(input => {
            input.addEventListener('change', () => this.onFilterChange());
        });
    }

    /**
     * Debounced filter input handler
     */
    onFilterInputDebounced() {
        if (!this.autoApplyEnabled) return;

        clearTimeout(this.filterDebounceTimer);
        this.filterDebounceTimer = setTimeout(() => {
            this.loadGraphData();
        }, 500);
    }

    /**
     * Handle filter change
     */
    onFilterChange() {
        if (this.autoApplyEnabled) {
            this.loadGraphData();
        }
    }

    /**
     * Toggle auto-apply mode
     */
    toggleAutoApply() {
        this.autoApplyEnabled = this.autoApplySwitch?.checked || false;

        // Save setting to localStorage
        localStorage.setItem('dgc_auto_apply_filters', this.autoApplyEnabled ? '1' : '0');

        // Hide/show apply button and separator
        this.updateAutoApplyUI();

        // If auto-apply is enabled, apply immediately
        if (this.autoApplyEnabled) {
            this.loadGraphData();
        }
    }

    /**
     * Restore auto-apply setting from localStorage
     */
    restoreAutoApplySetting() {
        const saved = localStorage.getItem('dgc_auto_apply_filters');
        if (saved === '1' && this.autoApplySwitch) {
            this.autoApplySwitch.checked = true;
            this.autoApplyEnabled = true;
        }
        // Always call updateAutoApplyUI to show/hide elements based on state
        this.updateAutoApplyUI();
    }

    /**
     * Update UI based on auto-apply state
     * Elements are hidden by default in CSS, we add 'visible' class to show them
     */
    updateAutoApplyUI() {
        const separator = this.container.querySelector('.filter-actions-separator');

        if (this.autoApplyEnabled) {
            // Live filtering enabled - hide button and separator
            if (this.applyBtn) this.applyBtn.classList.remove('visible');
            if (separator) separator.classList.remove('visible');
        } else {
            // Live filtering disabled - show button and separator
            if (this.applyBtn) this.applyBtn.classList.add('visible');
            if (separator) separator.classList.add('visible');
        }
    }

    /**
     * Export chart as image
     */
    exportChart() {
        if (this.preview && this.preview.chart) {
            this.exporter.setChart(this.preview.chart);
            this.exporter.exportImage();
        }
    }

    /**
     * Load graph data from server
     */
    loadGraphData() {
        const filterValues = this.getFilterValues();

        Loading.show('Loading graph...');

        Ajax.post('preview_graph', {
            id: this.graphId,
            filters: filterValues
        }).then(result => {
            Loading.hide();
            if (result.success && result.data) {
                // Clear any filter message and ensure preview is initialized
                this.ensurePreviewReady();
                this.preview.setData(result.data.chartData);
                this.preview.render();
            } else {
                Toast.error(result.message || 'Failed to load graph');
            }
        }).catch(error => {
            Loading.hide();
            Toast.error('Failed to load graph');
        });
    }

    /**
     * Ensure preview container is ready for rendering
     * Clears filter message and re-initializes preview if needed
     */
    ensurePreviewReady() {
        const previewContainer = this.container.querySelector('.graph-preview-container');
        if (!previewContainer) return;

        // Check if filter message is showing
        const filterMessage = previewContainer.querySelector('.graph-filter-message');
        if (filterMessage) {
            // Clear the container
            previewContainer.innerHTML = '';
        }

        // Initialize preview if not already done
        if (!this.preview) {
            this.preview = new GraphPreview(previewContainer);
            this.preview.setType(this.graphType);
            this.preview.setConfig(this.config);
        }
    }

    /**
     * Initialize sidebar collapse functionality
     */
    initSidebarCollapse() {
        const sidebar = document.querySelector('.graph-view-sidebar');
        const header = sidebar?.querySelector('.sidebar-header');
        const collapseBtn = sidebar?.querySelector('.collapse-btn');

        if (!sidebar) return;

        // Toggle on header click
        if (header) {
            header.addEventListener('click', (e) => {
                // Don't toggle if clicking a link inside the header
                if (e.target.closest('a')) return;
                this.toggleSidebar();
            });
        }

        // Toggle on collapse button click
        if (collapseBtn) {
            collapseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleSidebar();
            });
        }
    }

    /**
     * Toggle sidebar collapsed state
     */
    toggleSidebar() {
        const sidebar = document.querySelector('.graph-view-sidebar');

        if (!sidebar) return;

        sidebar.classList.toggle('collapsed');

        // Save state to localStorage
        localStorage.setItem('graphViewSidebarCollapsed', sidebar.classList.contains('collapsed') ? 'true' : 'false');

        // Resize chart after sidebar animation
        setTimeout(() => {
            if (this.preview) {
                this.preview.resize();
            }
        }, 350);
    }

    /**
     * Get filter values from inputs
     */
    getFilterValues() {
        const filtersContainer = this.container.querySelector('#graph-filters');
        return DataFilterUtils.getValues(filtersContainer, { visibleOnly: false });
    }
}
