/**
 * PlaceholderSettings - Manages empty filter behavior per placeholder
 * Shows a table of placeholders with checkbox for "allow empty"
 */

export default class PlaceholderSettings {
    constructor(container, options = {}) {
        this.container = container;
        this.onChange = options.onChange || (() => {});
        this.getMatchedFilters = options.getMatchedFilters || (() => ({}));

        this.section = null;
        this.tbody = null;
        this.placeholders = [];
        this.settings = {}; // { "::placeholder": { allowEmpty: true } }

        this.init();
    }

    /**
     * Initialize placeholder settings
     */
    init() {
        this.section = this.container.querySelector('.placeholder-settings-section');
        this.tbody = this.container.querySelector('#placeholder-settings-body');
    }

    /**
     * Update placeholders from query
     * @param {Array} placeholders - Array of placeholder keys like ['::category', '::year']
     * @param {Object} matchedFilters - Object mapping placeholder keys to filter info
     */
    setPlaceholders(placeholders, matchedFilters = {}) {
        this.placeholders = placeholders;

        // Initialize settings for new placeholders (default: allow empty = true)
        placeholders.forEach(placeholder => {
            if (!this.settings[placeholder]) {
                this.settings[placeholder] = { allowEmpty: true };
            }
        });

        // Remove settings for placeholders no longer in query
        Object.keys(this.settings).forEach(key => {
            if (!placeholders.includes(key)) {
                delete this.settings[key];
            }
        });

        this.render(matchedFilters);
    }

    /**
     * Render the settings table
     */
    render(matchedFilters = {}) {
        if (!this.tbody || !this.section) return;

        // Show/hide section based on placeholders
        if (this.placeholders.length === 0) {
            this.section.style.display = 'none';
            return;
        }

        this.section.style.display = 'block';

        // Build table rows
        let html = '';
        this.placeholders.forEach(placeholder => {
            const filter = matchedFilters[placeholder];
            const filterLabel = filter ? filter.filter_label : '<span class="text-muted">Not linked</span>';
            const isAllowEmpty = this.settings[placeholder]?.allowEmpty !== false;
            const checkboxId = `allow-empty-${placeholder.replace(/::/g, '')}`;

            html += `
                <tr data-placeholder="${this.escapeHtml(placeholder)}">
                    <td><code>${this.escapeHtml(placeholder)}</code></td>
                    <td>${filter ? this.escapeHtml(filterLabel) : filterLabel}</td>
                    <td>
                        <div class="form-check">
                            <input class="form-check-input allow-empty-checkbox" type="checkbox"
                                   id="${checkboxId}"
                                   data-placeholder="${this.escapeHtml(placeholder)}"
                                   ${isAllowEmpty ? 'checked' : ''}>
                            <label class="form-check-label" for="${checkboxId}">
                                ${isAllowEmpty ? 'Yes' : 'No'}
                            </label>
                        </div>
                    </td>
                </tr>
            `;
        });

        this.tbody.innerHTML = html;
        this.bindEvents();
    }

    /**
     * Bind checkbox change events
     */
    bindEvents() {
        const checkboxes = this.tbody.querySelectorAll('.allow-empty-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const placeholder = e.target.dataset.placeholder;
                const allowEmpty = e.target.checked;

                // Update settings
                if (!this.settings[placeholder]) {
                    this.settings[placeholder] = {};
                }
                this.settings[placeholder].allowEmpty = allowEmpty;

                // Update label
                const label = e.target.nextElementSibling;
                if (label) {
                    label.textContent = allowEmpty ? 'Yes' : 'No';
                }

                this.onChange();
            });
        });
    }

    /**
     * Get current settings
     * @returns {Object} Settings object
     */
    getSettings() {
        return this.settings;
    }

    /**
     * Set settings (for loading saved graph)
     * @param {Object} settings - Settings object
     */
    setSettings(settings) {
        if (settings && typeof settings === 'object') {
            this.settings = { ...settings };
        }
    }

    /**
     * Check if a placeholder requires a value (allowEmpty = false)
     * @param {string} placeholder - Placeholder key
     * @returns {boolean}
     */
    isRequired(placeholder) {
        return this.settings[placeholder]?.allowEmpty === false;
    }

    /**
     * Get list of required placeholders that are empty
     * @param {Object} filterValues - Current filter values
     * @returns {Array} Array of missing required placeholder keys
     */
    getMissingRequired(filterValues = {}) {
        const missing = [];

        Object.keys(this.settings).forEach(placeholder => {
            if (this.settings[placeholder].allowEmpty === false) {
                const value = filterValues[placeholder];
                const isEmpty = value === undefined || value === null || value === '' ||
                    (Array.isArray(value) && value.length === 0);

                if (isEmpty) {
                    missing.push(placeholder);
                }
            }
        });

        return missing;
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(value) {
        if (value === null || value === undefined) return '';
        const str = String(value);
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}
