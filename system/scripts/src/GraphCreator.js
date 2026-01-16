/**
 * GraphCreator - Main orchestrator class
 * Coordinates all components of the graph creator
 */

import GraphPreview from './GraphPreview.js';
import GraphExporter from './GraphExporter.js';
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
        this.exporter = null;
        this.queryBuilder = null;
        this.dataMapper = null;
        this.filterManager = null;
        this.configPanel = null;

        // State
        this.columns = [];
        this.isLoading = false;
        this.activeSidebarTab = 'config'; // Default to config tab

        // Unsaved changes tracking
        this.hasUnsavedChanges = false;
        this.savedState = null;
    }

    /**
     * Initialize all components
     */
    init() {
        this.initPreview();
        this.initExporter();
        this.initQueryBuilder();
        this.initDataMapper();
        this.initFilterManager();
        this.initConfigPanel();
        this.initGraphTypeSelector();
        this.initSaveHandler();
        this.initTabs();
        this.initSidebarTabs();
        this.initCollapsiblePanels();
        this.initFilterSelector();
        this.initSidebarFilters();

        // Load existing graph if editing
        if (this.graphId) {
            this.loadGraph(this.graphId);
        } else {
            // Show dummy data for new graph
            this.preview.showDummyData(this.graphType);
            // Capture initial state for new graph
            this.captureState();
        }

        // Initialize change tracking
        this.initChangeTracking();
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
     * Initialize graph exporter component
     */
    initExporter() {
        this.exporter = new GraphExporter({
            filename: 'chart-preview'
        });

        const exportBtn = this.container.querySelector('#export-chart');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                if (this.preview && this.preview.chart) {
                    // Use graph name if available, otherwise default
                    this.exporter.setFilename(this.graphName || 'chart-preview');
                    this.exporter.setChart(this.preview.chart);
                    this.exporter.exportImage();
                }
            });
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
                onError: (error) => this.onQueryError(error),
                onChange: () => this.checkForChanges()
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
                onChange: () => {
                    this.updatePreview();
                    this.checkForChanges();
                }
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
                onChange: () => {
                    this.onFiltersChanged();
                    this.checkForChanges();
                }
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
                onChange: () => {
                    this.updatePreview();
                    this.checkForChanges();
                }
            });
            this.configPanel.setGraphType(this.graphType);
        }
    }

    /**
     * Initialize graph type selector (supports both old and new class names)
     */
    initGraphTypeSelector() {
        // Support both .graph-type-item (old) and .chart-type-item (new single sidebar)
        const typeItems = this.container.querySelectorAll('.graph-type-item, .chart-type-item');
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
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.save();
            });
        }

        if (nameInput) {
            // Read initial value from input (for edit mode where value is pre-filled)
            this.graphName = nameInput.value || '';

            nameInput.addEventListener('input', (e) => {
                this.graphName = e.target.value;
            });
        }
    }

    /**
     * Initialize query/mapping tabs
     */
    initTabs() {
        const tabs = this.container.querySelectorAll('.query-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active from all tabs
                tabs.forEach(t => t.classList.remove('active'));

                // Remove active from all tab contents
                this.container.querySelectorAll('.query-tab-content').forEach(c => {
                    c.classList.remove('active');
                });

                // Set active on clicked tab
                tab.classList.add('active');

                // Show corresponding content
                const targetId = 'tab-' + tab.dataset.tab;
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }

    /**
     * Initialize sidebar tabs (Chart/Filters)
     */
    initSidebarTabs() {
        const tabs = this.container.querySelectorAll('.sidebar-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                this.setActiveSidebarTab(tab.dataset.tab);
            });
        });

        // Restore saved tab from localStorage
        const savedTab = localStorage.getItem('graphCreatorSidebarTab');
        if (savedTab && (savedTab === 'config' || savedTab === 'filters')) {
            this.setActiveSidebarTab(savedTab);
        }
    }

    /**
     * Set active sidebar tab
     */
    setActiveSidebarTab(tabName) {
        this.activeSidebarTab = tabName;

        // Save to localStorage for persistence across page refreshes
        localStorage.setItem('graphCreatorSidebarTab', tabName);

        const tabs = this.container.querySelectorAll('.sidebar-tab');

        // Remove active from all sidebar tabs
        tabs.forEach(t => t.classList.remove('active'));

        // Remove active from all sidebar tab contents
        this.container.querySelectorAll('.sidebar-tab-content').forEach(c => {
            c.classList.remove('active');
        });

        // Set active on target tab
        const targetTab = this.container.querySelector(`.sidebar-tab[data-tab="${tabName}"]`);
        if (targetTab) {
            targetTab.classList.add('active');
        }

        // Show corresponding content
        const targetId = 'sidebar-tab-' + tabName;
        const targetContent = document.getElementById(targetId);
        if (targetContent) {
            targetContent.classList.add('active');
        }
    }

    /**
     * Initialize collapsible panels
     */
    initCollapsiblePanels() {
        const headers = this.container.querySelectorAll('.collapsible-header');

        // Check if sidebar is already collapsed (by inline script) and resize chart accordingly
        const sidebar = this.container.querySelector('.graph-sidebar');
        if (sidebar && sidebar.classList.contains('collapsed')) {
            setTimeout(() => {
                if (this.preview) {
                    this.preview.resize();
                }
            }, 100);
        }

        headers.forEach(header => {
            header.addEventListener('click', () => {
                const panel = header.closest('.collapsible-panel');
                const sidebar = header.closest('.graph-sidebar');

                if (panel) {
                    panel.classList.toggle('collapsed');
                }
                if (sidebar) {
                    sidebar.classList.toggle('collapsed');
                    // Save collapse state to localStorage
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('graphCreatorSidebarCollapsed', isCollapsed ? 'true' : 'false');
                }

                // Trigger chart resize after animation
                setTimeout(() => {
                    if (this.preview) {
                        this.preview.resize();
                    }
                }, 350);
            });
        });
    }

    /**
     * Initialize filter selector (choose which filters to use)
     */
    initFilterSelector() {
        const selectorView = this.container.querySelector('#filter-selector-view');
        const activeView = this.container.querySelector('#filter-active-view');
        const useBtn = this.container.querySelector('#filter-use-btn');
        const changeBtn = this.container.querySelector('#filter-change-btn');
        const countDisplay = this.container.querySelector('.filter-selector-count');
        const checkboxes = this.container.querySelectorAll('.filter-selector-checkbox');

        if (!selectorView || !activeView || !useBtn) return;

        // Track selected filters
        this.selectedFilters = [];

        // Update count and button state when checkboxes change
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const selected = Array.from(checkboxes).filter(cb => cb.checked);
                this.selectedFilters = selected.map(cb => cb.value);

                // Update count display
                if (countDisplay) {
                    countDisplay.textContent = `${this.selectedFilters.length} selected`;
                }

                // Enable/disable use button
                useBtn.disabled = this.selectedFilters.length === 0;
            });
        });

        // Handle "Use Selected Filters" button
        useBtn.addEventListener('click', () => {
            if (this.selectedFilters.length === 0) return;

            // Show active filters view
            selectorView.style.display = 'none';
            activeView.style.display = 'flex';

            // Show only selected filter inputs
            const filterItems = this.container.querySelectorAll('#graph-filters .filter-input-item');
            filterItems.forEach(item => {
                const key = item.dataset.filterKey;
                if (this.selectedFilters.includes(key)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Handle "Change" button to go back to selector
        if (changeBtn) {
            changeBtn.addEventListener('click', () => {
                // Show selector view
                selectorView.style.display = 'flex';
                activeView.style.display = 'none';
            });
        }
    }

    /**
     * Initialize sidebar filters (multi-select dropdowns, etc.)
     */
    initSidebarFilters() {
        const filtersContainer = this.container.querySelector('#graph-filters');
        if (!filtersContainer) return;

        // Initialize multi-select dropdowns
        const multiSelectDropdowns = filtersContainer.querySelectorAll('.filter-multiselect-dropdown');
        multiSelectDropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.filter-multiselect-trigger');
            const optionsPanel = dropdown.querySelector('.filter-multiselect-options');
            const placeholder = dropdown.querySelector('.filter-multiselect-placeholder');
            const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');

            if (!trigger || !optionsPanel) return;

            // Toggle dropdown on trigger click
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();

                // Close other open dropdowns
                multiSelectDropdowns.forEach(other => {
                    if (other !== dropdown) {
                        other.querySelector('.filter-multiselect-options')?.classList.remove('open');
                        const icon = other.querySelector('.filter-multiselect-trigger i');
                        if (icon) {
                            icon.classList.remove('fa-chevron-up');
                            icon.classList.add('fa-chevron-down');
                        }
                    }
                });

                optionsPanel.classList.toggle('open');
                const icon = trigger.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                }
            });

            // Update placeholder text when checkboxes change
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const selected = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.nextElementSibling?.textContent || cb.value);

                    if (selected.length === 0) {
                        placeholder.textContent = '-- Select multiple --';
                    } else if (selected.length <= 2) {
                        placeholder.textContent = selected.join(', ');
                    } else {
                        placeholder.textContent = `${selected.length} selected`;
                    }
                });
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.filter-multiselect-dropdown')) {
                multiSelectDropdowns.forEach(dropdown => {
                    dropdown.querySelector('.filter-multiselect-options')?.classList.remove('open');
                    const icon = dropdown.querySelector('.filter-multiselect-trigger i');
                    if (icon) {
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    }
                });
            }
        });

        // Copy filter placeholder to clipboard on click
        const placeholders = filtersContainer.querySelectorAll('.filter-placeholder');
        placeholders.forEach(placeholder => {
            placeholder.addEventListener('click', async (e) => {
                e.stopPropagation();
                const text = placeholder.textContent;
                try {
                    await navigator.clipboard.writeText(text);
                    Toast.success(`Copied "${text}" to clipboard`);
                } catch (err) {
                    // Fallback for older browsers
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    Toast.success(`Copied "${text}" to clipboard`);
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
            if (this.columns.length === 0) {
                this.preview.showDummyData(type);
            } else {
                this.updatePreview();
            }
        }

        // Check for unsaved changes
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

                // Update type selector (supports both old and new class names)
                const typeItems = this.container.querySelectorAll('.graph-type-item, .chart-type-item');
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
                if (graph.config) {
                    const config = JSON.parse(graph.config);

                    // Restore active sidebar tab
                    if (config.activeSidebarTab) {
                        this.setActiveSidebarTab(config.activeSidebarTab);
                    }

                    // Set config panel values
                    if (this.configPanel) {
                        this.configPanel.setConfig(config);
                    }
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

                // Capture initial state after loading
                this.captureState();
                this.setUnsavedChanges(false);
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
        // Clear previous errors
        this.clearErrors();

        const nameInput = this.container.querySelector('.graph-name-input');

        // Validate
        if (!this.graphName.trim()) {
            Toast.error('Please enter a graph name');
            if (nameInput) {
                nameInput.classList.add('error');
                nameInput.focus();
            }
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

        console.log('All validations passed, saving...');
        Loading.show('Saving graph...');

        try {
            // Include activeSidebarTab in config
            const config = this.configPanel ? this.configPanel.getConfig() : {};
            config.activeSidebarTab = this.activeSidebarTab;

            const data = {
                id: this.graphId,
                name: this.graphName,
                description: this.graphDescription,
                graph_type: this.graphType,
                query: query,
                data_mapping: mapping,
                config: config,
                filters: this.filterManager ? this.filterManager.getFilters() : []
            };

            const result = await Ajax.post('save_graph', data);

            if (result.success) {
                Toast.success(result.message);

                // Mark as saved
                this.captureState();
                this.setUnsavedChanges(false);

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

    /**
     * Clear all error states
     */
    clearErrors() {
        const nameInput = this.container.querySelector('.graph-name-input');
        if (nameInput) {
            nameInput.classList.remove('error');
        }
    }

    /**
     * Initialize change tracking
     */
    initChangeTracking() {
        // Track changes on name input
        const nameInput = this.container.querySelector('.graph-name-input');
        if (nameInput) {
            nameInput.addEventListener('input', () => this.checkForChanges());
        }

        // Warn before leaving page with unsaved changes
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });

        // Track link clicks for internal navigation
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (e) => {
                if (this.hasUnsavedChanges && !link.classList.contains('no-unsaved-warning')) {
                    if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    }

    /**
     * Capture current state as the saved state
     */
    captureState() {
        this.savedState = this.getCurrentState();
    }

    /**
     * Get current form state
     */
    getCurrentState() {
        return {
            name: this.graphName,
            graphType: this.graphType,
            query: this.queryBuilder ? this.queryBuilder.getQuery() : '',
            mapping: this.dataMapper ? JSON.stringify(this.dataMapper.getMapping()) : '{}',
            config: this.configPanel ? JSON.stringify(this.configPanel.getConfig()) : '{}',
            filters: this.filterManager ? JSON.stringify(this.filterManager.getFilters()) : '[]'
        };
    }

    /**
     * Check if current state differs from saved state
     */
    checkForChanges() {
        if (!this.savedState) {
            this.setUnsavedChanges(false);
            return;
        }

        const currentState = this.getCurrentState();
        const hasChanges =
            currentState.name !== this.savedState.name ||
            currentState.graphType !== this.savedState.graphType ||
            currentState.query !== this.savedState.query ||
            currentState.mapping !== this.savedState.mapping ||
            currentState.config !== this.savedState.config ||
            currentState.filters !== this.savedState.filters;

        this.setUnsavedChanges(hasChanges);
    }

    /**
     * Set unsaved changes state and update UI
     */
    setUnsavedChanges(hasChanges) {
        this.hasUnsavedChanges = hasChanges;
        this.updateUnsavedIndicator();
    }

    /**
     * Update the unsaved changes indicator in the UI
     */
    updateUnsavedIndicator() {
        let indicator = this.container.querySelector('.save-indicator');

        if (!indicator) {
            indicator = document.createElement('span');
            indicator.className = 'save-indicator';

            // Add before the Cancel button in .save-buttons
            const saveButtons = this.container.querySelector('.save-buttons');
            if (saveButtons && saveButtons.firstChild) {
                saveButtons.insertBefore(indicator, saveButtons.firstChild);
            }
        }

        if (this.hasUnsavedChanges) {
            indicator.className = 'save-indicator unsaved';
            indicator.innerHTML = '<i class="fas fa-circle"></i> Unsaved';
        } else {
            indicator.className = 'save-indicator saved';
            indicator.innerHTML = '<i class="fas fa-check"></i> Saved';
        }
    }

    /**
     * Mark state as changed (call from components when they change)
     */
    markAsChanged() {
        this.checkForChanges();
    }
}
