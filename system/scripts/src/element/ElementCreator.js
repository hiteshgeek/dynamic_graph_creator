/**
 * ElementCreator - Base orchestrator class for element creators
 * Provides shared functionality for Graph, Table, List, Counter creators
 *
 * Subclasses must implement:
 * - getElementTypeSlug() - returns element type identifier (e.g., 'graph', 'table')
 * - getElementTypeName() - returns human-readable name (e.g., 'Graph', 'Table')
 * - initPreview() - initialize preview component
 * - initTypeSpecificComponents() - initialize type-specific components
 * - updatePreview() - update preview with current data
 * - validateMapping(mapping) - validate data mapping for element type
 * - getTypeSpecificSaveData() - return type-specific data for saving
 * - loadTypeSpecificData(data) - load type-specific data when editing
 * - onQueryTest(columns) - handle successful query test
 * - onQueryError(error) - handle query test error
 */

import QueryBuilder from '../QueryBuilder.js';
import DataFilterManager from '../DataFilterManager.js';
import PlaceholderSettings from '../PlaceholderSettings.js';
import DataFilterUtils from '../DataFilterUtils.js';
import MandatoryFilterValidator from '../MandatoryFilterValidator.js';
import DataMapper from '../DataMapper.js';

// Use globals from CDN
const autosize = window.autosize;
const FormValidator = window.FormValidator;
const Toast = window.Toast;
const Loading = window.Loading;
const Ajax = window.Ajax;

export default class ElementCreator {
    constructor(container, options = {}) {
        this.container = container;
        this.elementId = options.elementId || null;
        this.elementName = '';
        this.elementDescription = '';

        // Component instances
        this.preview = null;
        this.queryBuilder = null;
        this.dataMapper = null;
        this.filterManager = null;
        this.placeholderSettings = null;
        this.mandatoryFilterValidator = null;
        this.formValidator = null;

        // State
        this.columns = [];
        this.isLoading = false;
        this.initialLoad = !!options.elementId; // True when editing
        this.activeSidebarTab = 'config';

        // Unsaved changes tracking
        this.hasUnsavedChanges = false;

        // Status tracking
        this.queryError = null;
        this.queryTested = !!options.elementId; // True when editing existing element
        this.placeholderWarnings = [];
        this.savedState = null;

        // Category selection
        this.selectedCategories = [];

        // Filter selection
        this.selectedFilters = [];
    }

    /**
     * Abstract methods - must be implemented by subclasses
     */

    getElementTypeSlug() {
        throw new Error('Subclass must implement getElementTypeSlug()');
    }

    getElementTypeName() {
        throw new Error('Subclass must implement getElementTypeName()');
    }

    initPreview() {
        throw new Error('Subclass must implement initPreview()');
    }

    initTypeSpecificComponents() {
        // Optional - subclasses can override
    }

    updatePreview() {
        throw new Error('Subclass must implement updatePreview()');
    }

    validateMapping(mapping) {
        throw new Error('Subclass must implement validateMapping()');
    }

    getTypeSpecificSaveData() {
        return {};
    }

    loadTypeSpecificData(data) {
        // Optional - subclasses can override
    }

    onQueryTest(columns) {
        // Default implementation - subclasses can override
        this.columns = columns;
        if (this.dataMapper) {
            this.dataMapper.setColumns(columns);
        }
        this.clearQueryError();
        this.queryTested = true; // Mark query as successfully tested
        Toast.success(`Query valid. Found ${columns.length} columns.`);
        this.updatePreview();
    }

    onQueryError(error) {
        // Default implementation - subclasses can override
        this.columns = [];
        if (this.dataMapper) {
            this.dataMapper.setColumns([]);
        }
        this.setQueryError(error);
        this.queryTested = false; // Mark query as failed/not tested
        Toast.error(error);
    }

    /**
     * Initialize all components
     */
    init() {
        this.initMandatoryFilterValidator();
        this.initPreview();
        this.initQueryBuilder();
        this.initDataMapper();
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

        // Load existing element if editing
        if (this.elementId) {
            this.loadElement(this.elementId);
        } else {
            this.captureState();
        }
    }

