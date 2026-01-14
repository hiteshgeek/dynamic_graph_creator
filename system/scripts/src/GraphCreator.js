/**
 * GraphCreator - Main orchestrator class
 * Coordinates all components of the graph creator
 */

import GraphPreview from './GraphPreview.js';
import QueryBuilder from './QueryBuilder.js';
import DataMapper from './DataMapper.js';
import FilterManager from './FilterManager.js';
import ConfigPanel from './ConfigPanel.js';

export default class GraphCreator {
    constructor(container, options = {}) {
        this.container = container;
        this.graphId = options.graphId || null;
        this.graphType = 'bar';
        this.graphName = '';
        this.graphDescription = '';

        // Component instances
        this.preview = null;
        this.queryBuilder = null;
        this.dataMapper = null;
        this.filterManager = null;
        this.configPanel = null;

        // State
        this.columns = [];
        this.isLoading = false;
    }

    /**
     * Initialize all components
     */
    init() {
        this.initPreview();
        this.initQueryBuilder();
        this.initDataMapper();
        this.initFilterManager();
        this.initConfigPanel();
        this.initGraphTypeSelector();
        this.initSaveHandler();

        // Load existing graph if editing
        if (this.graphId) {
            this.loadGraph(this.graphId);
        } else {
            // Show dummy data for new graph
            this.preview.showDummyData(this.graphType);
        }
    }

    /**
     * Initialize graph preview component
     */
    initPreview() {
        const previewContainer = this.container.querySelector('.graph-preview-container');
        if (previewContainer) {
            this.preview = new GraphPreview(previewContainer);
        }
    }

    /**
     * Initialize query builder component
     */
    initQueryBuilder() {
        const queryContainer = this.container.querySelector('.query-builder');
        if (queryContainer) {
            this.queryBuilder = new QueryBuilder(queryContainer, {
                onTest: (columns) => this.onQueryTest(columns),
                onError: (error) => this.onQueryError(error)
            });
        }
    }

    /**
     * Initialize data mapper component
     */
    initDataMapper() {
        const mapperContainer = this.container.querySelector('.data-mapper');
        if (mapperContainer) {
            this.dataMapper = new DataMapper(mapperContainer, {
                onChange: () => this.updatePreview()
            });
            this.dataMapper.setGraphType(this.graphType);
        }
    }

    /**
     * Initialize filter manager component
     */
    initFilterManager() {
        const filterContainer = this.container.querySelector('.filter-manager');
        if (filterContainer) {
            this.filterManager = new FilterManager(filterContainer, {
                entityType: 'graph',
                entityId: this.graphId,
                onChange: () => this.onFiltersChanged()
            });
            this.filterManager.init();
        }
    }

    /**
     * Initialize config panel component
     */
    initConfigPanel() {
        const configContainer = this.container.querySelector('.graph-config-panel');
        if (configContainer) {
            this.configPanel = new ConfigPanel(configContainer, {
                onChange: () => this.updatePreview()
            });
            this.configPanel.setGraphType(this.graphType);
        }
    }

    /**
     * Initialize graph type selector
     */
    initGraphTypeSelector() {
        const typeItems = this.container.querySelectorAll('.graph-type-item');
        typeItems.forEach(item => {
            item.addEventListener('click', () => {
                const type = item.dataset.type;
                this.setGraphType(type);

                // Update active state
                typeItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
            });
        });
    }

