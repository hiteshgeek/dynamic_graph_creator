/**
 * GraphView - Graph viewing page controller
 * Handles graph display and filter application
 */

import GraphPreview from './GraphPreview.js';
import GraphExporter from './GraphExporter.js';

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
        // Initialize exporter
        this.exporter = new GraphExporter({
            filename: this.graphName
        });

        this.initFilters();
        this.bindEvents();

        // Only auto-load if no filters are present
        // When filters exist, wait for user to apply them first
        if (!this.hasFilters) {
            this.initPreview();
            this.loadGraphData();
        } else {
            this.showFilterMessage();
        }
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
     * Initialize filter components (multi-select dropdowns, etc.)
     */
    initFilters() {
        const filtersContainer = this.container.querySelector('#graph-filters');
        if (!filtersContainer) return;

        // Initialize multi-select dropdowns
        const multiSelectDropdowns = filtersContainer.querySelectorAll('.filter-multiselect-dropdown');
        multiSelectDropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.filter-multiselect-trigger');
            const optionsPanel = dropdown.querySelector('.filter-multiselect-options');
            const placeholder = dropdown.querySelector('.filter-multiselect-placeholder');
            const optionItems = dropdown.querySelectorAll('.filter-multiselect-option');
            const checkboxes = dropdown.querySelectorAll('.filter-multiselect-option input[type="checkbox"]');
            const selectAllBtn = dropdown.querySelector('.multiselect-select-all');
            const selectNoneBtn = dropdown.querySelector('.multiselect-select-none');
            const searchInput = dropdown.querySelector('.multiselect-search');

            if (!trigger || !optionsPanel) return;

            // Helper function to update placeholder
            const updatePlaceholder = () => {
                const selected = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.nextElementSibling?.textContent || cb.value);

                if (selected.length === 0) {
                    placeholder.textContent = '-- Select multiple --';
                    placeholder.classList.remove('has-selection');
                } else if (selected.length <= 2) {
                    placeholder.textContent = selected.join(', ');
                    placeholder.classList.add('has-selection');
                } else {
                    placeholder.textContent = `${selected.length} selected`;
                    placeholder.classList.add('has-selection');
                }
            };

            // Select All button
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    optionItems.forEach(item => {
                        if (item.style.display !== 'none') {
                            const cb = item.querySelector('input[type="checkbox"]');
                            if (cb) cb.checked = true;
                        }
                    });
                    updatePlaceholder();
                });
            }

            // Select None button
            if (selectNoneBtn) {
                selectNoneBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    optionItems.forEach(item => {
                        if (item.style.display !== 'none') {
                            const cb = item.querySelector('input[type="checkbox"]');
                            if (cb) cb.checked = false;
                        }
                    });
                    updatePlaceholder();
                });
            }

            // Search functionality
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    optionItems.forEach(item => {
                        const label = item.querySelector('.form-check-label')?.textContent.toLowerCase() || '';
                        if (searchTerm === '' || label.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });

                // Prevent dropdown from closing when clicking search input
                searchInput.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            }

            // Update placeholder text when checkboxes change
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updatePlaceholder);
            });
        });
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Export button
        const exportBtn = this.container.querySelector('#export-chart');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportChart());
        }

        // Apply filters button
        const applyBtn = this.container.querySelector('.filter-apply-btn');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => this.loadGraphData());
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
     * Get filter values from inputs
     */
    getFilterValues() {
        const filterValues = {};
        const filtersContainer = this.container.querySelector('#graph-filters');

        if (!filtersContainer) return filterValues;

        const filterItems = filtersContainer.querySelectorAll('.filter-input-item');

        filterItems.forEach(item => {
            const filterKey = item.dataset.filterKey;
            if (!filterKey) return;

            // Single select
            const select = item.querySelector('select.filter-input');
            if (select && select.value) {
                filterValues['::' + filterKey] = select.value;
                return;
            }

            // Multi-select dropdown (checkboxes)
            const multiSelectChecked = item.querySelectorAll('.filter-multiselect-options input[type="checkbox"]:checked');
            if (multiSelectChecked.length > 0) {
                const values = Array.from(multiSelectChecked).map(cb => cb.value);
                filterValues['::' + filterKey] = values;
                return;
            }

            // Checkbox group
            const checkboxChecked = item.querySelectorAll('.filter-checkbox-group input[type="checkbox"]:checked');
            if (checkboxChecked.length > 0) {
                const values = Array.from(checkboxChecked).map(cb => cb.value);
                filterValues['::' + filterKey] = values;
                return;
            }

            // Radio group
            const radioChecked = item.querySelector('.filter-radio-group input[type="radio"]:checked');
            if (radioChecked) {
                filterValues['::' + filterKey] = radioChecked.value;
                return;
            }

            // Text/number/date input
            const textInput = item.querySelector('input.filter-input');
            if (textInput && textInput.value) {
                filterValues['::' + filterKey] = textInput.value;
                return;
            }

            // Date range
            const dateFrom = item.querySelector('input[name$="_from"]');
            const dateTo = item.querySelector('input[name$="_to"]');
            if (dateFrom && dateTo) {
                if (dateFrom.value) filterValues['::' + filterKey + '_from'] = dateFrom.value;
                if (dateTo.value) filterValues['::' + filterKey + '_to'] = dateTo.value;
            }
        });

        return filterValues;
    }
}