    /**
     * Initialize mandatory filter validator
     */
    initMandatoryFilterValidator() {
        this.mandatoryFilterValidator = new MandatoryFilterValidator(this.container);
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
                    // Reset queryTested when query changes (requires re-testing)
                    if (!this.initialLoad) {
                        this.queryTested = false;
                    }
                },
                getFilterValues: () => this.getSidebarFilterValues(),
                getPlaceholderSettings: () => this.getPlaceholderSettingsForQuery()
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
        }
    }

    /**
     * Initialize filter manager component
     */
    initFilterManager() {
        const filterContainer = this.container.querySelector('.filter-manager');
        if (filterContainer) {
            this.filterManager = new DataFilterManager(filterContainer, {
                entityType: this.getElementTypeSlug(),
                entityId: this.elementId,
                onChange: () => {
                    this.onFiltersChanged();
                    this.checkForChanges();
                }
            });
            this.filterManager.init();
        }
    }

    /**
     * Initialize placeholder settings component
     */
    initPlaceholderSettings() {
        const settingsContainer = this.container.querySelector('.element-main, .graph-main');
        if (settingsContainer) {
            this.placeholderSettings = new PlaceholderSettings(settingsContainer, {
                onChange: () => this.checkForChanges(),
                getMatchedFilters: () => this.getMatchedFilters()
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
     * Get matched filters for placeholders in query
     */
    getMatchedFilters() {
        const matchedFilters = {};
        const filtersContainer = this.container.querySelector('#graph-filters, #element-filters, #counter-filters, #table-filters');
        if (!filtersContainer) return matchedFilters;

        const dateRangeTypes = ['date_range', 'main_datepicker'];

        // Add all selected filters
        const filterItems = filtersContainer.querySelectorAll('.filter-input-item');
        filterItems.forEach(item => {
            const filterKey = item.dataset.filterKey;
            const filterType = item.dataset.filterType;
            const filterLabel = item.querySelector('.filter-input-label')?.textContent || filterKey;
            const isRequired = item.dataset.isRequired === '1';

            if (filterKey && this.selectedFilters && this.selectedFilters.includes(filterKey)) {
                matchedFilters['::' + filterKey] = {
                    filter_key: filterKey,
                    filter_label: filterLabel,
                    filter_type: filterType,
                    is_required: isRequired
                };

                if (dateRangeTypes.includes(filterType)) {
                    matchedFilters['::' + filterKey + '_from'] = {
                        filter_key: filterKey,
                        filter_label: filterLabel + ' (From)',
                        filter_type: filterType,
                        is_derived: true,
                        parent_key: filterKey,
                        is_required: isRequired
                    };
                    matchedFilters['::' + filterKey + '_to'] = {
                        filter_key: filterKey,
                        filter_label: filterLabel + ' (To)',
                        filter_type: filterType,
                        is_derived: true,
                        parent_key: filterKey,
                        is_required: isRequired
                    };
                }
            }
        });

        // Second pass for available filters
        filterItems.forEach(item => {
            const filterKey = item.dataset.filterKey;
            const filterType = item.dataset.filterType;
            const filterLabel = item.querySelector('.filter-input-label')?.textContent || filterKey;

            if (filterKey && dateRangeTypes.includes(filterType)) {
                if (!matchedFilters['::' + filterKey + '_from']) {
                    matchedFilters['::' + filterKey + '_from'] = {
                        filter_key: filterKey,
                        filter_label: filterLabel + ' (From)',
                        filter_type: filterType,
                        is_derived: true,
                        parent_key: filterKey,
                        not_selected: true
                    };
                }
                if (!matchedFilters['::' + filterKey + '_to']) {
                    matchedFilters['::' + filterKey + '_to'] = {
                        filter_key: filterKey,
                        filter_label: filterLabel + ' (To)',
                        filter_type: filterType,
                        is_derived: true,
                        parent_key: filterKey,
                        not_selected: true
                    };
                }
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
     * Update status indicators in page header
     */
    updateStatusIndicators() {
        const statusContainer = document.querySelector('.page-header-right .status-indicators');
        if (!statusContainer) return;

        let html = '';

        if (this.hasUnsavedChanges) {
            html += `<span class="save-indicator unsaved"><i class="fas fa-circle"></i> Unsaved</span>`;
        } else {
            html += `<span class="save-indicator saved"><i class="fas fa-check"></i> Saved</span>`;
        }

        if (this.queryError) {
            html += `<span class="status-box status-error" title="${this.escapeHtml(this.queryError)}">
                <i class="fas fa-times-circle"></i>
            </span>`;
        }

        if (this.placeholderWarnings.length > 0) {
            const warningText = `Filter not found: ${this.placeholderWarnings.join(', ')}`;
            html += `<span class="status-box status-warning" title="${this.escapeHtml(warningText)}">
                <i class="fas fa-exclamation-triangle"></i>
            </span>`;
        }

        statusContainer.innerHTML = html;

        const tooltipElements = statusContainer.querySelectorAll('[title]');
        tooltipElements.forEach(el => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                new bootstrap.Tooltip(el, { placement: 'bottom' });
            }
        });
    }

    /**
     * Initialize save handler
     */
    initSaveHandler() {
        const saveBtn = document.querySelector('.page-header-right .save-graph-btn, .page-header-right .save-element-btn');
        const nameInput = this.container.querySelector('.graph-name-input, .element-name-input');
        const descriptionInput = this.container.querySelector('.graph-description-input, .element-description-input');

        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.save();
            });
        }

        if (nameInput) {
            this.elementName = nameInput.value || '';
            nameInput.addEventListener('input', (e) => {
                this.elementName = e.target.value;
                this.checkForChanges();
            });
        }

        if (descriptionInput) {
            this.elementDescription = descriptionInput.value || '';
            autosize(descriptionInput);
            descriptionInput.addEventListener('input', (e) => {
                this.elementDescription = e.target.value;
                this.checkForChanges();
            });
        }
    }

    /**
     * Initialize form validation
     */
    initFormValidation() {
        const form = document.getElementById('graph-form') || document.getElementById('element-form');
        if (!form) return;

        const nameInputName = form.querySelector('.graph-name-input, .element-name-input')?.getAttribute('name') || 'element-name-input';

        this.formValidator = new FormValidator(form, {
            rules: {
                [nameInputName]: { required: true }
            },
            messages: {
                [nameInputName]: { required: `${this.getElementTypeName()} name is required` }
            },
            validateOnBlur: true,
            validateOnInput: true
        });
    }

    /**
     * Initialize category chips
     */
    initCategoryChips() {
        const chipsContainer = this.container.querySelector('#category-chips');
        const hiddenInput = this.container.querySelector('#selected-categories');
        const wrapper = this.container.querySelector('#graph-categories-wrapper, #element-categories-wrapper');

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

    /**
     * Validate category selection
     */
    validateCategories() {
        const wrapper = this.container.querySelector('#graph-categories-wrapper, #element-categories-wrapper');
        const isValid = this.selectedCategories && this.selectedCategories.length > 0;

        if (wrapper) {
            wrapper.classList.toggle('is-invalid', !isValid);
        }

        return isValid;
    }

    /**
     * Initialize query/mapping tabs
     */
    initTabs() {
        const tabs = this.container.querySelectorAll('.query-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                this.container.querySelectorAll('.query-tab-content').forEach(c => {
                    c.classList.remove('active');
                });
                tab.classList.add('active');
                const targetId = 'tab-' + tab.dataset.tab;
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }

    /**
     * Initialize sidebar tabs
     */
    initSidebarTabs() {
        const tabs = this.container.querySelectorAll('.sidebar-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                this.setActiveSidebarTab(tab.dataset.tab);
            });
        });

        const storageKey = `${this.getElementTypeSlug()}CreatorSidebarTab`;
        const savedTab = localStorage.getItem(storageKey);
        if (savedTab && (savedTab === 'config' || savedTab === 'filters')) {
            this.setActiveSidebarTab(savedTab);
        }
    }

    /**
     * Set active sidebar tab
     */
    setActiveSidebarTab(tabName) {
        this.activeSidebarTab = tabName;

        const storageKey = `${this.getElementTypeSlug()}CreatorSidebarTab`;
        localStorage.setItem(storageKey, tabName);

        const tabs = this.container.querySelectorAll('.sidebar-tab');
        tabs.forEach(t => t.classList.remove('active'));

        this.container.querySelectorAll('.sidebar-tab-content').forEach(c => {
            c.classList.remove('active');
        });

        const targetTab = this.container.querySelector(`.sidebar-tab[data-tab="${tabName}"]`);
        if (targetTab) {
            targetTab.classList.add('active');
        }

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
        const collapseHeader = this.container.querySelector('.sidebar-card-header');
        const sidebar = this.container.querySelector('.graph-sidebar, .element-sidebar');

        if (sidebar && sidebar.classList.contains('collapsed')) {
            setTimeout(() => {
                if (this.preview && this.preview.resize) {
                    this.preview.resize();
                }
            }, 100);
        }

        if (collapseHeader) {
            collapseHeader.addEventListener('click', () => {
                const card = this.container.querySelector('.sidebar-card');
                const sidebarLeft = this.container.querySelector('.graph-sidebar-left, .element-sidebar-left');

                if (card) {
                    card.classList.toggle('collapsed');
                }
                if (sidebarLeft) {
                    sidebarLeft.classList.toggle('collapsed');
                    const isCollapsed = sidebarLeft.classList.contains('collapsed');
                    localStorage.setItem(`${this.getElementTypeSlug()}CreatorSidebarCollapsed`, isCollapsed ? 'true' : 'false');
                }

                setTimeout(() => {
                    if (this.preview && this.preview.resize) {
                        this.preview.resize();
                    }
                }, 350);
            });
        }

        // Legacy support
        const headers = this.container.querySelectorAll('.collapsible-header');
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const panel = header.closest('.collapsible-panel');
                const sidebarEl = header.closest('.graph-sidebar, .element-sidebar');

                if (panel) {
                    panel.classList.toggle('collapsed');
                }
                if (sidebarEl) {
                    sidebarEl.classList.toggle('collapsed');
                    const isCollapsed = sidebarEl.classList.contains('collapsed');
                    localStorage.setItem(`${this.getElementTypeSlug()}CreatorSidebarCollapsed`, isCollapsed ? 'true' : 'false');
                }

                setTimeout(() => {
                    if (this.preview && this.preview.resize) {
                        this.preview.resize();
                    }
                }, 350);
            });
        });
    }

    /**
     * Expand sidebar if collapsed
     */
    expandSidebar() {
        const sidebarLeft = this.container.querySelector('.graph-sidebar-left, .element-sidebar-left');
        const card = this.container.querySelector('.sidebar-card');

        if (sidebarLeft && sidebarLeft.classList.contains('collapsed')) {
            sidebarLeft.classList.remove('collapsed');
            if (card) {
                card.classList.remove('collapsed');
            }
            localStorage.setItem(`${this.getElementTypeSlug()}CreatorSidebarCollapsed`, 'false');

            setTimeout(() => {
                if (this.preview && this.preview.resize) {
                    this.preview.resize();
                }
            }, 350);
        }
    }

    /**
     * Initialize keyboard shortcuts
     */
    initKeyboardShortcuts() {
        // Shortcuts are registered globally in common.js
    }

    /**
     * Initialize filter selector
     */
    initFilterSelector() {
        const selectorView = this.container.querySelector('#filter-selector-view');
        const activeView = this.container.querySelector('#filter-active-view');
        const useBtn = this.container.querySelector('#filter-use-btn');
        const changeBtn = this.container.querySelector('#filter-change-btn');
        const countDisplay = this.container.querySelector('.filter-selector-count');
        const checkboxes = this.container.querySelectorAll('.filter-selector-checkbox');

        if (!selectorView || !activeView || !useBtn) return;

        this.selectedFilters = [];

        const mandatoryKeys = this.mandatoryFilterValidator
            ? this.mandatoryFilterValidator.getMandatoryKeys()
            : [];

        const storageKey = this.elementId ? `${this.getElementTypeSlug()}Filters_${this.elementId}` : null;

        // Auto-select mandatory filters
        mandatoryKeys.forEach(key => {
            if (!this.selectedFilters.includes(key)) {
                this.selectedFilters.push(key);
            }
        });

        // Load saved filters
        if (storageKey) {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                try {
                    const savedFilters = JSON.parse(saved);
                    savedFilters.forEach(key => {
                        if (!this.selectedFilters.includes(key)) {
                            this.selectedFilters.push(key);
                        }
                    });
                    checkboxes.forEach(cb => {
                        if (this.selectedFilters.includes(cb.value) && !cb.disabled) {
                            cb.checked = true;
                        }
                    });
                    if (countDisplay) {
                        countDisplay.textContent = `${this.selectedFilters.length} selected`;
                    }
                    if (this.selectedFilters.length > 0) {
                        this.applySelectedFilters(selectorView, activeView);
                    }
                } catch (e) {
                    // Invalid JSON
                }
            } else if (mandatoryKeys.length > 0) {
                if (countDisplay) {
                    countDisplay.textContent = `${this.selectedFilters.length} selected`;
                }
                this.applySelectedFilters(selectorView, activeView);
            }
        } else if (mandatoryKeys.length > 0) {
            if (countDisplay) {
                countDisplay.textContent = `${this.selectedFilters.length} selected`;
            }
            this.applySelectedFilters(selectorView, activeView);
        }

        const updateSelectionState = () => {
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.type === 'checkbox' && cb.checked);
            this.selectedFilters = checkedBoxes.map(cb => cb.value);

            const mandatoryInputs = this.container.querySelectorAll('.filter-selector-checkbox[data-checked="true"]');
            mandatoryInputs.forEach(input => {
                if (!this.selectedFilters.includes(input.value)) {
                    this.selectedFilters.push(input.value);
                }
            });

            if (countDisplay) {
                countDisplay.textContent = `${this.selectedFilters.length} selected`;
            }

            useBtn.disabled = this.selectedFilters.length === 0;
        };

        const selectorItems = this.container.querySelectorAll('.filter-selector-item');
        selectorItems.forEach(item => {
            item.addEventListener('click', (e) => {
                if (e.target.classList.contains('filter-selector-checkbox') ||
                    e.target.closest('.form-check-label') ||
                    e.target.tagName === 'LABEL') {
                    return;
                }

                if (item.dataset.mandatory === '1') {
                    Toast.info('This filter is mandatory and cannot be removed');
                    return;
                }

                const checkbox = item.querySelector('.filter-selector-checkbox');
                if (checkbox && !checkbox.disabled) {
                    checkbox.checked = !checkbox.checked;
                    updateSelectionState();
                }
            });
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => updateSelectionState());
        });

        useBtn.addEventListener('click', () => {
            if (this.selectedFilters.length === 0) return;

            if (storageKey) {
                const nonMandatoryFilters = this.selectedFilters.filter(key => !mandatoryKeys.includes(key));
                localStorage.setItem(storageKey, JSON.stringify(nonMandatoryFilters));
            }

            this.applySelectedFilters(selectorView, activeView);
        });

        if (changeBtn) {
            changeBtn.addEventListener('click', () => {
                selectorView.style.display = 'flex';
                activeView.style.display = 'none';

                // Count both checked checkboxes and mandatory filters
                const selectedCheckboxes = Array.from(checkboxes).filter(cb => cb.type === 'checkbox' && cb.checked);
                const mandatoryCount = this.container.querySelectorAll('.filter-selector-checkbox[data-checked="true"]').length;
                useBtn.disabled = (selectedCheckboxes.length + mandatoryCount) === 0;
            });
        }

        // Enable button initially if there are mandatory filters
        if (mandatoryKeys.length > 0) {
            useBtn.disabled = false;
        }

        // Clipboard copy for placeholders
        this.initPlaceholderCopy(selectorView.querySelectorAll('.filter-selector-keys .placeholder-key'));
    }

    /**
     * Apply selected filters
     */
    applySelectedFilters(selectorView, activeView) {
        selectorView.style.display = 'none';
        activeView.style.display = 'flex';

        const filterItems = this.container.querySelectorAll('#graph-filters .filter-input-item, #element-filters .filter-input-item, #counter-filters .filter-input-item, #table-filters .filter-input-item');
        filterItems.forEach(item => {
            const key = item.dataset.filterKey;
            item.style.display = this.selectedFilters.includes(key) ? 'flex' : 'none';
        });

        const filtersContainer = this.container.querySelector('#graph-filters, #element-filters, #counter-filters, #table-filters');
        if (filtersContainer && typeof FilterRenderer !== 'undefined') {
            FilterRenderer.init(filtersContainer);
        }

        this.updatePlaceholderSettings();
    }

    /**
     * Initialize sidebar filters
     */
    initSidebarFilters() {
        const filtersContainer = this.container.querySelector('#graph-filters, #element-filters, #counter-filters, #table-filters');
        if (!filtersContainer) return;

        this.initPlaceholderCopy(filtersContainer.querySelectorAll('.placeholder-key'));
    }

    /**
     * Initialize placeholder copy functionality
     */
    initPlaceholderCopy(placeholders) {
        placeholders.forEach(placeholder => {
            placeholder.addEventListener('click', async (e) => {
                e.stopPropagation();
                const text = placeholder.textContent;
                const originalText = text;

                const showCopiedFeedback = () => {
                    placeholder.textContent = 'Copied!';
                    placeholder.classList.add('copied');
                    setTimeout(() => {
                        placeholder.textContent = originalText;
                        placeholder.classList.remove('copied');
                    }, 1000);
                };

                try {
                    await navigator.clipboard.writeText(text);
                    showCopiedFeedback();
                } catch (err) {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    showCopiedFeedback();
                }
            });
        });
    }

    /**
     * Handle filter changes
     */
    onFiltersChanged() {
        // Filters changed - subclasses can override
    }

    /**
     * Get current filter values from sidebar inputs
     */
    getSidebarFilterValues() {
        let filtersContainer = this.container.querySelector('#graph-filters, #element-filters, #counter-filters, #table-filters');
        if (!filtersContainer) {
            filtersContainer = document.getElementById('graph-filters') || document.getElementById('element-filters') || document.getElementById('table-filters');
        }
        return DataFilterUtils.getValues(filtersContainer, { visibleOnly: true });
    }

    /**
     * Load existing element for editing
     */
    async loadElement(elementId) {
        this.isLoading = true;

        try {
            const result = await Ajax.post(`load_${this.getElementTypeSlug()}`, { id: elementId });

            if (result.success && result.data) {
                const element = result.data;

                this.elementName = element.name;
                this.elementDescription = element.description || '';

                const nameInput = this.container.querySelector('.graph-name-input, .element-name-input');
                if (nameInput) nameInput.value = element.name;

                const descriptionInput = this.container.querySelector('.graph-description-input, .element-description-input');
                if (descriptionInput) {
                    descriptionInput.value = this.elementDescription;
                    autosize.update(descriptionInput);
                }

                if (this.queryBuilder && element.query) {
                    this.queryBuilder.setQuery(element.query);
                }

                if (element.config) {
                    const config = JSON.parse(element.config);
                    if (config.activeSidebarTab) {
                        this.setActiveSidebarTab(config.activeSidebarTab);
                    }
                    this.loadConfig(config);
                }

                if (this.placeholderSettings) {
                    let placeholderSettings = {};
                    if (element.placeholder_settings) {
                        placeholderSettings = typeof element.placeholder_settings === 'string'
                            ? JSON.parse(element.placeholder_settings)
                            : element.placeholder_settings;
                    } else if (element.config) {
                        const config = typeof element.config === 'string'
                            ? JSON.parse(element.config)
                            : element.config;
                        if (config.placeholderSettings) {
                            placeholderSettings = config.placeholderSettings;
                        }
                    }
                    this.placeholderSettings.setSettings(placeholderSettings);
                }

                if (this.filterManager && element.filters) {
                    this.filterManager.setFilters(element.filters);
                }

                if (this.dataMapper && element.data_mapping) {
                    const mapping = JSON.parse(element.data_mapping);
                    this.dataMapper.setMapping(mapping, false);

                    this.isLoading = false;
                    this.initialLoad = false;

                    if (element.query) {
                        await this.queryBuilder.testQuery();
                    }
                }

                // Load type-specific data
                this.loadTypeSpecificData(element);

                this.updatePlaceholderSettings();
                this.captureState();
                this.setUnsavedChanges(false);
            } else {
                if (this.preview && this.preview.hideSkeleton) {
                    this.preview.hideSkeleton();
                }
                Toast.error(result.message || `Failed to load ${this.getElementTypeName().toLowerCase()}`);
            }
        } catch (error) {
            if (this.preview && this.preview.hideSkeleton) {
                this.preview.hideSkeleton();
            }
            Toast.error(`Failed to load ${this.getElementTypeName().toLowerCase()}`);
            console.error(error);
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Load config - subclasses can override
     */
    loadConfig(config) {
        // Subclasses implement this
    }

    /**
     * Save element
     */
    async save() {
        const errors = [];

        if (this.formValidator && !this.formValidator.validate()) {
            this.expandSidebar();
            errors.push(`${this.getElementTypeName()} name is required`);
        }

        if (!this.validateCategories()) {
            this.expandSidebar();
            errors.push('At least one category is required');
        }

        const query = this.queryBuilder ? this.queryBuilder.getQuery() : '';
        if (!query.trim()) {
            errors.push('SQL query is required');
        }

        // Check if query has been tested successfully
        if (query.trim() && !this.queryTested) {
            errors.push('Query must be tested before saving - click "Test Query" button');
        }

        if (this.mandatoryFilterValidator && query.trim()) {
            const mandatoryValidation = this.mandatoryFilterValidator.validateQuery(query);
            if (!mandatoryValidation.valid) {
                const errorMsg = this.mandatoryFilterValidator.getErrorMessage(mandatoryValidation.missing);
                errors.push(errorMsg);
            }
        }

        // Check for query errors (syntax errors from test)
        if (this.queryError) {
            errors.push('Query has errors - please fix the query before saving');
        }

        // Check for unmatched placeholders (placeholders without assigned filters)
        if (this.placeholderWarnings && this.placeholderWarnings.length > 0) {
            const unmatchedList = this.placeholderWarnings.join(', ');
            errors.push(`Placeholders without assigned filters: ${unmatchedList}`);
        }

        const mapping = this.dataMapper ? this.dataMapper.getMapping() : {};
        if (!this.validateMapping(mapping)) {
            errors.push('Data mapping is incomplete (run query first to see columns)');
        }

        if (errors.length > 0) {
            Toast.error(errors.map(err => '• ' + err).join('<br>'));
            return;
        }

        Loading.show(`Saving ${this.getElementTypeName().toLowerCase()}...`);

        try {
            const config = this.getConfig();
            config.activeSidebarTab = this.activeSidebarTab;

            const placeholderSettings = this.placeholderSettings
                ? this.placeholderSettings.getSettings()
                : {};

            const data = {
                id: this.elementId,
                name: this.elementName,
                description: this.elementDescription,
                query: query,
                data_mapping: mapping,
                config: config,
                placeholder_settings: placeholderSettings,
                filters: this.filterManager ? this.filterManager.getFilters() : [],
                categories: this.selectedCategories || [],
                ...this.getTypeSpecificSaveData()
            };

            const result = await Ajax.post(`save_${this.getElementTypeSlug()}`, data);

            if (result.success) {
                const successMsg = result.message || (this.elementId ? `${this.getElementTypeName()} updated successfully` : `${this.getElementTypeName()} created successfully`);
                Toast.success(successMsg);

                this.captureState();
                this.setUnsavedChanges(false);

                if (!this.elementId && result.data && result.data.id) {
                    window.location.href = `?urlq=${this.getElementTypeSlug()}/edit/${result.data.id}`;
                }
            } else {
                Toast.error(result.message || `Failed to save ${this.getElementTypeName().toLowerCase()}`);
            }
        } catch (error) {
            Toast.error(`Failed to save ${this.getElementTypeName().toLowerCase()}`);
            console.error(error);
        } finally {
            Loading.hide();
        }
    }

    /**
     * Get config - subclasses should override
     */
    getConfig() {
        return {};
    }

    /**
     * Initialize change tracking
     */
    initChangeTracking() {
        const nameInput = this.container.querySelector('.graph-name-input, .element-name-input');
        if (nameInput) {
            nameInput.addEventListener('input', () => this.checkForChanges());
        }

        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });

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
     * Capture current state as saved state
     */
    captureState() {
        this.savedState = this.getCurrentState();
    }

    /**
     * Get current form state
     */
    getCurrentState() {
        return {
            name: this.elementName,
            description: this.elementDescription,
            query: this.queryBuilder ? this.queryBuilder.getQuery() : '',
            mapping: this.dataMapper ? JSON.stringify(this.dataMapper.getMapping()) : '{}',
            config: JSON.stringify(this.getConfig()),
            filters: this.filterManager ? JSON.stringify(this.filterManager.getFilters()) : '[]',
            placeholderSettings: this.placeholderSettings ? JSON.stringify(this.placeholderSettings.getSettings()) : '{}',
            categories: this.selectedCategories ? JSON.stringify(this.selectedCategories.slice().sort()) : '[]'
        };
    }

    /**
     * Check for changes
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
            currentState.query !== this.savedState.query ||
            currentState.mapping !== this.savedState.mapping ||
            currentState.config !== this.savedState.config ||
            currentState.filters !== this.savedState.filters ||
            currentState.placeholderSettings !== this.savedState.placeholderSettings ||
            currentState.categories !== this.savedState.categories;

        this.setUnsavedChanges(hasChanges);
    }

    /**
     * Set unsaved changes state
     */
    setUnsavedChanges(hasChanges) {
        this.hasUnsavedChanges = hasChanges;
        this.updateStatusIndicators();
    }

    /**
     * Escape HTML for tooltips
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Mark state as changed
     */
    markAsChanged() {
        this.checkForChanges();
    }
}
