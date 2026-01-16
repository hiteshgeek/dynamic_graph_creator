/**
 * ConfigPanel - Chart configuration options component
 * Renders different options based on chart type
 */

export default class ConfigPanel {
    constructor(container, options = {}) {
        this.container = container;
        this.onChange = options.onChange || (() => {});

        this.graphType = 'bar';
        this.config = this.getDefaultConfig();
        this.switchIdCounter = 0;
    }

    /**
     * Generate a Bootstrap switch HTML
     * @param {string} configKey - The config key for this switch
     * @param {boolean} isChecked - Whether the switch is checked
     * @returns {string} HTML string
     */
    renderSwitch(configKey, isChecked) {
        const switchId = `config-switch-${configKey}-${++this.switchIdCounter}`;
        return `
            <div class="form-check form-switch custom-switch">
                <input class="form-check-input" type="checkbox" role="switch"
                    id="${switchId}"
                    data-config="${configKey}"
                    data-type="toggle"
                    ${isChecked ? 'checked' : ''}>
            </div>
        `;
    }

    /**
     * Get default configuration
     */
    getDefaultConfig() {
        return {
            // Common
            showLegend: true,
            legendPosition: 'top',
            showTooltip: true,
            colors: [],

            // Bar specific
            orientation: 'vertical',
            stacked: false,
            barWidth: 60,
            showBackground: false,
            borderRadius: 0,

            // Line specific
            smooth: false,
            showArea: false,
            showSymbol: true,
            lineWidth: 2,
            lineStyle: 'solid',

            // Pie specific
            pieType: 'pie',
            innerRadius: 0,
            outerRadius: 70,
            showLabel: true,
            labelPosition: 'outside'
        };
    }

    /**
     * Set graph type and re-render
     */
    setGraphType(type) {
        this.graphType = type;
        this.render();
    }

    /**
     * Set configuration values
     */
    setConfig(config) {
        this.config = { ...this.getDefaultConfig(), ...config };
        this.render();
    }

    /**
     * Get current configuration
     */
    getConfig() {
        return this.config;
    }

    /**
     * Render the config panel
     */
    render() {
        let html = `
            <div class="config-panel-header">
                <h3><i class="fas fa-cog"></i> Configuration</h3>
            </div>
        `;

        // Common options
        html += this.renderCommonOptions();

        // Type-specific options
        switch (this.graphType) {
            case 'bar':
                html += this.renderBarOptions();
                break;
            case 'line':
                html += this.renderLineOptions();
                break;
            case 'pie':
                html += this.renderPieOptions();
                break;
        }

        this.container.innerHTML = html;
        this.bindEvents();
    }

    /**
     * Render common options
     */
    renderCommonOptions() {
        return `
            <div class="config-section">
                <div class="config-section-title">
                    <i class="fas fa-cog"></i> General
                </div>

                <div class="config-row">
                    <span class="config-row-label">Show Legend</span>
                    ${this.renderSwitch('showLegend', this.config.showLegend)}
                </div>

                <div class="config-field">
                    <label>Legend Position</label>
                    <select class="config-select" data-config="legendPosition">
                        <option value="top" ${this.config.legendPosition === 'top' ? 'selected' : ''}>Top</option>
                        <option value="bottom" ${this.config.legendPosition === 'bottom' ? 'selected' : ''}>Bottom</option>
                        <option value="left" ${this.config.legendPosition === 'left' ? 'selected' : ''}>Left</option>
                        <option value="right" ${this.config.legendPosition === 'right' ? 'selected' : ''}>Right</option>
                    </select>
                </div>

                <div class="config-row">
                    <span class="config-row-label">Show Tooltip</span>
                    ${this.renderSwitch('showTooltip', this.config.showTooltip)}
                </div>
            </div>
        `;
    }

