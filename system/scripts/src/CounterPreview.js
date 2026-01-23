/**
 * CounterPreview - Counter preview rendering
 * Displays counter card with formatted value
 * Uses shared CounterFormatter for value formatting
 */
import { CounterFormatter } from './CounterFormatter.js';

export default class CounterPreview {
    constructor(container, options = {}) {
        this.container = container;
        this.value = options.value || 0;
        this.config = {
            icon: options.icon || 'trending_up',
            color: options.color || '#4CAF50',
            format: options.format || 'number',
            prefix: options.prefix || '',
            suffix: options.suffix || '',
            decimals: options.decimals || 0
        };

        this.valueEl = container.querySelector('.counter-value');
        this.iconEl = container.querySelector('.counter-icon .material-icons');
        this.cardEl = container.querySelector('.counter-card-display, .counter-card-preview, .counter-card');
    }

    /**
     * Set counter value
     */
    setValue(value) {
        this.value = parseFloat(value) || 0;
        this.render();
    }

    /**
     * Set counter config
     */
    setConfig(config) {
        this.config = { ...this.config, ...config };
        this.updateStyles();
    }

    /**
     * Show loading state with skeleton
     */
    showLoading() {
        if (this.cardEl) {
            this.cardEl.classList.add('is-loading');
        }
        if (this.valueEl) {
            this.valueEl.innerHTML = '<span class="counter-skeleton-value"></span>';
        }
        if (this.iconEl) {
            this.iconEl.classList.add('counter-skeleton-icon');
        }
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        if (this.cardEl) {
            this.cardEl.classList.remove('is-loading');
        }
        if (this.iconEl) {
            this.iconEl.classList.remove('counter-skeleton-icon');
        }
    }

    /**
     * Update card styles
     */
    updateStyles() {
        if (this.cardEl && this.config.color) {
            this.cardEl.style.backgroundColor = this.config.color;
        }
        if (this.iconEl && this.config.icon) {
            this.iconEl.textContent = this.config.icon;
        }
    }

    /**
     * Format value based on config using shared CounterFormatter
     */
    formatValue(value) {
        return CounterFormatter.format(value, this.config);
    }

    /**
     * Render counter
     */
    render() {
        this.hideLoading();
        if (this.valueEl) {
            this.valueEl.textContent = this.formatValue(this.value);
        }
        this.updateStyles();
    }
}
