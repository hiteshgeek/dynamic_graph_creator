/**
 * CounterFormatter - Shared utility for formatting counter values
 * Used by CounterPreview, WidgetLoader, and dashboard.js
 */

export class CounterFormatter {
  /**
   * Default formatting options
   */
  static get defaultOptions() {
    return {
      format: 'number',
      locale: 'en-IN',
      decimals: 0,
      prefix: '',
      suffix: ''
    };
  }

  /**
   * Format a counter value based on config options
   * @param {number} value - The numeric value to format
   * @param {Object} options - Formatting options
   * @param {string} options.format - Format type: 'number', 'currency', 'percentage', 'compact'
   * @param {string} options.locale - Locale for number formatting (default: 'en-IN')
   * @param {number} options.decimals - Number of decimal places (default: 0)
   * @param {string} options.prefix - Prefix to add before value
   * @param {string} options.suffix - Suffix to add after value
   * @returns {string} Formatted value string
   */
  static format(value, options = {}) {
    const opts = { ...this.defaultOptions, ...options };
    const numValue = parseFloat(value) || 0;
    let formatted;

    switch (opts.format) {
      case 'currency':
        formatted = new Intl.NumberFormat(opts.locale, {
          minimumFractionDigits: opts.decimals,
          maximumFractionDigits: opts.decimals
        }).format(numValue);
        break;

      case 'percentage':
        formatted = new Intl.NumberFormat(opts.locale, {
          minimumFractionDigits: opts.decimals,
          maximumFractionDigits: opts.decimals
        }).format(numValue) + '%';
        break;

      case 'compact':
        formatted = this.formatCompact(numValue, opts.decimals);
        break;

      case 'number':
      default:
        formatted = new Intl.NumberFormat(opts.locale, {
          minimumFractionDigits: opts.decimals,
          maximumFractionDigits: opts.decimals
        }).format(numValue);
        break;
    }

    // Add prefix and suffix
    if (opts.prefix) {
      formatted = opts.prefix + formatted;
    }
    if (opts.suffix) {
      formatted = formatted + opts.suffix;
    }

    return formatted;
  }

  /**
   * Format number in compact notation (K, M, B)
   * @param {number} value - The numeric value
   * @param {number} decimals - Decimal places
   * @returns {string} Compact formatted string
   */
  static formatCompact(value, decimals = 1) {
    if (value >= 1000000000) {
      return (value / 1000000000).toFixed(decimals) + 'B';
    } else if (value >= 1000000) {
      return (value / 1000000).toFixed(decimals) + 'M';
    } else if (value >= 1000) {
      return (value / 1000).toFixed(decimals) + 'K';
    } else {
      return value.toFixed(decimals);
    }
  }

  /**
   * Render counter card HTML
   * @param {Object} data - Counter data
   * @param {number} data.value - Counter value
   * @param {string} data.name - Counter name
   * @param {string} data.icon - Material icon name
   * @param {string} data.color - Background color
   * @param {Object} data.config - Formatting config
   * @param {string} size - Size variant: 'default' or 'compact'
   * @returns {string} HTML string
   */
  static renderCard(data, size = 'compact') {
    const { value = 0, name = 'Counter', icon = 'analytics', color = '#4361ee', config = {} } = data;
    const displayValue = this.format(value, config);
    const sizeClass = size === 'default' ? 'counter-card--default' : 'counter-card--compact';

    return `
      <div class="counter-card ${sizeClass}" style="background: ${color};">
        <div class="counter-icon">
          <span class="material-icons">${icon}</span>
        </div>
        <div class="counter-content">
          <div class="counter-value">${displayValue}</div>
          <div class="counter-name">${this.escapeHtml(name)}</div>
        </div>
      </div>
    `;
  }

  /**
   * Render counter skeleton HTML
   * @param {string} color - Background color
   * @param {string} icon - Material icon name
   * @param {string} size - Size variant: 'default' or 'compact'
   * @returns {string} HTML string
   */
  static renderSkeleton(color = '#4361ee', icon = 'analytics', size = 'compact') {
    const sizeClass = size === 'default' ? 'counter-skeleton--default' : 'counter-skeleton--compact';

    return `
      <div class="counter-skeleton ${sizeClass}" style="background: ${color};">
        <div class="counter-skeleton-icon">
          <span class="material-icons">${icon}</span>
        </div>
        <div class="counter-skeleton-content">
          <div class="counter-skeleton-value"></div>
          <div class="counter-skeleton-name"></div>
        </div>
      </div>
    `;
  }

  /**
   * Render counter error HTML
   * @param {string} message - Error message
   * @param {string} size - Size variant: 'default' or 'compact'
   * @returns {string} HTML string
   */
  static renderError(message = 'Failed to load counter', size = 'compact') {
    const sizeClass = size === 'default' ? 'counter-error--default' : 'counter-error--compact';

    return `
      <div class="counter-error ${sizeClass}">
        <i class="fas fa-exclamation-triangle"></i>
        <span>${this.escapeHtml(message)}</span>
      </div>
    `;
  }

  /**
   * Escape HTML special characters
   * @param {string} str - String to escape
   * @returns {string} Escaped string
   */
  static escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
}

// Export for non-module usage
window.CounterFormatter = CounterFormatter;

export default CounterFormatter;