    /**
     * Initialize save handler
     */
    initSaveHandler() {
        const saveBtn = this.container.querySelector('.save-graph-btn');
        const nameInput = this.container.querySelector('.graph-name-input');

        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.save());
        }

        if (nameInput) {
            nameInput.addEventListener('input', (e) => {
                this.graphName = e.target.value;
            });
        }
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
            if (this.columns.length === 0) {
                this.preview.showDummyData(type);
            } else {
                this.updatePreview();
            }
        }
    }

    /**
     * Handle query test success
     */
    onQueryTest(columns) {
        this.columns = columns;

        if (this.dataMapper) {
            this.dataMapper.setColumns(columns);
        }

        Toast.success(`Query valid. Found ${columns.length} columns.`);
    }

    /**
     * Handle query test error
     */
    onQueryError(error) {
        this.columns = [];

        if (this.dataMapper) {
            this.dataMapper.setColumns([]);
        }

        Toast.error(error);
    }

    /**
     * Handle filter changes
     */
    onFiltersChanged() {
        // Filters changed, might need to retest query
    }

    /**
     * Update preview with current data
     */
    async updatePreview() {
        if (!this.preview) return;

        const config = this.configPanel ? this.configPanel.getConfig() : {};
        const mapping = this.dataMapper ? this.dataMapper.getMapping() : {};

        // If no columns yet (dummy data mode), just update config and re-render
        if (this.columns.length === 0) {
            this.preview.setConfig(config);
            this.preview.setMapping(mapping);
            this.preview.showDummyData(this.graphType);
            return;
        }

        const query = this.queryBuilder ? this.queryBuilder.getQuery() : '';
        const filters = this.filterManager ? this.filterManager.getFilters() : [];

        // Build default filter values for preview
        const filterValues = {};
        filters.forEach(f => {
            if (f.default_value) {
                filterValues[f.filter_key] = f.default_value;
            }
        });

        try {
            const result = await Ajax.post('preview_graph', {
                query: query,
                mapping: mapping,
                config: config,
                graph_type: this.graphType,
                filters: filterValues
            });

            if (result.success && result.data) {
                this.preview.setData(result.data.chartData);
                this.preview.setConfig(config);
                this.preview.setMapping(mapping);
                this.preview.render();
            }
        } catch (error) {
            console.error('Preview update failed:', error);
        }
    }

    /**
     * Load existing graph for editing
     */
    async loadGraph(graphId) {
        Loading.show('Loading graph...');

        try {
            const result = await Ajax.post('load_graph', { id: graphId });

            if (result.success && result.data) {
                const graph = result.data;

                // Set graph properties
                this.graphName = graph.name;
                this.graphType = graph.graph_type;

                // Update name input
                const nameInput = this.container.querySelector('.graph-name-input');
                if (nameInput) nameInput.value = graph.name;

                // Update type selector
                const typeItems = this.container.querySelectorAll('.graph-type-item');
                typeItems.forEach(item => {
                    item.classList.toggle('active', item.dataset.type === graph.graph_type);
                });

                // Set graph type (updates components)
                this.setGraphType(graph.graph_type);

                // Set query
                if (this.queryBuilder && graph.query) {
                    this.queryBuilder.setQuery(graph.query);
                }

                // Set config
                if (this.configPanel && graph.config) {
                    this.configPanel.setConfig(JSON.parse(graph.config));
                }

                // Set mapping
                if (this.dataMapper && graph.data_mapping) {
                    const mapping = JSON.parse(graph.data_mapping);
                    // First test query to get columns
                    if (graph.query) {
                        await this.queryBuilder.testQuery();
                    }
                    this.dataMapper.setMapping(mapping);
                }

                // Set filters
                if (this.filterManager && graph.filters) {
                    this.filterManager.setFilters(graph.filters);
                }

                Toast.success('Graph loaded successfully');
            } else {
                Toast.error(result.message || 'Failed to load graph');
            }
        } catch (error) {
            Toast.error('Failed to load graph');
            console.error(error);
        } finally {
            Loading.hide();
        }
    }

    /**
     * Save graph
     */
    async save() {
        // Validate
        if (!this.graphName.trim()) {
            Toast.error('Please enter a graph name');
            return;
        }

        const query = this.queryBuilder ? this.queryBuilder.getQuery() : '';
        if (!query.trim()) {
            Toast.error('Please enter a SQL query');
            return;
        }

        const mapping = this.dataMapper ? this.dataMapper.getMapping() : {};
        if (!this.validateMapping(mapping)) {
            Toast.error('Please map the required columns');
            return;
        }

        Loading.show('Saving graph...');

        try {
            const data = {
                id: this.graphId,
                name: this.graphName,
                description: this.graphDescription,
                graph_type: this.graphType,
                query: query,
                data_mapping: mapping,
                config: this.configPanel ? this.configPanel.getConfig() : {},
                filters: this.filterManager ? this.filterManager.getFilters() : []
            };

            const result = await Ajax.post('save_graph', data);

            if (result.success) {
                Toast.success(result.message);

                // Redirect to list or update URL
                if (!this.graphId && result.data && result.data.id) {
                    window.location.href = `?urlq=graph/edit/${result.data.id}`;
                }
            } else {
                Toast.error(result.message);
            }
        } catch (error) {
            Toast.error('Failed to save graph');
            console.error(error);
        } finally {
            Loading.hide();
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
}