    /**
     * Render bar chart options
     */
    renderBarOptions() {
        return `
            <div class="config-section">
                <div class="config-section-title">
                    <i class="fas fa-chart-bar"></i> Bar Options
                </div>

                <div class="config-field">
                    <label>Orientation</label>
                    <select class="config-select" data-config="orientation">
                        <option value="vertical" ${this.config.orientation === 'vertical' ? 'selected' : ''}>Vertical</option>
                        <option value="horizontal" ${this.config.orientation === 'horizontal' ? 'selected' : ''}>Horizontal</option>
                    </select>
                </div>

                <div class="config-row">
                    <span class="config-row-label">Stacked</span>
                    ${this.renderSwitch('stacked', this.config.stacked)}
                </div>

                <div class="config-field">
                    <label>Bar Width (%)</label>
                    <div class="config-slider">
                        <input
                            type="range"
                            min="10"
                            max="100"
                            value="${this.config.barWidth || 60}"
                            data-config="barWidth"
                        >
                        <span class="slider-value">${this.config.barWidth || 60}%</span>
                    </div>
                </div>

                <div class="config-row">
                    <span class="config-row-label">Show Background</span>
                    ${this.renderSwitch('showBackground', this.config.showBackground)}
                </div>

                <div class="config-field">
                    <label>Border Radius</label>
                    <div class="config-slider">
                        <input
                            type="range"
                            min="0"
                            max="20"
                            value="${this.config.borderRadius || 0}"
                            data-config="borderRadius"
                        >
                        <span class="slider-value">${this.config.borderRadius || 0}px</span>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render line chart options
     */
    renderLineOptions() {
        return `
            <div class="config-section">
                <div class="config-section-title">
                    <i class="fas fa-chart-line"></i> Line Options
                </div>

                <div class="config-row">
                    <span class="config-row-label">Smooth Line</span>
                    ${this.renderSwitch('smooth', this.config.smooth)}
                </div>

                <div class="config-row">
                    <span class="config-row-label">Show Area Fill</span>
                    ${this.renderSwitch('showArea', this.config.showArea)}
                </div>

                <div class="config-row">
                    <span class="config-row-label">Show Markers</span>
                    ${this.renderSwitch('showSymbol', this.config.showSymbol)}
                </div>

                <div class="config-field">
                    <label>Line Width</label>
                    <div class="config-slider">
                        <input
                            type="range"
                            min="1"
                            max="10"
                            value="${this.config.lineWidth || 2}"
                            data-config="lineWidth"
                        >
                        <span class="slider-value">${this.config.lineWidth || 2}px</span>
                    </div>
                </div>

                <div class="config-field">
                    <label>Line Style</label>
                    <select class="config-select" data-config="lineStyle">
                        <option value="solid" ${this.config.lineStyle === 'solid' ? 'selected' : ''}>Solid</option>
                        <option value="dashed" ${this.config.lineStyle === 'dashed' ? 'selected' : ''}>Dashed</option>
                        <option value="dotted" ${this.config.lineStyle === 'dotted' ? 'selected' : ''}>Dotted</option>
                    </select>
                </div>
            </div>
        `;
    }

    /**
     * Render pie chart options
     */
    renderPieOptions() {
        return `
            <div class="config-section">
                <div class="config-section-title">
                    <i class="fas fa-chart-pie"></i> Pie Options
                </div>

                <div class="config-field">
                    <label>Pie Type</label>
                    <select class="config-select" data-config="pieType">
                        <option value="pie" ${this.config.pieType === 'pie' ? 'selected' : ''}>Pie</option>
                        <option value="donut" ${this.config.pieType === 'donut' ? 'selected' : ''}>Donut</option>
                    </select>
                </div>

                <div class="config-field" id="inner-radius-field" style="${this.config.pieType !== 'donut' ? 'display:none' : ''}">
                    <label>Inner Radius (%)</label>
                    <div class="config-slider">
                        <input
                            type="range"
                            min="0"
                            max="80"
                            value="${this.config.innerRadius || 40}"
                            data-config="innerRadius"
                        >
                        <span class="slider-value">${this.config.innerRadius || 40}%</span>
                    </div>
                </div>

                <div class="config-field">
                    <label>Outer Radius (%)</label>
                    <div class="config-slider">
                        <input
                            type="range"
                            min="30"
                            max="100"
                            value="${this.config.outerRadius || 70}"
                            data-config="outerRadius"
                        >
                        <span class="slider-value">${this.config.outerRadius || 70}%</span>
                    </div>
                </div>

                <div class="config-row">
                    <span class="config-row-label">Show Labels</span>
                    ${this.renderSwitch('showLabel', this.config.showLabel)}
                </div>

                <div class="config-field">
                    <label>Label Position</label>
                    <select class="config-select" data-config="labelPosition">
                        <option value="outside" ${this.config.labelPosition === 'outside' ? 'selected' : ''}>Outside</option>
                        <option value="inside" ${this.config.labelPosition === 'inside' ? 'selected' : ''}>Inside</option>
                    </select>
                </div>
            </div>
        `;
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
        // Toggle switches (Bootstrap checkbox)
        this.container.querySelectorAll('input[type="checkbox"][data-type="toggle"]').forEach(toggle => {
            toggle.addEventListener('change', () => {
                const key = toggle.dataset.config;
                this.config[key] = toggle.checked;
                this.onChange();
            });
        });

        // Select dropdowns
        this.container.querySelectorAll('select[data-config]').forEach(select => {
            select.addEventListener('change', (e) => {
                const key = e.target.dataset.config;
                this.config[key] = e.target.value;

                // Special handling for pie type
                if (key === 'pieType') {
                    const innerRadiusField = this.container.querySelector('#inner-radius-field');
                    if (innerRadiusField) {
                        innerRadiusField.style.display = e.target.value === 'donut' ? '' : 'none';
                    }
                }

                this.onChange();
            });
        });

        // Range sliders
        this.container.querySelectorAll('input[type="range"][data-config]').forEach(input => {
            input.addEventListener('input', (e) => {
                const key = e.target.dataset.config;
                const value = parseInt(e.target.value);
                this.config[key] = value;

                // Update displayed value
                const valueSpan = e.target.nextElementSibling;
                if (valueSpan) {
                    const suffix = key.includes('Width') || key.includes('Radius') ? '%' : 'px';
                    valueSpan.textContent = value + suffix;
                }
            });

            // Only trigger onChange on mouseup to avoid too many updates
            input.addEventListener('change', () => {
                this.onChange();
            });
        });
    }
}
