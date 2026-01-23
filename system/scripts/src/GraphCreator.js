/**
 * GraphCreator - Graph-specific creator class
 * Extends ElementCreator with chart-specific functionality
 */

import ElementCreator from './element/ElementCreator.js';
import GraphPreview from './GraphPreview.js';
import GraphExporter from './GraphExporter.js';
import ConfigPanel from './ConfigPanel.js';

const Toast = window.Toast;
const Ajax = window.Ajax;
const autosize = window.autosize;

export default class GraphCreator extends ElementCreator {
    constructor(container, options = {}) {
        super(container, {
            ...options,
            elementId: options.graphId || options.elementId || null
        });

        // Graph-specific properties
        this.graphId = this.elementId;
        this.graphType = 'bar';
        this.graphName = '';
        this.graphDescription = '';

        // Graph-specific components
        this.exporter = null;
        this.configPanel = null;

        // Mini preview state
        this.miniPreviewEl = null;
        this.miniToggleBtn = null;
        this.miniChart = null;
        this.miniPreviewVisible = false;
        this.miniPreviewDismissed = false;
        this.miniToggleVisible = false;
        this.miniPreviewSize = 'compact';
        this.miniPreviewOpacity = 1.0;
        this.previewCard = null;
        this.previewObserver = null;
    }

    /**
     * Element type identifiers
     */
    getElementTypeSlug() {
        return 'graph';
    }

    getElementTypeName() {
        return 'Graph';
    }

    /**
     * Initialize all components
     */
    init() {
        super.init();

        // Show dummy data for new graph
        if (!this.graphId && this.preview) {
            this.preview.showDummyData(this.graphType);
        }
    }

    /**
     * Initialize graph preview component
     */
    initPreview() {
        const previewContainer = this.container.querySelector('.graph-preview-container');
        if (previewContainer) {
            this.preview = new GraphPreview(previewContainer, { showSkeleton: false });
        }
    }

    /**
     * Initialize graph-specific components
     */
    initTypeSpecificComponents() {
        this.initExporter();
        this.initConfigPanel();
        this.initGraphTypeSelector();
        this.initMiniPreview();
    }

    /**
     * Initialize graph exporter component
     */
    initExporter() {
        const exportContainer = this.container.querySelector('#export-chart-container');
        if (!exportContainer) return;

        this.exporter = new GraphExporter({
            filename: this.graphName || 'chart-preview',
            container: exportContainer,
            graphId: this.graphId,
            onSaveSuccess: (data) => {
                console.log('Snapshot saved:', data);
            }
        });

        if (this.preview) {
            this.preview.onRender(() => {
                if (this.preview.chart) {
                    this.exporter.setChart(this.preview.chart);
                    this.exporter.setFilename(this.graphName || 'chart-preview');
                }
            });
        }
    }

    /**
     * Initialize config panel component
     */
    initConfigPanel() {
        const configContainer = this.container.querySelector('.graph-config-panel');
        if (configContainer) {
            this.configPanel = new ConfigPanel(configContainer, {
                onChange: () => {
                    this.updatePreview();
                    this.checkForChanges();
                }
            });
            this.configPanel.setGraphType(this.graphType);
        }
    }

    /**
     * Initialize data mapper with graph type
     */
    initDataMapper() {
        super.initDataMapper();
        if (this.dataMapper) {
            this.dataMapper.setGraphType(this.graphType);
        }
    }

