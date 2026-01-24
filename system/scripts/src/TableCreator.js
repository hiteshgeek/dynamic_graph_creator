/**
 * TableCreator - Table-specific creator class
 * Extends ElementCreator with table-specific functionality
 */

import ElementCreator from './element/ElementCreator.js';
import TablePreview from './TablePreview.js';

const Toast = window.Toast;
const Ajax = window.Ajax;
const Loading = window.Loading;
const autosize = window.autosize;

export default class TableCreator extends ElementCreator {
    constructor(container, options = {}) {
        super(container, {
            ...options,
            elementId: options.tableId || options.elementId || null
        });

        // Table-specific properties
        this.tableId = this.elementId;
        this.tableName = '';
        this.tableDescription = '';

        // Table config
        this.config = {
            columns: [],
            pagination: {
                enabled: true,
                rowsPerPage: 10,
                rowsPerPageOptions: [10, 25, 50, 100]
            },
            style: {
                striped: true,
                bordered: true,
                hover: true,
                density: 'comfortable'
            }
        };

        // No data mapper for table
        this.dataMapper = null;
    }

    /**
     * Element type identifiers
     */
    getElementTypeSlug() {
        return 'table';
    }

    getElementTypeName() {
        return 'Table';
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

        // Load existing table if editing
        if (this.tableId) {
            if (this.preview) {
                this.preview.showLoading();
            }
            this.loadElement(this.tableId);
        } else {
            // Show dummy data for new tables
            if (this.preview) {
                this.preview.showDummyData();
            }
            this.captureState();
        }
    }

    /**
     * Initialize table preview component
     */
    initPreview() {
        const previewWrapper = this.container.querySelector('.table-preview-wrapper');
        if (previewWrapper) {
            this.preview = new TablePreview(previewWrapper, {
                showSkeleton: !!this.elementId
            });
        }
    }

    /**
     * Initialize table-specific components
     */
    initTypeSpecificComponents() {
        this.initPaginationSettings();
        this.initStyleOptions();
        this.initRefreshButton();
    }

    /**
     * Initialize pagination settings
     */
    initPaginationSettings() {
        const paginationEnabled = document.getElementById('pagination-enabled');
        const rowsPerPageWrapper = document.getElementById('rows-per-page-wrapper');
        const rowsPerPageSelect = document.getElementById('rows-per-page');

        if (paginationEnabled) {
            paginationEnabled.addEventListener('change', (e) => {
                this.config.pagination.enabled = e.target.checked;
                if (rowsPerPageWrapper) {
                    rowsPerPageWrapper.style.display = e.target.checked ? '' : 'none';
                }
                this.updatePreview();
                this.checkForChanges();
            });
        }

        if (rowsPerPageSelect) {
            rowsPerPageSelect.addEventListener('change', (e) => {
                this.config.pagination.rowsPerPage = parseInt(e.target.value, 10);
                this.updatePreview();
                this.checkForChanges();
            });
        }
    }

