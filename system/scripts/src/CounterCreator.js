/**
 * CounterCreator - Counter-specific creator class
 * Extends ElementCreator with counter-specific functionality
 */

import ElementCreator from './element/ElementCreator.js';
import CounterPreview from './CounterPreview.js';

const Toast = window.Toast;
const Ajax = window.Ajax;
const Loading = window.Loading;
const autosize = window.autosize;

// Material Design Icons for counter - organized by category
const ICON_GROUPS = {
    'Trends & Analytics': [
        'trending_up', 'trending_down', 'trending_flat', 'insights', 'analytics',
        'bar_chart', 'pie_chart', 'timeline', 'show_chart', 'ssid_chart',
        'assessment', 'leaderboard'
    ],
    'Finance & Money': [
        'attach_money', 'monetization_on', 'payments', 'account_balance_wallet',
        'credit_card', 'receipt', 'request_quote', 'point_of_sale',
        'calculate', 'functions', 'percent', 'numbers'
    ],
    'Shopping & Commerce': [
        'shopping_cart', 'shopping_basket', 'store', 'storefront',
        'inventory', 'inventory_2', 'category', 'local_shipping'
    ],
    'People & Users': [
        'people', 'person', 'groups', 'person_add', 'supervisor_account'
    ],
    'Status & Feedback': [
        'check_circle', 'task_alt', 'verified', 'done_all',
        'star', 'favorite', 'thumb_up', 'grade', 'emoji_events',
        'warning', 'error', 'info', 'help'
    ],
    'Time & Schedule': [
        'schedule', 'event', 'today', 'date_range',
        'speed', 'timer', 'hourglass_empty', 'update'
    ],
    'Communication': [
        'notifications', 'email', 'message', 'chat'
    ],
    'System & Settings': [
        'settings', 'tune', 'build', 'construction',
        'security', 'lock', 'vpn_key', 'admin_panel_settings',
        'public', 'language', 'dns', 'cloud'
    ],
    'Actions': [
        'visibility', 'search', 'filter_list', 'sort',
        'download', 'upload', 'sync', 'refresh',
        'arrow_upward', 'arrow_downward', 'north', 'south',
        'add_circle', 'remove_circle', 'cancel', 'close'
    ]
};

export default class CounterCreator extends ElementCreator {
    constructor(container, options = {}) {
        super(container, {
            ...options,
            elementId: options.counterId || options.elementId || null
        });

        // Counter-specific properties
        this.counterId = this.elementId;
        this.counterName = '';
        this.counterDescription = '';

        // Counter config
        this.config = {
            icon: 'trending_up',
            color: '#4CAF50',
            format: 'number',
            prefix: '',
            suffix: '',
            decimals: 0
        };

        // No data mapper for counter
        this.dataMapper = null;
    }

    /**
     * Element type identifiers
     */
    getElementTypeSlug() {
        return 'counter';
    }

    getElementTypeName() {
        return 'Counter';
    }

    /**
     * Initialize all components
     */
    init() {
        this.initMandatoryFilterValidator();
        this.initPreview();
        this.initQueryBuilder();
        this.initFilterManager();
        this.initPlaceholderSettings();
        this.initTypeSpecificComponents();
        this.initSaveHandler();
        this.initFormValidation();
        this.initTabs();
        this.initSidebarTabs();
        this.initCollapsiblePanels();
        this.initFilterSelector();
        this.initSidebarFilters();
        this.initCategoryChips();
        this.initChangeTracking();
        this.initKeyboardShortcuts();
        this.updateStatusIndicators();

        // Load existing counter if editing
        if (this.counterId) {
            // Show skeleton loading immediately
            if (this.preview) {
                this.preview.showLoading();
            }
            this.loadElement(this.counterId);
        } else {
            this.captureState();
            this.updatePreview();
        }
    }

    /**
     * Initialize counter preview component
     */
    initPreview() {
        const previewContainer = this.container.querySelector('.counter-preview-container');
        if (previewContainer) {
            this.preview = new CounterPreview(previewContainer, {
                icon: this.config.icon,
                color: this.config.color,
                format: this.config.format,
                prefix: this.config.prefix,
                suffix: this.config.suffix,
                decimals: this.config.decimals
            });
        }
    }

    /**
     * Initialize counter-specific components
     */
    initTypeSpecificComponents() {
        this.initIconSelector();
        this.initColorPicker();
        this.initFormatOptions();
        this.initRefreshButton();
    }