    /**
     * Initialize graph type selector
     */
    initGraphTypeSelector() {
        const typeItems = this.container.querySelectorAll('.graph-type-item, .chart-type-item');

        const selectType = (item) => {
            const type = item.dataset.type;
            this.setGraphType(type);

            typeItems.forEach(i => {
                i.classList.remove('active');
                i.setAttribute('aria-checked', 'false');
            });
            item.classList.add('active');
            item.setAttribute('aria-checked', 'true');
        };

        typeItems.forEach(item => {
            item.addEventListener('click', () => selectType(item));
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectType(item);
                }
            });
        });
    }

    /**
     * Set graph type and update components
     */
    setGraphType(type) {
        this.graphType = type;

        if (this.dataMapper) {
            this.dataMapper.setGraphType(type);
        }

        if (this.configPanel) {
            this.configPanel.setGraphType(type);
        }

        if (this.preview) {
            this.preview.setType(type);
            if (!this.isLoading) {
                if (this.columns.length === 0) {
                    this.preview.showDummyData(type);
                } else {
                    this.updatePreview();
                }
            }
        }

        this.updateMiniToggleIcon();
        this.checkForChanges();
    }

    /**
     * Handle query test success
     */
    onQueryTest(columns) {
        this.columns = columns;

        if (this.dataMapper) {
            this.dataMapper.setColumns(columns);
        }

        this.clearQueryError();
        Toast.success(`Query valid. Found ${columns.length} columns.`);
        this.updatePreview();
    }

    /**
     * Update preview with current data
     */
    async updatePreview() {
        if (!this.preview) return;

        if (this.isLoading || this.initialLoad) return;

        const config = this.configPanel ? this.configPanel.getConfig() : {};
        const mapping = this.dataMapper ? this.dataMapper.getMapping() : {};

        if (this.columns.length === 0) {
            this.preview.setConfig(config);
            this.preview.setMapping(mapping);
            this.preview.showDummyData(this.graphType);
            return;
        }

        if (!this.validateMapping(mapping)) {
            this.preview.setConfig(config);
            this.preview.setMapping(mapping);
            this.preview.showDummyData(this.graphType);
            return;
        }

        const query = this.queryBuilder ? this.queryBuilder.getQuery() : '';
        const filterValues = this.getSidebarFilterValues();
        const placeholderSettings = this.getPlaceholderSettingsForQuery();

        this.preview.showSkeleton(this.graphType);

        try {
            const result = await Ajax.post('preview_graph', {
                query: query,
                mapping: mapping,
                config: config,
                graph_type: this.graphType,
                filters: filterValues,
                placeholder_settings: placeholderSettings
            });

            if (result.success && result.data) {
                this.preview.setData(result.data.chartData);
                this.preview.setConfig(config);
                this.preview.setMapping(mapping);
                this.preview.render();
            } else {
                this.preview.hideSkeleton();
            }
            this.initialLoad = false;
        } catch (error) {
            this.preview.hideSkeleton();
            this.initialLoad = false;
            console.error('Preview update failed:', error);
        }
    }

    /**
     * Validate mapping based on graph type
     */
    validateMapping(mapping) {
        if (this.graphType === 'pie') {
            return mapping.name_column && mapping.value_column;
        } else {
            return mapping.x_column && mapping.y_column;
        }
    }

    /**
     * Load graph-specific data when editing
     */
    async loadElement(graphId) {
        this.isLoading = true;

        try {
            const result = await Ajax.post('load_graph', { id: graphId });

            if (result.success && result.data) {
                const graph = result.data;

                this.graphName = graph.name;
                this.graphDescription = graph.description || '';
                this.graphType = graph.graph_type;

                // Sync with parent class
                this.elementName = this.graphName;
                this.elementDescription = this.graphDescription;

                const nameInput = this.container.querySelector('.graph-name-input');
                if (nameInput) nameInput.value = graph.name;

                const descriptionInput = this.container.querySelector('.graph-description-input');
                if (descriptionInput) {
                    descriptionInput.value = this.graphDescription;
                    autosize.update(descriptionInput);
                }

                const typeItems = this.container.querySelectorAll('.graph-type-item, .chart-type-item');
                typeItems.forEach(item => {
                    item.classList.toggle('active', item.dataset.type === graph.graph_type);
                });

                this.setGraphType(graph.graph_type);

                if (this.queryBuilder && graph.query) {
                    this.queryBuilder.setQuery(graph.query);
                }

                if (graph.config) {
                    const config = JSON.parse(graph.config);

                    if (config.activeSidebarTab) {
                        this.setActiveSidebarTab(config.activeSidebarTab);
                    }

                    if (this.configPanel) {
                        this.configPanel.setConfig(config);
                    }
                }

                if (this.placeholderSettings) {
                    let placeholderSettings = {};
                    if (graph.placeholder_settings) {
                        placeholderSettings = typeof graph.placeholder_settings === 'string'
                            ? JSON.parse(graph.placeholder_settings)
                            : graph.placeholder_settings;
                    } else if (graph.config) {
                        const config = typeof graph.config === 'string'
                            ? JSON.parse(graph.config)
                            : graph.config;
                        if (config.placeholderSettings) {
                            placeholderSettings = config.placeholderSettings;
                        }
                    }
                    this.placeholderSettings.setSettings(placeholderSettings);
                }

                if (this.filterManager && graph.filters) {
                    this.filterManager.setFilters(graph.filters);
                }

                if (this.dataMapper && graph.data_mapping) {
                    const mapping = JSON.parse(graph.data_mapping);
                    this.dataMapper.setMapping(mapping, false);

                    this.isLoading = false;
                    this.initialLoad = false;

                    if (graph.query) {
                        await this.queryBuilder.testQuery();
                    }
                }

                this.updatePlaceholderSettings();
                this.captureState();
                this.setUnsavedChanges(false);
            } else {
                if (this.preview) {
                    this.preview.hideSkeleton();
                }
                Toast.error(result.message || 'Failed to load graph');
            }
        } catch (error) {
            if (this.preview) {
                this.preview.hideSkeleton();
            }
            Toast.error('Failed to load graph');
            console.error(error);
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Get config from config panel
     */
    getConfig() {
        return this.configPanel ? this.configPanel.getConfig() : {};
    }

    /**
     * Get graph-specific save data
     */
    getTypeSpecificSaveData() {
        return {
            graph_type: this.graphType
        };
    }

    /**
     * Get current state including graph-specific data
     */
    getCurrentState() {
        const state = super.getCurrentState();
        state.graphType = this.graphType;
        return state;
    }

    /**
     * Check for changes including graph-specific data
     */
    checkForChanges() {
        if (!this.savedState) {
            this.setUnsavedChanges(false);
            return;
        }

        const currentState = this.getCurrentState();
        const hasChanges =
            currentState.name !== this.savedState.name ||
            currentState.description !== this.savedState.description ||
            currentState.graphType !== this.savedState.graphType ||
            currentState.query !== this.savedState.query ||
            currentState.mapping !== this.savedState.mapping ||
            currentState.config !== this.savedState.config ||
            currentState.filters !== this.savedState.filters ||
            currentState.placeholderSettings !== this.savedState.placeholderSettings ||
            currentState.categories !== this.savedState.categories;

        this.setUnsavedChanges(hasChanges);
    }

    /**
     * Save graph
     */
    async save() {
        const errors = [];

        if (this.formValidator && !this.formValidator.validate()) {
            this.expandSidebar();
            errors.push('Graph name is required');
        }

        if (!this.validateCategories()) {
            this.expandSidebar();
            errors.push('At least one category is required');
        }

        const query = this.queryBuilder ? this.queryBuilder.getQuery() : '';
        if (!query.trim()) {
            errors.push('SQL query is required');
        }

        if (this.mandatoryFilterValidator && query.trim()) {
            const mandatoryValidation = this.mandatoryFilterValidator.validateQuery(query);
            if (!mandatoryValidation.valid) {
                const errorMsg = this.mandatoryFilterValidator.getErrorMessage(mandatoryValidation.missing);
                errors.push(errorMsg);
            }
        }

        const mapping = this.dataMapper ? this.dataMapper.getMapping() : {};
        if (!this.validateMapping(mapping)) {
            errors.push('Data mapping is incomplete (run query first to see columns)');
        }

        if (errors.length > 0) {
            Toast.error(errors.map(err => '• ' + err).join('<br>'));
            return;
        }

        Loading.show('Saving graph...');

        try {
            const config = this.configPanel ? this.configPanel.getConfig() : {};
            config.activeSidebarTab = this.activeSidebarTab;

            const placeholderSettings = this.placeholderSettings
                ? this.placeholderSettings.getSettings()
                : {};

            const data = {
                id: this.graphId,
                name: this.graphName || this.elementName,
                description: this.graphDescription || this.elementDescription,
                graph_type: this.graphType,
                query: query,
                data_mapping: mapping,
                config: config,
                placeholder_settings: placeholderSettings,
                filters: this.filterManager ? this.filterManager.getFilters() : [],
                categories: this.selectedCategories || []
            };

            const result = await Ajax.post('save_graph', data);

            if (result.success) {
                const successMsg = result.message || (this.graphId ? 'Graph updated successfully' : 'Graph created successfully');
                Toast.success(successMsg);

                this.captureState();
                this.setUnsavedChanges(false);

                if (!this.graphId && result.data && result.data.id) {
                    window.location.href = `?urlq=widget-graph/edit/${result.data.id}`;
                }
            } else {
                Toast.error(result.message || 'Failed to save graph');
            }
        } catch (error) {
            Toast.error('Failed to save graph');
            console.error(error);
        } finally {
            Loading.hide();
        }
    }

    /**
     * Override save handler to sync graph name
     */
    initSaveHandler() {
        const saveBtn = document.querySelector('.page-header-right .save-graph-btn');
        const nameInput = this.container.querySelector('.graph-name-input');
        const descriptionInput = this.container.querySelector('.graph-description-input');

        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.save();
            });
        }

        if (nameInput) {
            this.graphName = nameInput.value || '';
            this.elementName = this.graphName;

            nameInput.addEventListener('input', (e) => {
                this.graphName = e.target.value;
                this.elementName = this.graphName;
                this.checkForChanges();
            });
        }

        if (descriptionInput) {
            this.graphDescription = descriptionInput.value || '';
            this.elementDescription = this.graphDescription;

            autosize(descriptionInput);

            descriptionInput.addEventListener('input', (e) => {
                this.graphDescription = e.target.value;
                this.elementDescription = this.graphDescription;
                this.checkForChanges();
            });
        }
    }

    // ============================================
    // Mini Preview Methods (Graph-specific)
    // ============================================

    /**
     * Initialize mini chart preview
     */
    initMiniPreview() {
        const previewCard = this.container.querySelector('.graph-preview-card');
        if (!previewCard || !this.preview) return;

        this.previewCard = previewCard;
        this.miniPreviewSize = 'compact';

        const savedOpacity = localStorage.getItem('miniPreviewOpacity');
        this.miniPreviewOpacity = savedOpacity ? parseFloat(savedOpacity) : 1.0;

        this.miniPreviewEl = document.createElement('div');
        this.miniPreviewEl.className = 'mini-chart-preview size-compact';
        this.miniPreviewEl.style.opacity = this.miniPreviewOpacity;
        this.miniPreviewEl.innerHTML = `
            <div class="mini-chart-header">
                <div class="mini-chart-size-buttons">
                    <button type="button" class="mini-size-btn active" data-size="compact" title="Compact">Compact</button>
                    <button type="button" class="mini-size-btn" data-size="expanded" title="Expanded">Expanded</button>
                </div>
                <div class="mini-chart-opacity-slider">
                    <input type="range" min="50" max="100" value="${Math.round(this.miniPreviewOpacity * 100)}" class="mini-opacity-input" title="Opacity">
                </div>
                <button type="button" class="mini-chart-close" title="Minimize">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <div class="mini-chart-content"></div>
        `;
        document.body.appendChild(this.miniPreviewEl);

        this.miniToggleBtn = document.createElement('button');
        this.miniToggleBtn.type = 'button';
        this.miniToggleBtn.className = 'mini-chart-toggle';
        this.miniToggleBtn.innerHTML = '<i class="fas ' + this.getChartTypeIcon() + '"></i>';
        this.miniToggleBtn.title = 'Show mini preview';
        document.body.appendChild(this.miniToggleBtn);

        this.miniChart = null;
        this.miniPreviewVisible = false;
        this.miniPreviewDismissed = false;
        this.miniToggleVisible = false;

        const chartContainer = this.miniPreviewEl.querySelector('.mini-chart-content');
        if (typeof echarts !== 'undefined') {
            this.miniChart = echarts.init(chartContainer);
        }

        const closeBtn = this.miniPreviewEl.querySelector('.mini-chart-close');
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.miniPreviewDismissed = true;
            this.hideMiniPreview();
            this.showMiniToggle();
        });

        this.miniToggleBtn.addEventListener('click', () => {
            this.miniPreviewDismissed = false;
            this.hideMiniToggle();
            if (this.preview && this.preview.chart) {
                this.showMiniPreview();
            }
        });

        const sizeButtons = this.miniPreviewEl.querySelectorAll('.mini-size-btn');
        sizeButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.setMiniPreviewSize(btn.dataset.size);
            });
        });

        const opacitySlider = this.miniPreviewEl.querySelector('.mini-opacity-input');
        if (opacitySlider) {
            opacitySlider.addEventListener('input', (e) => {
                this.miniPreviewOpacity = parseInt(e.target.value, 10) / 100;
                this.miniPreviewEl.style.opacity = this.miniPreviewOpacity;
                localStorage.setItem('miniPreviewOpacity', this.miniPreviewOpacity);
            });
        }

        chartContainer.addEventListener('click', () => {
            previewCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        this.previewObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.hideMiniPreview();
                        this.hideMiniToggle();
                    } else {
                        if (this.preview && this.preview.chart) {
                            if (this.miniPreviewDismissed) {
                                this.showMiniToggle();
                            } else {
                                this.showMiniPreview();
                            }
                        }
                    }
                });
            },
            {
                threshold: 0.5,
                rootMargin: '-60px 0px 0px 0px'
            }
        );

        this.previewObserver.observe(previewCard);

        this.preview.onRender(() => {
            if (this.miniPreviewVisible) {
                this.updateMiniChart();
            }
        });
    }

    /**
     * Set mini preview size
     */
    setMiniPreviewSize(size) {
        if (!this.miniPreviewEl) return;

        this.miniPreviewSize = size;
        this.miniPreviewEl.classList.add('resizing');
        this.miniPreviewEl.classList.remove('size-compact', 'size-expanded');
        this.miniPreviewEl.classList.add('size-' + size);

        const sizeButtons = this.miniPreviewEl.querySelectorAll('.mini-size-btn');
        sizeButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.size === size);
        });

        if (this.miniChart) {
            const self = this;
            setTimeout(function() {
                self.miniChart.resize();
                self.updateMiniChart();
                self.miniPreviewEl.classList.remove('resizing');
            }, 300);
        } else {
            this.miniPreviewEl.classList.remove('resizing');
        }
    }

    /**
     * Show mini chart preview
     */
    showMiniPreview() {
        if (this.miniPreviewVisible || !this.miniPreviewEl || !this.preview || !this.preview.chart) return;

        this.miniPreviewVisible = true;
        this.miniPreviewEl.classList.add('visible');
        this.updateMiniChart();
    }

    /**
     * Hide mini chart preview
     */
    hideMiniPreview() {
        if (!this.miniPreviewVisible || !this.miniPreviewEl) return;

        this.miniPreviewVisible = false;
        this.miniPreviewEl.classList.remove('visible');
    }

    /**
     * Show mini toggle button
     */
    showMiniToggle() {
        if (this.miniToggleVisible || !this.miniToggleBtn) return;

        this.miniToggleVisible = true;
        this.miniToggleBtn.classList.add('visible');
    }

    /**
     * Hide mini toggle button
     */
    hideMiniToggle() {
        if (!this.miniToggleVisible || !this.miniToggleBtn) return;

        this.miniToggleVisible = false;
        this.miniToggleBtn.classList.remove('visible');
    }

    /**
     * Update mini chart with current preview data
     */
    updateMiniChart() {
        if (!this.preview || !this.preview.chart || !this.miniChart) return;

        const mainOption = this.preview.chart.getOption();
        if (!mainOption) return;

        const miniOption = JSON.parse(JSON.stringify(mainOption));
        miniOption.animation = false;

        if (miniOption.tooltip) {
            if (Array.isArray(miniOption.tooltip)) {
                miniOption.tooltip.forEach(function(t) {
                    t.show = false;
                });
            } else {
                miniOption.tooltip.show = false;
            }
        }

        this.miniChart.setOption(miniOption, true);
        this.miniChart.resize();
    }

    /**
     * Get chart type icon
     */
    getChartTypeIcon() {
        const iconMap = {
            bar: 'fa-chart-bar',
            line: 'fa-chart-line',
            pie: 'fa-chart-pie'
        };
        return iconMap[this.graphType] || 'fa-chart-bar';
    }

    /**
     * Update mini toggle button icon
     */
    updateMiniToggleIcon() {
        if (this.miniToggleBtn) {
            const icon = this.miniToggleBtn.querySelector('i');
            if (icon) {
                icon.className = 'fas ' + this.getChartTypeIcon();
            }
        }
    }
}