    /**
     * Initialize style options
     */
    initStyleOptions() {
        const stripedInput = document.getElementById('style-striped');
        const borderedInput = document.getElementById('style-bordered');
        const hoverInput = document.getElementById('style-hover');
        const densityInputs = document.querySelectorAll('input[name="density"]');

        if (stripedInput) {
            stripedInput.addEventListener('change', (e) => {
                this.config.style.striped = e.target.checked;
                this.updatePreview();
                this.checkForChanges();
            });
        }

        if (borderedInput) {
            borderedInput.addEventListener('change', (e) => {
                this.config.style.bordered = e.target.checked;
                this.updatePreview();
                this.checkForChanges();
            });
        }

        if (hoverInput) {
            hoverInput.addEventListener('change', (e) => {
                this.config.style.hover = e.target.checked;
                this.updatePreview();
                this.checkForChanges();
            });
        }

        if (densityInputs.length > 0) {
            densityInputs.forEach(input => {
                input.addEventListener('change', (e) => {
                    this.config.style.density = e.target.value;
                    this.updatePreview();
                    this.checkForChanges();
                });
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
     * Handle query test success
     */
    onQueryTest(columns) {
        this.columns = columns;
        this.clearQueryError();

        if (columns.length === 0) {
            Toast.warning('Query returned no columns');
        } else {
            Toast.success(`Query is valid. Found ${columns.length} columns.`);
        }

        this.updatePreview();
    }

    /**
     * Update preview with current data
     */
    async updatePreview() {
        if (!this.preview) return;
        if (this.isLoading || this.initialLoad) return;

        const query = this.queryBuilder ? this.queryBuilder.getQuery() : '';
        if (!query.trim()) {
            // Update dummy data preview with current config
            this.preview.setConfig(this.config);
            if (this.preview.isDummyData) {
                // Re-render with new config
                this.preview.render();
                this.preview.showDummyDataLabel();
            } else {
                this.preview.showDummyData();
            }
            return;
        }

        const filterValues = this.getSidebarFilterValues();
        const placeholderSettings = this.getPlaceholderSettingsForQuery();

        this.preview.showLoading();
        this.preview.setConfig(this.config);

        try {
            const result = await Ajax.post('preview_table', {
                query: query,
                filters: filterValues,
                placeholder_settings: placeholderSettings
            });

            if (result.success && result.data && result.data.tableData) {
                this.preview.setData(result.data.tableData);
                this.preview.render();
            } else {
                this.preview.showEmpty('No data returned');
            }
        } catch (error) {
            this.preview.showError('Failed to load preview');
            console.error('Preview update failed:', error);
        }
    }

    /**
     * Table doesn't need data mapping validation
     */
    validateMapping(mapping) {
        return true;
    }

    /**
     * Load table-specific data when editing
     */
    async loadElement(tableId) {
        this.isLoading = true;

        if (this.preview) {
            this.preview.showLoading();
        }

        try {
            const result = await Ajax.post('load_table', { id: tableId });

            if (result.success && result.data) {
                const table = result.data;

                this.tableName = table.name;
                this.tableDescription = table.description || '';
                this.elementName = this.tableName;
                this.elementDescription = this.tableDescription;

                const nameInput = this.container.querySelector('.graph-name-input');
                if (nameInput) nameInput.value = table.name;

                const descriptionInput = this.container.querySelector('.graph-description-input');
                if (descriptionInput) {
                    descriptionInput.value = this.tableDescription;
                    autosize.update(descriptionInput);
                }

                if (this.queryBuilder && table.query) {
                    this.queryBuilder.setQuery(table.query);
                }

                // Load config
                if (table.config) {
                    const config = typeof table.config === 'string'
                        ? JSON.parse(table.config)
                        : table.config;

                    this.config = { ...this.config, ...config };
                    this.applyConfigToUI();
                }

                // Load placeholder settings
                if (this.placeholderSettings) {
                    let placeholderSettings = {};
                    if (table.placeholder_settings) {
                        placeholderSettings = typeof table.placeholder_settings === 'string'
                            ? JSON.parse(table.placeholder_settings)
                            : table.placeholder_settings;
                    }
                    this.placeholderSettings.setSettings(placeholderSettings);
                }

                this.isLoading = false;
                this.initialLoad = false;

                // Test query to trigger preview
                if (table.query && this.queryBuilder) {
                    await this.queryBuilder.testQuery();
                }

                this.updatePlaceholderSettings();
                this.captureState();
                this.setUnsavedChanges(false);
            } else {
                Toast.error(result.message || 'Failed to load table');
            }
        } catch (error) {
            Toast.error('Failed to load table');
            console.error(error);
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Apply config values to UI elements
     */
    applyConfigToUI() {
        const paginationEnabled = document.getElementById('pagination-enabled');
        const rowsPerPageWrapper = document.getElementById('rows-per-page-wrapper');
        const rowsPerPageSelect = document.getElementById('rows-per-page');
        const stripedInput = document.getElementById('style-striped');
        const borderedInput = document.getElementById('style-bordered');
        const hoverInput = document.getElementById('style-hover');

        const { pagination, style } = this.config;

        if (paginationEnabled) {
            paginationEnabled.checked = pagination.enabled;
        }
        if (rowsPerPageWrapper) {
            rowsPerPageWrapper.style.display = pagination.enabled ? '' : 'none';
        }
        if (rowsPerPageSelect) {
            rowsPerPageSelect.value = pagination.rowsPerPage || 10;
        }
        if (stripedInput) {
            stripedInput.checked = style.striped;
        }
        if (borderedInput) {
            borderedInput.checked = style.bordered;
        }
        if (hoverInput) {
            hoverInput.checked = style.hover;
        }

        // Update density radio buttons
        const densityRadio = document.querySelector(`input[name="density"][value="${style.density}"]`);
        if (densityRadio) {
            densityRadio.checked = true;
        }
    }

    /**
     * Get table config
     */
    getConfig() {
        return { ...this.config };
    }

    /**
     * Get table-specific save data
     */
    getTypeSpecificSaveData() {
        return {};
    }

    /**
     * Get current state including table-specific data
     */
    getCurrentState() {
        return {
            name: this.tableName || this.elementName,
            description: this.tableDescription || this.elementDescription,
            query: this.queryBuilder ? this.queryBuilder.getQuery() : '',
            mapping: '{}',
            config: JSON.stringify(this.config),
            filters: this.filterManager ? JSON.stringify(this.filterManager.getFilters()) : '[]',
            placeholderSettings: this.placeholderSettings ? JSON.stringify(this.placeholderSettings.getSettings()) : '{}',
            categories: this.selectedCategories ? JSON.stringify(this.selectedCategories.slice().sort()) : '[]'
        };
    }

    /**
     * Save table
     */
    async save() {
        const errors = [];

        if (this.formValidator && !this.formValidator.validate()) {
            this.expandSidebar();
            errors.push('Table name is required');
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

        Loading.show('Saving table...');

        try {
            const config = this.getConfig();
            config.activeSidebarTab = this.activeSidebarTab;

            const placeholderSettings = this.placeholderSettings
                ? this.placeholderSettings.getSettings()
                : {};

            const data = {
                id: this.tableId,
                name: this.tableName || this.elementName,
                description: this.tableDescription || this.elementDescription,
                query: query,
                config: config,
                placeholder_settings: placeholderSettings,
                categories: this.selectedCategories || []
            };

            const result = await Ajax.post('save_table', data);

            if (result.success) {
                const successMsg = result.message || (this.tableId ? 'Table updated successfully' : 'Table created successfully');
                Toast.success(successMsg);

                this.captureState();
                this.setUnsavedChanges(false);

                if (!this.tableId && result.data && result.data.id) {
                    window.location.href = `?urlq=widget-table/edit/${result.data.id}`;
                }
            } else {
                Toast.error(result.message || 'Failed to save table');
            }
        } catch (error) {
            Toast.error('Failed to save table');
            console.error(error);
        } finally {
            Loading.hide();
        }
    }

    /**
     * Override save handler to sync table name
     */
    initSaveHandler() {
        const saveBtn = document.querySelector('.page-header-right .save-table-btn');
        const nameInput = this.container.querySelector('.graph-name-input');
        const descriptionInput = this.container.querySelector('.graph-description-input');

        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.save();
            });
        }

        if (nameInput) {
            this.tableName = nameInput.value || '';
            this.elementName = this.tableName;

            nameInput.addEventListener('input', (e) => {
                this.tableName = e.target.value;
                this.elementName = this.tableName;
                this.checkForChanges();
            });
        }

        if (descriptionInput) {
            this.tableDescription = descriptionInput.value || '';
            this.elementDescription = this.tableDescription;

            autosize(descriptionInput);

            descriptionInput.addEventListener('input', (e) => {
                this.tableDescription = e.target.value;
                this.elementDescription = this.tableDescription;
                this.checkForChanges();
            });
        }
    }

    /**
     * Initialize form validation for table
     */
    initFormValidation() {
        const form = document.getElementById('table-form');
        if (!form) return;

        this.formValidator = new FormValidator(form, {
            rules: {
                'table-name-input': { required: true }
            },
            messages: {
                'table-name-input': { required: 'Table name is required' }
            },
            validateOnBlur: true,
            validateOnInput: true
        });
    }

    /**
     * Initialize category chips for table
     */
    initCategoryChips() {
        const chipsContainer = this.container.querySelector('#category-chips');
        const hiddenInput = this.container.querySelector('#selected-categories');
        const wrapper = this.container.querySelector('#table-categories-wrapper');

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