    /**
     * Initialize icon selector with grouped icons
     */
    initIconSelector() {
        const iconGrid = document.getElementById('icon-grid');
        const iconSearch = document.getElementById('icon-search');
        const selectedIconDisplay = this.container.querySelector('.selected-icon');
        const selectedIconInput = document.getElementById('selected-icon');

        if (!iconGrid) return;

        // Track expanded groups (all expanded by default)
        this.expandedGroups = new Set(Object.keys(ICON_GROUPS));

        // Render icons grouped by category
        const renderIcons = (filter = '') => {
            const filterLower = filter.toLowerCase();
            let html = '';

            Object.entries(ICON_GROUPS).forEach(([groupName, icons]) => {
                // Filter icons within group
                const filteredIcons = icons.filter(icon =>
                    icon.toLowerCase().includes(filterLower)
                );

                // Skip empty groups when filtering
                if (filter && filteredIcons.length === 0) return;

                const isExpanded = filter || this.expandedGroups.has(groupName);
                const groupId = groupName.replace(/\s+/g, '-').toLowerCase();

                html += `
                    <div class="icon-group" data-group="${groupName}">
                        <button type="button" class="icon-group-header${isExpanded ? ' expanded' : ''}" data-group="${groupName}">
                            <span class="icon-group-title">${groupName}</span>
                            <span class="icon-group-count">${filteredIcons.length}</span>
                            <i class="fas fa-chevron-down icon-group-arrow"></i>
                        </button>
                        <div class="icon-group-content${isExpanded ? ' expanded' : ''}" id="icon-group-${groupId}">
                            ${filteredIcons.map(icon => `
                                <button type="button" class="icon-grid-item${this.config.icon === icon ? ' active' : ''}" data-icon="${icon}">
                                    <span class="material-icons">${icon}</span>
                                    <span class="icon-name">${icon.replace(/_/g, ' ')}</span>
                                </button>
                            `).join('')}
                        </div>
                    </div>
                `;
            });

            iconGrid.innerHTML = html;

            // Add group toggle handlers
            iconGrid.querySelectorAll('.icon-group-header').forEach(header => {
                header.addEventListener('click', (e) => {
                    e.preventDefault();
                    const groupName = header.dataset.group;
                    const content = header.nextElementSibling;

                    if (this.expandedGroups.has(groupName)) {
                        this.expandedGroups.delete(groupName);
                        header.classList.remove('expanded');
                        content.classList.remove('expanded');
                    } else {
                        this.expandedGroups.add(groupName);
                        header.classList.add('expanded');
                        content.classList.add('expanded');
                    }
                });
            });

            // Add icon click handlers
            iconGrid.querySelectorAll('.icon-grid-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.setIcon(item.dataset.icon);

                    // Update UI
                    iconGrid.querySelectorAll('.icon-grid-item').forEach(i => i.classList.remove('active'));
                    item.classList.add('active');

                    if (selectedIconDisplay) {
                        selectedIconDisplay.textContent = item.dataset.icon;
                    }
                    if (selectedIconInput) {
                        selectedIconInput.value = item.dataset.icon;
                    }

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('icon-picker-modal'));
                    if (modal) modal.hide();
                });
            });
        };

        renderIcons();

        if (iconSearch) {
            iconSearch.addEventListener('input', (e) => {
                renderIcons(e.target.value);
            });
        }
    }

    /**
     * Initialize color picker with swatches
     */
    initColorPicker() {
        const swatchesContainer = document.getElementById('color-swatches');
        const customColorInput = document.getElementById('counter-color');
        const customColorLabel = this.container.querySelector('.custom-color-label');

        // Handle swatch clicks
        if (swatchesContainer) {
            swatchesContainer.querySelectorAll('.color-swatch').forEach(swatch => {
                swatch.addEventListener('click', () => {
                    const color = swatch.dataset.color;
                    this.setColor(color);

                    // Update active states
                    swatchesContainer.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
                    swatch.classList.add('active');
                    if (customColorLabel) customColorLabel.classList.remove('active');
                    if (customColorInput) customColorInput.value = color;
                });
            });
        }

        // Handle custom color picker
        if (customColorInput) {
            customColorInput.addEventListener('input', (e) => {
                this.setColor(e.target.value);

                // Remove active from swatches, add to custom
                if (swatchesContainer) {
                    swatchesContainer.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
                }
                if (customColorLabel) customColorLabel.classList.add('active');
            });
        }
    }

    /**
     * Initialize format options
     */
    initFormatOptions() {
        const formatSelect = document.getElementById('counter-format');
        const prefixInput = document.getElementById('counter-prefix');
        const suffixInput = document.getElementById('counter-suffix');
        const decimalsInput = document.getElementById('counter-decimals');

        if (formatSelect) {
            formatSelect.addEventListener('change', (e) => {
                this.setFormat(e.target.value);
            });
        }

        if (prefixInput) {
            prefixInput.addEventListener('input', (e) => {
                this.config.prefix = e.target.value;
                this.updatePreview();
                this.checkForChanges();
            });
        }

        if (suffixInput) {
            suffixInput.addEventListener('input', (e) => {
                this.config.suffix = e.target.value;
                this.updatePreview();
                this.checkForChanges();
            });
        }

        if (decimalsInput) {
            decimalsInput.addEventListener('input', (e) => {
                this.config.decimals = parseInt(e.target.value, 10) || 0;
                this.updatePreview();
                this.checkForChanges();
            });
        }
    }

    /**
     * Initialize refresh button
     */
    initRefreshButton() {
        const refreshBtn = document.getElementById('refresh-preview');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.updatePreview();
            });
        }
    }

    /**
     * Set counter icon
     */
    setIcon(icon) {
        this.config.icon = icon;

        // Update preview icon
        const previewIcon = document.getElementById('preview-icon');
        if (previewIcon) previewIcon.textContent = icon;

        this.updatePreview();
        this.checkForChanges();
    }

    /**
     * Set counter color
     */
    setColor(color) {
        this.config.color = color;

        // Update preview card
        const previewCard = document.getElementById('counter-card-preview');
        if (previewCard) previewCard.style.backgroundColor = color;

        this.updatePreview();
        this.checkForChanges();
    }

    /**
     * Set counter format
     */
    setFormat(format) {
        this.config.format = format;
        this.updatePreview();
        this.checkForChanges();
    }

    /**
     * Handle query test success - counter doesn't need columns
     */
    onQueryTest(columns) {
        this.columns = columns;
        this.clearQueryError();

        // Check for 'counter' column
        const hasCounterColumn = columns.includes('counter');
        if (!hasCounterColumn) {
            Toast.warning(`Query should return a column named 'counter'. Found: ${columns.join(', ')}`);
        } else {
            Toast.success('Query is valid.');
        }

        this.updatePreview();
    }

    /**
     * Update preview with current data
     */
    async updatePreview() {
        if (!this.preview) return;
        if (this.isLoading || this.initialLoad) return;

        // Update preview name
        const previewName = document.getElementById('preview-name');
        if (previewName) {
            previewName.textContent = this.counterName || this.elementName || 'Counter Name';
        }

        // Update preview config
        this.preview.setConfig(this.config);

        const query = this.queryBuilder ? this.queryBuilder.getQuery() : '';
        if (!query.trim()) {
            this.preview.setValue(12345); // Dummy value
            return;
        }

        const filterValues = this.getSidebarFilterValues();
        const placeholderSettings = this.getPlaceholderSettingsForQuery();

        this.preview.showLoading();

        try {
            const result = await Ajax.post('preview_counter', {
                query: query,
                filters: filterValues,
                placeholder_settings: placeholderSettings
            });

            if (result.success && result.data && result.data.counterData) {
                this.preview.setValue(result.data.counterData.value);
            } else {
                this.preview.setValue(0);
            }
        } catch (error) {
            this.preview.setValue(0);
            console.error('Preview update failed:', error);
        }
    }

    /**
     * Counter doesn't need data mapping validation
     */
    validateMapping(mapping) {
        return true; // Counter doesn't use data mapping
    }

    /**
     * Load counter-specific data when editing
     */
    async loadElement(counterId) {
        this.isLoading = true;

        // Show skeleton loading state
        if (this.preview) {
            this.preview.showLoading();
        }

        try {
            const result = await Ajax.post('load_counter', { id: counterId });

            if (result.success && result.data) {
                const counter = result.data;

                this.counterName = counter.name;
                this.counterDescription = counter.description || '';
                this.elementName = this.counterName;
                this.elementDescription = this.counterDescription;

                const nameInput = this.container.querySelector('.graph-name-input');
                if (nameInput) nameInput.value = counter.name;

                const descriptionInput = this.container.querySelector('.graph-description-input');
                if (descriptionInput) {
                    descriptionInput.value = this.counterDescription;
                    autosize.update(descriptionInput);
                }

                if (this.queryBuilder && counter.query) {
                    this.queryBuilder.setQuery(counter.query);
                }

                // Load config
                if (counter.config) {
                    const config = typeof counter.config === 'string'
                        ? JSON.parse(counter.config)
                        : counter.config;

                    this.config = { ...this.config, ...config };
                    this.applyConfigToUI();

                    if (config.activeSidebarTab) {
                        this.setActiveSidebarTab(config.activeSidebarTab);
                    }
                }

                // Load placeholder settings
                if (this.placeholderSettings) {
                    let placeholderSettings = {};
                    if (counter.placeholder_settings) {
                        placeholderSettings = typeof counter.placeholder_settings === 'string'
                            ? JSON.parse(counter.placeholder_settings)
                            : counter.placeholder_settings;
                    }
                    this.placeholderSettings.setSettings(placeholderSettings);
                }

                this.isLoading = false;
                this.initialLoad = false;

                // Test query to trigger preview
                if (counter.query && this.queryBuilder) {
                    await this.queryBuilder.testQuery();
                }

                this.updatePlaceholderSettings();
                this.captureState();
                this.setUnsavedChanges(false);
            } else {
                Toast.error(result.message || 'Failed to load counter');
            }
        } catch (error) {
            Toast.error('Failed to load counter');
            console.error(error);
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Apply config values to UI elements
     */
    applyConfigToUI() {
        const colorInput = document.getElementById('counter-color');
        const swatchesContainer = document.getElementById('color-swatches');
        const customColorLabel = this.container.querySelector('.custom-color-label');
        const formatSelect = document.getElementById('counter-format');
        const prefixInput = document.getElementById('counter-prefix');
        const suffixInput = document.getElementById('counter-suffix');
        const decimalsInput = document.getElementById('counter-decimals');
        const selectedIconDisplay = this.container.querySelector('.selected-icon');
        const selectedIconInput = document.getElementById('selected-icon');
        const previewCard = document.getElementById('counter-card-preview');
        const previewIcon = document.getElementById('preview-icon');

        // Update color swatches
        if (colorInput) colorInput.value = this.config.color;
        if (swatchesContainer) {
            let foundSwatch = false;
            swatchesContainer.querySelectorAll('.color-swatch').forEach(swatch => {
                if (swatch.dataset.color.toUpperCase() === this.config.color.toUpperCase()) {
                    swatch.classList.add('active');
                    foundSwatch = true;
                } else {
                    swatch.classList.remove('active');
                }
            });
            // If color not in swatches, mark custom as active
            if (customColorLabel) {
                if (foundSwatch) {
                    customColorLabel.classList.remove('active');
                } else {
                    customColorLabel.classList.add('active');
                }
            }
        }

        if (formatSelect) formatSelect.value = this.config.format;
        if (prefixInput) prefixInput.value = this.config.prefix || '';
        if (suffixInput) suffixInput.value = this.config.suffix || '';
        if (decimalsInput) decimalsInput.value = this.config.decimals || 0;
        if (selectedIconDisplay) selectedIconDisplay.textContent = this.config.icon;
        if (selectedIconInput) selectedIconInput.value = this.config.icon;
        if (previewCard) previewCard.style.backgroundColor = this.config.color;
        if (previewIcon) previewIcon.textContent = this.config.icon;
    }

    /**
     * Get counter config
     */
    getConfig() {
        return { ...this.config };
    }

    /**
     * Get counter-specific save data
     */
    getTypeSpecificSaveData() {
        return {};
    }

    /**
     * Get current state including counter-specific data
     */
    getCurrentState() {
        return {
            name: this.counterName || this.elementName,
            description: this.counterDescription || this.elementDescription,
            query: this.queryBuilder ? this.queryBuilder.getQuery() : '',
            mapping: '{}', // Counter doesn't use mapping
            config: JSON.stringify(this.config),
            filters: this.filterManager ? JSON.stringify(this.filterManager.getFilters()) : '[]',
            placeholderSettings: this.placeholderSettings ? JSON.stringify(this.placeholderSettings.getSettings()) : '{}',
            categories: this.selectedCategories ? JSON.stringify(this.selectedCategories.slice().sort()) : '[]'
        };
    }

    /**
     * Save counter
     */
    async save() {
        const errors = [];

        if (this.formValidator && !this.formValidator.validate()) {
            this.expandSidebar();
            errors.push('Counter name is required');
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

        if (errors.length > 0) {
            Toast.error(errors.map(err => '• ' + err).join('<br>'));
            return;
        }

        Loading.show('Saving counter...');

        try {
            const config = this.getConfig();
            config.activeSidebarTab = this.activeSidebarTab;

            const placeholderSettings = this.placeholderSettings
                ? this.placeholderSettings.getSettings()
                : {};

            const data = {
                id: this.counterId,
                name: this.counterName || this.elementName,
                description: this.counterDescription || this.elementDescription,
                query: query,
                config: config,
                placeholder_settings: placeholderSettings,
                categories: this.selectedCategories || []
            };

            const result = await Ajax.post('save_counter', data);

            if (result.success) {
                const successMsg = result.message || (this.counterId ? 'Counter updated successfully' : 'Counter created successfully');
                Toast.success(successMsg);

                this.captureState();
                this.setUnsavedChanges(false);

                if (!this.counterId && result.data && result.data.id) {
                    window.location.href = `?urlq=widget-counter/edit/${result.data.id}`;
                }
            } else {
                Toast.error(result.message || 'Failed to save counter');
            }
        } catch (error) {
            Toast.error('Failed to save counter');
            console.error(error);
        } finally {
            Loading.hide();
        }
    }

    /**
     * Override save handler to sync counter name
     */
    initSaveHandler() {
        const saveBtn = document.querySelector('.page-header-right .save-counter-btn');
        const nameInput = this.container.querySelector('.graph-name-input');
        const descriptionInput = this.container.querySelector('.graph-description-input');

        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.save();
            });
        }

        if (nameInput) {
            this.counterName = nameInput.value || '';
            this.elementName = this.counterName;

            nameInput.addEventListener('input', (e) => {
                this.counterName = e.target.value;
                this.elementName = this.counterName;

                // Update preview name
                const previewName = document.getElementById('preview-name');
                if (previewName) {
                    previewName.textContent = this.counterName || 'Counter Name';
                }

                this.checkForChanges();
            });
        }

        if (descriptionInput) {
            this.counterDescription = descriptionInput.value || '';
            this.elementDescription = this.counterDescription;

            autosize(descriptionInput);

            descriptionInput.addEventListener('input', (e) => {
                this.counterDescription = e.target.value;
                this.elementDescription = this.counterDescription;
                this.checkForChanges();
            });
        }
    }

    /**
     * Initialize form validation for counter
     */
    initFormValidation() {
        const form = document.getElementById('counter-form');
        if (!form) return;

        this.formValidator = new FormValidator(form, {
            rules: {
                'counter-name-input': { required: true }
            },
            messages: {
                'counter-name-input': { required: 'Counter name is required' }
            },
            validateOnBlur: true,
            validateOnInput: true
        });
    }

    /**
     * Initialize category chips for counter
     */
    initCategoryChips() {
        const chipsContainer = this.container.querySelector('#category-chips');
        const hiddenInput = this.container.querySelector('#selected-categories');
        const wrapper = this.container.querySelector('#counter-categories-wrapper');

        if (!chipsContainer || !hiddenInput) return;

        try {
            this.selectedCategories = JSON.parse(hiddenInput.value) || [];
        } catch (e) {
            this.selectedCategories = [];
        }

        const chips = chipsContainer.querySelectorAll('.category-chip');
        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                const categoryId = parseInt(chip.dataset.categoryId, 10);
                const color = chip.dataset.color || '#6c757d';
                const isActive = chip.classList.contains('active');

                if (isActive) {
                    chip.classList.remove('active');
                    chip.style.backgroundColor = '';
                    chip.style.borderColor = '';
                    chip.style.color = '';
                    this.selectedCategories = this.selectedCategories.filter(id => id !== categoryId);
                } else {
                    chip.classList.add('active');
                    chip.style.backgroundColor = color;
                    chip.style.borderColor = color;
                    chip.style.color = '#fff';
                    if (!this.selectedCategories.includes(categoryId)) {
                        this.selectedCategories.push(categoryId);
                    }
                }

                hiddenInput.value = JSON.stringify(this.selectedCategories);

                if (wrapper && this.selectedCategories.length > 0) {
                    wrapper.classList.remove('is-invalid');
                }

                this.checkForChanges();
            });
        });
    }
}
