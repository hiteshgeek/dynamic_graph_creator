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
import PlaceholderSettings from './PlaceholderSettings.js';
import FilterUtils from './FilterUtils.js';

// Use autosize from CDN (global)
const autosize = window.autosize;

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
        this.placeholderSettings = null;

        // State
        this.columns = [];
        this.isLoading = false;
        this.activeSidebarTab = 'config'; // Default to config tab

        // Unsaved changes tracking
        this.hasUnsavedChanges = false;

        // Status tracking for errors/warnings
        this.queryError = null;
        this.placeholderWarnings = [];
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
        this.initPlaceholderSettings();
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

        // Initialize keyboard shortcuts
        this.initKeyboardShortcuts();

        // Show initial status indicators
        this.updateStatusIndicators();

        // Initialize mini chart preview (shows when main chart scrolls out of view)
        this.initMiniPreview();
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
                onChange: () => {
                    this.checkForChanges();
                    this.updatePlaceholderSettings();
                },
                getFilterValues: () => this.getSidebarFilterValues(),
                getPlaceholderSettings: () => this.getPlaceholderSettingsForQuery()
            });
        }
    }

    /**
     * Get placeholder settings for query execution
     */
    getPlaceholderSettingsForQuery() {
        if (!this.placeholderSettings) return {};
        return this.placeholderSettings.getSettings();
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
     * Initialize placeholder settings component
     */
    initPlaceholderSettings() {
        const settingsContainer = this.container.querySelector('.graph-main');
        if (settingsContainer) {
            this.placeholderSettings = new PlaceholderSettings(settingsContainer, {
                onChange: () => this.checkForChanges(),
                getMatchedFilters: () => this.getMatchedFilters()
            });
        }
    }

    /**
     * Get matched filters for placeholders in query
     * Returns object mapping placeholder keys to filter info
     * Only includes filters that are currently selected/active for this graph
     */
    getMatchedFilters() {
        const matchedFilters = {};
        const filtersContainer = this.container.querySelector('#graph-filters');
        if (!filtersContainer) return matchedFilters;

        const filterItems = filtersContainer.querySelectorAll('.filter-input-item');
        filterItems.forEach(item => {
            const filterKey = item.dataset.filterKey;
            const filterLabel = item.querySelector('.filter-input-label')?.textContent || filterKey;
            // Only include filter if it's in the selectedFilters list (active for this graph)
            if (filterKey && this.selectedFilters && this.selectedFilters.includes(filterKey)) {
                matchedFilters['::' + filterKey] = {
                    filter_key: filterKey,
                    filter_label: filterLabel
                };
            }
        });

        return matchedFilters;
    }

    /**
     * Update placeholder settings when query changes
     */
    updatePlaceholderSettings() {
        if (!this.placeholderSettings || !this.queryBuilder) return;

        const placeholders = this.queryBuilder.getPlaceholders();
        const matchedFilters = this.getMatchedFilters();
        this.placeholderSettings.setPlaceholders(placeholders, matchedFilters);

        // Update warnings for missing filters
        this.updatePlaceholderWarnings(placeholders, matchedFilters);
    }

    /**
     * Update placeholder warnings list
     */
    updatePlaceholderWarnings(placeholders, matchedFilters) {
        this.placeholderWarnings = [];
        placeholders.forEach(placeholder => {
            if (!matchedFilters[placeholder]) {
                this.placeholderWarnings.push(placeholder);
            }
        });
        this.updateStatusIndicators();
    }

    /**
     * Set query error message
     */
    setQueryError(error) {
        this.queryError = error;
        this.updateStatusIndicators();
    }

    /**
     * Clear query error
     */
    clearQueryError() {
        this.queryError = null;
        this.updateStatusIndicators();
    }

    /**
     * Update status indicators in page header (errors, warnings, and save status)
     */
    updateStatusIndicators() {
        // Status indicators are now in page header
        const statusContainer = document.querySelector('.page-header-right .status-indicators');
        if (!statusContainer) return;

        let html = '';

        // Save indicator (always shown)
        if (this.hasUnsavedChanges) {
            html += `<span class="save-indicator unsaved"><i class="fas fa-circle"></i> Unsaved</span>`;
        } else {
            html += `<span class="save-indicator saved"><i class="fas fa-check"></i> Saved</span>`;
        }

        // Error indicator
        if (this.queryError) {
            html += `<span class="status-box status-error" title="${this.escapeHtml(this.queryError)}">
                <i class="fas fa-times-circle"></i>
            </span>`;
        }

        // Warning indicator for missing filters
        if (this.placeholderWarnings.length > 0) {
            const warningText = `Filter not found: ${this.placeholderWarnings.join(', ')}`;
            html += `<span class="status-box status-warning" title="${this.escapeHtml(warningText)}">
                <i class="fas fa-exclamation-triangle"></i>
            </span>`;
        }

        statusContainer.innerHTML = html;

        // Initialize tooltips (Bootstrap)
        const tooltipElements = statusContainer.querySelectorAll('[title]');
        tooltipElements.forEach(el => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                new bootstrap.Tooltip(el, { placement: 'bottom' });
            }
        });
    }

    /**
     * Escape HTML for tooltip
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Initialize graph type selector (supports both old and new class names)
     */
    initGraphTypeSelector() {
        // Support both .graph-type-item (old) and .chart-type-item (new single sidebar)
        const typeItems = this.container.querySelectorAll('.graph-type-item, .chart-type-item');

        const selectType = (item) => {
            const type = item.dataset.type;
            this.setGraphType(type);

            // Update active state and aria-checked
            typeItems.forEach(i => {
                i.classList.remove('active');
                i.setAttribute('aria-checked', 'false');
            });
            item.classList.add('active');
            item.setAttribute('aria-checked', 'true');
        };

        typeItems.forEach(item => {
            // Click handler
            item.addEventListener('click', () => selectType(item));

            // Keyboard handler (Enter/Space)
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectType(item);
                }
            });
        });
    }

    /**
     * Initialize save handler
     */
    initSaveHandler() {
        // Save button is now in page header
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
            // Read initial value from input (for edit mode where value is pre-filled)
            this.graphName = nameInput.value || '';

            nameInput.addEventListener('input', (e) => {
                this.graphName = e.target.value;
                this.checkForChanges();
            });
        }

        if (descriptionInput) {
            // Read initial value from input (for edit mode where value is pre-filled)
            this.graphDescription = descriptionInput.value || '';

            // Initialize autosize for description textarea
            autosize(descriptionInput);

            descriptionInput.addEventListener('input', (e) => {
                this.graphDescription = e.target.value;
                this.checkForChanges();
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
        // New sidebar-card header (with title and collapse button)
        const collapseHeader = this.container.querySelector('.sidebar-card-header');

        // Check if sidebar is already collapsed (by inline script) and resize chart accordingly
        const sidebar = this.container.querySelector('.graph-sidebar');
        if (sidebar && sidebar.classList.contains('collapsed')) {
            setTimeout(() => {
                if (this.preview) {
                    this.preview.resize();
                }
            }, 100);
        }

        // Handle new sidebar-card collapse
        if (collapseHeader) {
            collapseHeader.addEventListener('click', () => {
                const card = this.container.querySelector('.sidebar-card');
                const sidebar = this.container.querySelector('.graph-sidebar-left');

                if (card) {
                    card.classList.toggle('collapsed');
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
        }

        // Legacy: Handle old collapsible-header (for backward compatibility)
        const headers = this.container.querySelectorAll('.collapsible-header');
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const panel = header.closest('.collapsible-panel');
                const sidebar = header.closest('.graph-sidebar');

                if (panel) {
                    panel.classList.toggle('collapsed');
                }
                if (sidebar) {
                    sidebar.classList.toggle('collapsed');
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('graphCreatorSidebarCollapsed', isCollapsed ? 'true' : 'false');
                }

                setTimeout(() => {
                    if (this.preview) {
                        this.preview.resize();
                    }
                }, 350);
            });
        });
    }

    /**
     * Expand sidebar if it's collapsed
     */
    expandSidebar() {
        const sidebar = this.container.querySelector('.graph-sidebar-left');
        const card = this.container.querySelector('.sidebar-card');

        if (sidebar && sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed');
            if (card) {
                card.classList.remove('collapsed');
            }
            // Update localStorage
            localStorage.setItem('graphCreatorSidebarCollapsed', 'false');

            // Trigger chart resize after animation
            setTimeout(() => {
                if (this.preview) {
                    this.preview.resize();
                }
            }, 350);
        }
    }

    /**
     * Initialize keyboard shortcuts
     * Note: Graph creator shortcuts are registered globally in common.js
     * to ensure they appear in the shortcuts modal on all pages (grayed out when unavailable)
     */
    initKeyboardShortcuts() {
        // Shortcuts are now registered in common.js registerGlobalShortcuts()
    }

    /**
     * Initialize mini chart preview that shows when main chart is scrolled out of view
     */
    initMiniPreview() {
        const previewCard = this.container.querySelector('.graph-preview-card');
        if (!previewCard || !this.preview) return;

        // Store reference to preview card for scrolling
        this.previewCard = previewCard;

        // Mini preview size state: 'compact', 'expanded'
        this.miniPreviewSize = 'compact';

        // Mini preview opacity (0.5 to 1.0, default 1.0) - load from localStorage
        var savedOpacity = localStorage.getItem('miniPreviewOpacity');
        this.miniPreviewOpacity = savedOpacity ? parseFloat(savedOpacity) : 1.0;

        // Create mini preview container
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

        // Create minimized toggle button (shown when mini preview is dismissed)
        this.miniToggleBtn = document.createElement('button');
        this.miniToggleBtn.type = 'button';
        this.miniToggleBtn.className = 'mini-chart-toggle';
        this.miniToggleBtn.innerHTML = '<i class="fas ' + this.getChartTypeIcon() + '"></i>';
        this.miniToggleBtn.title = 'Show mini preview';
        document.body.appendChild(this.miniToggleBtn);

        // Mini chart instance (ECharts)
        this.miniChart = null;
        this.miniPreviewVisible = false;
        this.miniPreviewDismissed = false;
        this.miniToggleVisible = false;

        // Initialize ECharts on mini chart container
        const chartContainer = this.miniPreviewEl.querySelector('.mini-chart-content');
        if (typeof echarts !== 'undefined') {
            this.miniChart = echarts.init(chartContainer);
        }

        // Close/minimize button - hides preview and shows toggle button
        const closeBtn = this.miniPreviewEl.querySelector('.mini-chart-close');
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.miniPreviewDismissed = true;
            this.hideMiniPreview();
            this.showMiniToggle();
        });

        // Toggle button click - restore mini preview
        this.miniToggleBtn.addEventListener('click', () => {
            this.miniPreviewDismissed = false;
            this.hideMiniToggle();
            if (this.preview && this.preview.chart) {
                this.showMiniPreview();
            }
        });

        // Size buttons
        const sizeButtons = this.miniPreviewEl.querySelectorAll('.mini-size-btn');
        sizeButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const size = btn.dataset.size;
                this.setMiniPreviewSize(size);
            });
        });

        // Opacity slider - update live as user drags and save to localStorage
        const opacitySlider = this.miniPreviewEl.querySelector('.mini-opacity-input');
        if (opacitySlider) {
            opacitySlider.addEventListener('input', (e) => {
                this.miniPreviewOpacity = parseInt(e.target.value, 10) / 100;
                this.miniPreviewEl.style.opacity = this.miniPreviewOpacity;
                localStorage.setItem('miniPreviewOpacity', this.miniPreviewOpacity);
            });
        }

        // Click on mini chart to scroll to main chart
        chartContainer.addEventListener('click', () => {
            previewCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        // Set up intersection observer to detect when main chart is out of view
        this.previewObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Main chart is visible, hide mini preview and toggle
                    this.hideMiniPreview();
                    this.hideMiniToggle();
                } else {
                    // Main chart is out of view
                    if (this.preview && this.preview.chart) {
                        if (this.miniPreviewDismissed) {
                            // Show toggle button if dismissed
                            this.showMiniToggle();
                        } else {
                            // Show mini preview if not dismissed
                            this.showMiniPreview();
                        }
                    }
                }
            });
        }, {
            threshold: 0.9, // Show mini map when 10% or more of main chart is not visible
            rootMargin: '-60px 0px 0px 0px' // Account for header
        });

        this.previewObserver.observe(previewCard);

        // Register callback to update mini chart when main chart renders
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

        // Show loading overlay before resize
        this.miniPreviewEl.classList.add('resizing');

        // Remove all size classes and add the new one
        this.miniPreviewEl.classList.remove('size-compact', 'size-expanded');
        this.miniPreviewEl.classList.add('size-' + size);

        // Update active button
        const sizeButtons = this.miniPreviewEl.querySelectorAll('.mini-size-btn');
        sizeButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.size === size);
        });

        // Resize ECharts after CSS transition completes (transition is 250ms)
        if (this.miniChart) {
            var self = this;
            setTimeout(function() {
                self.miniChart.resize();
                // Re-apply the chart option to ensure proper rendering
                self.updateMiniChart();
                // Remove loading overlay after chart is updated
                self.miniPreviewEl.classList.remove('resizing');
            }, 300);
        } else {
            // No chart, just remove loading state
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

        // Update mini chart with current data
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

        // Get current option from main chart (deep clone)
        const mainOption = this.preview.chart.getOption();
        if (!mainOption) return;

        // Deep clone the entire option - keep everything exactly as main chart
        const miniOption = JSON.parse(JSON.stringify(mainOption));

        // Only disable animation and tooltip interaction
        miniOption.animation = false;
        if (miniOption.tooltip) {
            if (Array.isArray(miniOption.tooltip)) {
                miniOption.tooltip.forEach(function(t) { t.show = false; });
            } else {
                miniOption.tooltip.show = false;
            }
        }

        // Set option on mini chart - exact same as main chart
        this.miniChart.setOption(miniOption, true);
        this.miniChart.resize();
    }

    /**
     * Get Font Awesome icon class for current chart type
     */
    getChartTypeIcon() {
        var iconMap = {
            'bar': 'fa-chart-bar',
            'line': 'fa-chart-line',
            'pie': 'fa-chart-pie'
        };
        return iconMap[this.graphType] || 'fa-chart-bar';
    }

    /**
     * Update mini toggle button icon to match current chart type
     */
    updateMiniToggleIcon() {
        if (this.miniToggleBtn) {
            var icon = this.miniToggleBtn.querySelector('i');
            if (icon) {
                icon.className = 'fas ' + this.getChartTypeIcon();
            }
        }
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

        // Storage key for this graph's selected filters
        const storageKey = this.graphId ? `graphFilters_${this.graphId}` : null;

        // Load saved filters from localStorage
        if (storageKey) {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                try {
                    this.selectedFilters = JSON.parse(saved);
                    // Check the saved checkboxes
                    checkboxes.forEach(cb => {
                        if (this.selectedFilters.includes(cb.value)) {
                            cb.checked = true;
                        }
                    });
                    // Update count display
                    if (countDisplay) {
                        countDisplay.textContent = `${this.selectedFilters.length} selected`;
                    }
                    // Auto-show active filters if we have saved selections
                    if (this.selectedFilters.length > 0) {
                        this.applySelectedFilters(selectorView, activeView);
                    }
                } catch (e) {
                    // Invalid JSON, ignore
                }
            }
        }

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

            // Save to localStorage
            if (storageKey) {
                localStorage.setItem(storageKey, JSON.stringify(this.selectedFilters));
            }

            this.applySelectedFilters(selectorView, activeView);
        });

        // Handle "Change" button to go back to selector
        if (changeBtn) {
            changeBtn.addEventListener('click', () => {
                // Show selector view
                selectorView.style.display = 'flex';
                activeView.style.display = 'none';

                // Update button state based on currently checked filters
                const selected = Array.from(checkboxes).filter(cb => cb.checked);
                useBtn.disabled = selected.length === 0;
            });
        }
    }

    /**
     * Apply selected filters - show active view with selected filter inputs
     */
    applySelectedFilters(selectorView, activeView) {
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

        // Update placeholder settings to reflect new filter selection
        this.updatePlaceholderSettings();
    }

    /**
     * Initialize sidebar filters (multi-select dropdowns, datepickers, etc.)
     */
    initSidebarFilters() {
        const filtersContainer = this.container.querySelector('#graph-filters');
        if (!filtersContainer) return;

        // Initialize date pickers
        if (typeof DatePickerInit !== 'undefined') {
            DatePickerInit.init(filtersContainer);
        }

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
                    placeholder.textContent = `${selected.length} SELECTED`;
                    placeholder.classList.add('has-selection');
                }
            };

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

            // Select All button (only selects visible/filtered items)
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

            // Select None button (only deselects visible/filtered items)
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

            // Update placeholder on init to reflect any pre-selected options (from is_selected)
            updatePlaceholder();
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

        // Update mini toggle button icon
        this.updateMiniToggleIcon();

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

        // Clear any previous error
        this.clearQueryError();

        Toast.success(`Query valid. Found ${columns.length} columns.`);

        // Automatically update preview after successful query test
        this.updatePreview();
    }

    /**
     * Handle query test error
     */
    onQueryError(error) {
        this.columns = [];

        if (this.dataMapper) {
            this.dataMapper.setColumns([]);
        }

        // Set error for status indicator
        this.setQueryError(error);

        Toast.error(error);
    }

    /**
     * Handle filter changes
     */
    onFiltersChanged() {
        // Filters changed, might need to retest query
    }

    /**
     * Get current filter values from sidebar inputs (only from selected/visible filters)
     */
    getSidebarFilterValues() {
        // Try to find filters container - check both within container and globally
        let filtersContainer = this.container.querySelector('#graph-filters');
        if (!filtersContainer) {
            filtersContainer = document.getElementById('graph-filters');
        }

        return FilterUtils.getValues(filtersContainer, { visibleOnly: true });
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

        // If mapping is incomplete, show dummy data
        if (!this.validateMapping(mapping)) {
            this.preview.setConfig(config);
            this.preview.setMapping(mapping);
            this.preview.showDummyData(this.graphType);
            return;
        }

        const query = this.queryBuilder ? this.queryBuilder.getQuery() : '';

        // Use sidebar filter values (includes pre-selected options from is_selected)
        const filterValues = this.getSidebarFilterValues();
        const placeholderSettings = this.getPlaceholderSettingsForQuery();

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
                this.graphDescription = graph.description || '';
                this.graphType = graph.graph_type;

                // Update name input
                const nameInput = this.container.querySelector('.graph-name-input');
                if (nameInput) nameInput.value = graph.name;

                // Update description input
                const descriptionInput = this.container.querySelector('.graph-description-input');
                if (descriptionInput) {
                    descriptionInput.value = this.graphDescription;
                    // Trigger autosize update
                    autosize.update(descriptionInput);
                }

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

                    // Restore placeholder settings
                    if (this.placeholderSettings && config.placeholderSettings) {
                        this.placeholderSettings.setSettings(config.placeholderSettings);
                    }

                    // Set config panel values
                    if (this.configPanel) {
                        this.configPanel.setConfig(config);
                    }
                }

                // Set filters first (they may be needed for query testing)
                if (this.filterManager && graph.filters) {
                    this.filterManager.setFilters(graph.filters);
                }

                // Set mapping and test query
                if (this.dataMapper && graph.data_mapping) {
                    const mapping = JSON.parse(graph.data_mapping);
                    // Set mapping without triggering onChange (we'll update preview after testQuery)
                    this.dataMapper.setMapping(mapping, false);

                    // Test query to get columns - this will call onQueryTest which now
                    // calls updatePreview(), and since mapping is already set, it should work
                    if (graph.query) {
                        await this.queryBuilder.testQuery();
                    }
                }

                // Update placeholder settings table after query is set
                this.updatePlaceholderSettings();

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
            // Expand sidebar if collapsed so user can see the name input
            this.expandSidebar();
            if (nameInput) {
                nameInput.classList.add('error');
                // Focus after a short delay to allow sidebar animation
                setTimeout(() => nameInput.focus(), 100);
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
            // Include activeSidebarTab and placeholderSettings in config
            const config = this.configPanel ? this.configPanel.getConfig() : {};
            config.activeSidebarTab = this.activeSidebarTab;
            config.placeholderSettings = this.placeholderSettings ? this.placeholderSettings.getSettings() : {};

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
            description: this.graphDescription,
            graphType: this.graphType,
            query: this.queryBuilder ? this.queryBuilder.getQuery() : '',
            mapping: this.dataMapper ? JSON.stringify(this.dataMapper.getMapping()) : '{}',
            config: this.configPanel ? JSON.stringify(this.configPanel.getConfig()) : '{}',
            filters: this.filterManager ? JSON.stringify(this.filterManager.getFilters()) : '[]',
            placeholderSettings: this.placeholderSettings ? JSON.stringify(this.placeholderSettings.getSettings()) : '{}'
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
            currentState.description !== this.savedState.description ||
            currentState.graphType !== this.savedState.graphType ||
            currentState.query !== this.savedState.query ||
            currentState.mapping !== this.savedState.mapping ||
            currentState.config !== this.savedState.config ||
            currentState.filters !== this.savedState.filters ||
            currentState.placeholderSettings !== this.savedState.placeholderSettings;

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
        // Delegate to updateStatusIndicators which handles all status displays
        this.updateStatusIndicators();
    }

    /**
     * Mark state as changed (call from components when they change)
     */
    markAsChanged() {
        this.checkForChanges();
    }
}
