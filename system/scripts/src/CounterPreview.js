/**
 * CounterPreview - Counter preview rendering
 * Displays counter card with formatted value
 */

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
        this.cardEl = container.querySelector('.counter-card-display, .counter-card-preview');
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
     * Format value based on config
     */
    formatValue(value) {
        let formatted;

        switch (this.config.format) {
            case 'currency':
                formatted = new Intl.NumberFormat('en-IN', {
                    minimumFractionDigits: this.config.decimals,
                    maximumFractionDigits: this.config.decimals
                }).format(value);
                break;

            case 'percentage':
                formatted = new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: this.config.decimals,
                    maximumFractionDigits: this.config.decimals
                }).format(value) + '%';
                break;

            case 'compact':
                if (value >= 1000000000) {
                    formatted = (value / 1000000000).toFixed(1) + 'B';
                } else if (value >= 1000000) {
                    formatted = (value / 1000000).toFixed(1) + 'M';
                } else if (value >= 1000) {
                    formatted = (value / 1000).toFixed(1) + 'K';
                } else {
                    formatted = value.toFixed(this.config.decimals);
                }
                break;

            case 'number':
            default:
                formatted = new Intl.NumberFormat('en-IN', {
                    minimumFractionDigits: this.config.decimals,
                    maximumFractionDigits: this.config.decimals
                }).format(value);
                break;
        }

        // Add prefix and suffix
        if (this.config.prefix) {
            formatted = this.config.prefix + formatted;
        }
        if (this.config.suffix) {
            formatted = formatted + this.config.suffix;
        }

        return formatted;
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
