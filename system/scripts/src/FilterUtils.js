/**
 * FilterUtils - Utility for extracting filter values from filter containers
 * Provides a single entry point for getting filter values across the app
 */

export default class FilterUtils {
    /**
     * Get all filter values from a container
     * @param {HTMLElement} container - The container element with .filter-input-item children
     * @param {Object} options - Options for filtering
     * @param {boolean} options.visibleOnly - Only get values from visible filters (default: true)
     * @returns {Object} Filter values keyed by placeholder (e.g., {'::category': 'value'})
     */
    static getValues(container, options = {}) {
        const { visibleOnly = true } = options;
        const filterValues = {};

        if (!container) {
            return filterValues;
        }

        const filterItems = container.querySelectorAll('.filter-input-item');

        filterItems.forEach(item => {
            // Skip hidden filters if visibleOnly is true
            if (visibleOnly && item.style.display === 'none') return;

            const filterKey = item.dataset.filterKey;
            if (!filterKey) return;

            const value = FilterUtils.getItemValue(item, filterKey);
            if (value !== null) {
                Object.assign(filterValues, value);
            }
        });

        return filterValues;
    }

    /**
     * Get value from a single filter item
     * @param {HTMLElement} item - The filter item element
     * @param {string} filterKey - The filter key (without :: prefix)
     * @returns {Object|null} Filter value object or null if no value
     */
    static getItemValue(item, filterKey) {
        // Single select
        const select = item.querySelector('select.filter-input');
        if (select && select.value) {
            return { ['::' + filterKey]: select.value };
        }

        // Multi-select dropdown (checkboxes)
        const multiSelectChecked = item.querySelectorAll('.filter-multiselect-options input[type="checkbox"]:checked');
        if (multiSelectChecked.length > 0) {
            const values = Array.from(multiSelectChecked).map(cb => cb.value);
            return { ['::' + filterKey]: values };
        }

        // Checkbox group
        const checkboxChecked = item.querySelectorAll('.filter-checkbox-group input[type="checkbox"]:checked');
        if (checkboxChecked.length > 0) {
            const values = Array.from(checkboxChecked).map(cb => cb.value);
            return { ['::' + filterKey]: values };
        }

        // Radio group
        const radioChecked = item.querySelector('.filter-radio-group input[type="radio"]:checked');
        if (radioChecked) {
            return { ['::' + filterKey]: radioChecked.value };
        }

        // Date range picker (single input with data attributes)
        const dateRangePicker = item.querySelector('.dgc-datepicker[data-picker-type="range"]');
        if (dateRangePicker) {
            const from = dateRangePicker.dataset.from;
            const to = dateRangePicker.dataset.to;

            if (from || to) {
                const result = {};
                if (from) result['::' + filterKey + '_from'] = from;
                if (to) result['::' + filterKey + '_to'] = to;
                return result;
            }
            return null;
        }

        // Single date picker
        const singleDatePicker = item.querySelector('.dgc-datepicker[data-picker-type="single"]');
        if (singleDatePicker && singleDatePicker.value) {
            return { ['::' + filterKey]: singleDatePicker.value };
        }

        // Text/number input (non-datepicker)
        const textInput = item.querySelector('input.filter-input:not(.dgc-datepicker)');
        if (textInput && textInput.value) {
            return { ['::' + filterKey]: textInput.value };
        }

        // Legacy: Date range with two separate inputs (fallback)
        const dateFrom = item.querySelector('input[name$="_from"]');
        const dateTo = item.querySelector('input[name$="_to"]');
        if (dateFrom && dateTo) {
            const result = {};
            if (dateFrom.value) result['::' + filterKey + '_from'] = dateFrom.value;
            if (dateTo.value) result['::' + filterKey + '_to'] = dateTo.value;
            if (Object.keys(result).length > 0) {
                return result;
            }
        }

        return null;
    }
}
