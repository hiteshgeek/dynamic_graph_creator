/**
 * MandatoryFilterValidator - Reusable class for handling mandatory filters
 * Works with any widget type (graph, table, list, counter, link)
 */

const Toast = window.Toast;

export default class MandatoryFilterValidator {
  /**
   * Create a new MandatoryFilterValidator
   * @param {HTMLElement} container - Container element with data-mandatory-filters and data-widget-type
   * @param {Object} options - Configuration options
   */
  constructor(container, options = {}) {
    this.container = container;
    this.options = options;

    // Parse mandatory filter keys from container data attribute
    this.mandatoryFilterKeys = this.parseMandatoryFilters();
    this.widgetType = container.dataset.widgetType || 'graph';
  }

  /**
   * Parse mandatory filter keys from container's data attribute
   * @returns {Array} Array of mandatory filter keys (without :: prefix)
   */
  parseMandatoryFilters() {
    const dataAttr = this.container.dataset.mandatoryFilters;
    if (!dataAttr) return [];

    try {
      const keys = JSON.parse(dataAttr);
      return Array.isArray(keys) ? keys : [];
    } catch (e) {
      console.warn('Failed to parse mandatory filters:', e);
      return [];
    }
  }

  /**
   * Check if a filter key is mandatory
   * @param {string} filterKey - Filter key (with or without :: prefix)
   * @returns {boolean}
   */
  isMandatory(filterKey) {
    const cleanKey = filterKey.replace(/^::/, '');
    return this.mandatoryFilterKeys.includes(cleanKey);
  }

  /**
   * Get all mandatory filter keys
   * @returns {Array} Array of filter keys (without :: prefix)
   */
  getMandatoryKeys() {
    return [...this.mandatoryFilterKeys];
  }

  /**
   * Get mandatory filter keys with :: prefix
   * @returns {Array} Array of placeholders (e.g., ['::company_list', '::year'])
   */
  getMandatoryPlaceholders() {
    return this.mandatoryFilterKeys.map(key => '::' + key);
  }

  /**
   * Validate that a query contains all mandatory filter placeholders
   * @param {string} query - SQL query to validate
   * @returns {Object} { valid: boolean, missing: Array of missing filter keys }
   */
  validateQuery(query) {
    if (!query || this.mandatoryFilterKeys.length === 0) {
      return { valid: true, missing: [] };
    }

    const missing = [];
    const queryLower = query.toLowerCase();

    // Get date range filter types from DOM for _from/_to handling
    const dateRangeTypes = ['date_range', 'main_datepicker'];

    for (const key of this.mandatoryFilterKeys) {
      const placeholder = '::' + key;
      const placeholderLower = placeholder.toLowerCase();

      // Check if this is a date range filter
      const filterItem = this.container.querySelector(`[data-filter-key="${key}"]`);
      const filterType = filterItem?.dataset.filterType;
      const isDateRange = dateRangeTypes.includes(filterType);

      if (isDateRange) {
        // For date range filters, check for _from and _to variants
        const fromPlaceholder = placeholderLower + '_from';
        const toPlaceholder = placeholderLower + '_to';

        if (!queryLower.includes(fromPlaceholder) && !queryLower.includes(toPlaceholder)) {
          missing.push(key);
        }
      } else {
        // Standard filter - check for exact placeholder
        if (!queryLower.includes(placeholderLower)) {
          missing.push(key);
        }
      }
    }

    return {
      valid: missing.length === 0,
      missing: missing
    };
  }

  /**
   * Get validation error message for missing mandatory filters
   * @param {Array} missingKeys - Array of missing filter keys
   * @returns {string} Error message
   */
  getErrorMessage(missingKeys) {
    if (missingKeys.length === 0) return '';

    const placeholders = missingKeys.map(k => '::' + k).join(', ');

    if (missingKeys.length === 1) {
      return `Query must include mandatory filter: ${placeholders}`;
    }
    return `Query must include mandatory filters: ${placeholders}`;
  }

  /**
   * Initialize filter selector to auto-select mandatory filters
   * @param {Function} onSelectionChange - Callback when selection changes
   */
  initFilterSelector(onSelectionChange) {
    const checkboxes = this.container.querySelectorAll('.filter-selector-checkbox');

    checkboxes.forEach(checkbox => {
      const filterKey = checkbox.value;

      if (this.isMandatory(filterKey)) {
        // Auto-select mandatory filters
        checkbox.checked = true;
        checkbox.disabled = true;

        // Add click handler to show toast if user tries to uncheck
        const item = checkbox.closest('.filter-selector-item');
        if (item) {
          item.addEventListener('click', (e) => {
            if (e.target !== checkbox && !e.target.closest('label')) {
              Toast.info('This filter is mandatory and cannot be removed');
            }
          });
        }
      }
    });

    // Trigger callback with initial selection
    if (onSelectionChange) {
      onSelectionChange(this.getSelectedFilters());
    }
  }

  /**
   * Get currently selected filter keys
   * @returns {Array} Array of selected filter keys
   */
  getSelectedFilters() {
    const selected = [];
    const checkboxes = this.container.querySelectorAll('.filter-selector-checkbox:checked');

    checkboxes.forEach(cb => {
      selected.push(cb.value);
    });

    // Ensure mandatory filters are always included
    for (const key of this.mandatoryFilterKeys) {
      if (!selected.includes(key)) {
        selected.push(key);
      }
    }

    return selected;
  }

  /**
   * Ensure mandatory filters are always in the selected list
   * @param {Array} selectedFilters - Current selected filter keys
   * @returns {Array} Updated list with mandatory filters included
   */
  ensureMandatorySelected(selectedFilters) {
    const result = [...selectedFilters];

    for (const key of this.mandatoryFilterKeys) {
      if (!result.includes(key)) {
        result.unshift(key); // Add to beginning
      }
    }

    return result;
  }

  /**
   * Validate before save - combines query validation with other checks
   * @param {string} query - SQL query
   * @param {Object} options - { showToast: boolean }
   * @returns {Object} { valid: boolean, errors: Array }
   */
  validateBeforeSave(query, options = { showToast: true }) {
    const errors = [];

    // Validate mandatory filters in query
    const queryValidation = this.validateQuery(query);
    if (!queryValidation.valid) {
      const errorMsg = this.getErrorMessage(queryValidation.missing);
      errors.push(errorMsg);

      if (options.showToast) {
        Toast.error(errorMsg);
      }
    }

    return {
      valid: errors.length === 0,
      errors: errors
    };
  }
}
