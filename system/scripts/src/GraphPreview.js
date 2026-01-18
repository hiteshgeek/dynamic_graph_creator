/**
 * GraphPreview - ECharts rendering component
 * Handles chart preview with dummy or real data
 */

export default class GraphPreview {
    constructor(container) {
        this.container = container;
        this.chart = null;
        this.type = 'bar';
        this.config = {};
        this.mapping = {};
        this.data = null;

        this.init();
    }

    /**
     * Initialize ECharts instance
     */
    init() {
        if (typeof echarts !== 'undefined') {
            this.chart = echarts.init(this.container);

            // Handle resize
            window.addEventListener('resize', () => {
                if (this.chart) {
                    this.chart.resize();
                }
            });

            // Listen for theme changes
            this.observeThemeChanges();
        } else {
            console.error('ECharts not loaded');
        }
    }

    /**
     * Observe theme changes and re-render chart
     */
    observeThemeChanges() {
        // Watch for class changes on html element
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    // Re-render chart when theme changes
                    if (this.data) {
                        this.render();
                    }
                }
            });
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    /**
     * Check if dark mode is active
     */
    isDarkMode() {
        return document.documentElement.classList.contains('theme-dark');
    }

    /**
     * Get text color based on current theme
     */
    getTextColor() {
        return this.isDarkMode() ? 'rgba(255, 255, 255, 0.87)' : '#333333';
    }

    /**
     * Get secondary text color based on current theme
     */
    getSecondaryTextColor() {
        return this.isDarkMode() ? 'rgba(255, 255, 255, 0.6)' : '#666666';
    }

    /**
     * Get border/line color based on current theme
     */
    getBorderColor() {
        return this.isDarkMode() ? '#3d3d3d' : '#ccc';
    }

    /**
     * Set chart type
     */
    setType(type) {
        this.type = type;
    }

    /**
     * Set chart configuration
     */
    setConfig(config) {
        this.config = config || {};
    }

    /**
     * Set data mapping (includes axis titles)
     */
    setMapping(mapping) {
        this.mapping = mapping || {};
    }

    /**
     * Set chart data
     */
    setData(data) {
        this.data = data;
    }

    /**
     * Set callback to be called after render
     */
    onRender(callback) {
        this.renderCallback = callback;
    }

    /**
     * Render chart with current data and config
     */
    render() {
        if (!this.chart || !this.data) return;

        const option = this.buildOption();
        this.chart.setOption(option, true);

        // Force resize to fit container
        this.chart.resize();

        // Notify listeners that chart was rendered
        if (this.renderCallback) {
            this.renderCallback();
        }
    }

    /**
     * Show chart with dummy data
     */
    showDummyData(type) {
        this.type = type || this.type;
        this.data = this.getDummyData(this.type);
        this.render();
    }

    /**
     * Get dummy data for chart type
     */
    getDummyData(type) {
        switch (type) {
            case 'bar':
            case 'line':
                return {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    values: [120, 200, 150, 80, 70, 110]
                };
            case 'pie':
                return {
                    items: [
                        { name: 'Category A', value: 335 },
                        { name: 'Category B', value: 234 },
                        { name: 'Category C', value: 154 },
                        { name: 'Category D', value: 135 },
                        { name: 'Category E', value: 98 }
                    ]
                };
            default:
                return null;
        }
    }

    /**
     * Build ECharts option based on type and config
     */
    buildOption() {
        const textColor = this.getTextColor();
        const secondaryTextColor = this.getSecondaryTextColor();
        const borderColor = this.getBorderColor();

        const baseOption = {
            tooltip: {
                trigger: this.type === 'pie' ? 'item' : 'axis',
                backgroundColor: this.isDarkMode() ? '#2d2d2d' : '#fff',
                borderColor: borderColor,
                textStyle: {
                    color: textColor
                }
            },
            color: this.getColors()
        };

        // Add title if configured
        if (this.config.title) {
            baseOption.title = {
                text: this.config.title,
                left: 'center',
                textStyle: {
                    color: textColor
                }
            };
        }

        // Add legend if configured
        if (this.config.showLegend !== false) {
            const legendPosition = this.config.legendPosition || 'top';
            let legendIcon = this.config.legendIcon || 'circle';

            // Square uses 'rect' icon with equal dimensions
            const isSquare = legendIcon === 'square';
            if (isSquare) {
                legendIcon = 'rect';
            }

            // Set dimensions based on icon type
            const itemWidth = (legendIcon === 'rect' && !isSquare) ? 18 : 10;
            const itemHeight = 10;

            const legendConfig = {
                show: true,
                icon: legendIcon,
                itemWidth: itemWidth,
                itemHeight: itemHeight,
                textStyle: {
                    color: textColor
                }
            };

            // Set legend orientation and position
            if (legendPosition === 'left' || legendPosition === 'right') {
                legendConfig.orient = 'vertical';
                legendConfig[legendPosition] = 10;
                legendConfig.top = 'center';
            } else {
                legendConfig.orient = 'horizontal';
                legendConfig[legendPosition] = legendPosition === 'bottom' ? 15 : 10;
                legendConfig.left = 'center';
            }

            baseOption.legend = legendConfig;
        }

        // Build type-specific option
        switch (this.type) {
            case 'bar':
                return this.buildBarOption(baseOption);
            case 'line':
                return this.buildLineOption(baseOption);
            case 'pie':
                return this.buildPieOption(baseOption);
            default:
                return baseOption;
        }
    }

    /**
     * Build bar chart option
     */
    buildBarOption(baseOption) {
        const isHorizontal = this.config.orientation === 'horizontal';
        const xAxisTitle = this.mapping.x_axis_title || '';
        const yAxisTitle = this.mapping.y_axis_title || '';
        const textColor = this.getTextColor();
        const secondaryTextColor = this.getSecondaryTextColor();
        const borderColor = this.getBorderColor();

        const categoryAxis = {
            type: 'category',
            data: this.data.categories || [],
            name: isHorizontal ? yAxisTitle : xAxisTitle,
            nameLocation: 'middle',
            nameGap: 25,
            nameTextStyle: {
                color: textColor
            },
            axisLabel: {
                color: secondaryTextColor
            },
            axisLine: {
                lineStyle: {
                    color: borderColor
                }
            }
        };

        const valueAxis = {
            type: 'value',
            name: isHorizontal ? xAxisTitle : yAxisTitle,
            nameLocation: 'middle',
            nameGap: 50,
            nameRotate: 90,
            nameTextStyle: {
                color: textColor
            },
            axisLabel: {
                color: secondaryTextColor
            },
            axisLine: {
                lineStyle: {
                    color: borderColor
                }
            },
            splitLine: {
                lineStyle: {
                    color: borderColor
                }
            }
        };

        return {
            ...baseOption,
            grid: {
                left: yAxisTitle ? '8%' : '3%',
                right: '4%',
                bottom: xAxisTitle ? '12%' : '3%',
                containLabel: true
            },
            xAxis: isHorizontal ? valueAxis : categoryAxis,
            yAxis: isHorizontal ? categoryAxis : valueAxis,
            series: [{
                type: 'bar',
                name: this.mapping.y_column || yAxisTitle || 'Value',
                data: this.data.values || [],
                barWidth: this.config.barWidth ? `${this.config.barWidth}%` : '60%',
                showBackground: this.config.showBackground || false,
                backgroundStyle: {
                    color: 'rgba(180, 180, 180, 0.2)'
                },
                itemStyle: {
                    borderRadius: this.config.borderRadius || 0
                }
            }]
        };
    }

    /**
     * Build line chart option
     */
    buildLineOption(baseOption) {
        const xAxisTitle = this.mapping.x_axis_title || '';
        const yAxisTitle = this.mapping.y_axis_title || '';
        const textColor = this.getTextColor();
        const secondaryTextColor = this.getSecondaryTextColor();
        const borderColor = this.getBorderColor();

        return {
            ...baseOption,
            grid: {
                left: yAxisTitle ? '8%' : '3%',
                right: '4%',
                bottom: xAxisTitle ? '12%' : '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: this.data.categories || [],
                boundaryGap: false,
                name: xAxisTitle,
                nameLocation: 'middle',
                nameGap: 25,
                nameTextStyle: {
                    color: textColor
                },
                axisLabel: {
                    color: secondaryTextColor
                },
                axisLine: {
                    lineStyle: {
                        color: borderColor
                    }
                }
            },
            yAxis: {
                type: 'value',
                name: yAxisTitle,
                nameLocation: 'middle',
                nameGap: 50,
                nameRotate: 90,
                nameTextStyle: {
                    color: textColor
                },
                axisLabel: {
                    color: secondaryTextColor
                },
                axisLine: {
                    lineStyle: {
                        color: borderColor
                    }
                },
                splitLine: {
                    lineStyle: {
                        color: borderColor
                    }
                }
            },
            series: [{
                type: 'line',
                name: this.mapping.y_column || yAxisTitle || 'Value',
                data: this.data.values || [],
                smooth: this.config.smooth || false,
                showSymbol: this.config.showSymbol !== false,
                symbolSize: 8,
                lineStyle: {
                    width: this.config.lineWidth || 2,
                    type: this.config.lineStyle || 'solid'
                },
                areaStyle: this.config.showArea ? {
                    opacity: 0.3
                } : null
            }]
        };
    }

    /**
     * Build pie chart option
     */
    buildPieOption(baseOption) {
        const isDonut = this.config.pieType === 'donut';
        const textColor = this.getTextColor();
        const borderColor = this.getBorderColor();
        const showLegend = this.config.showLegend !== false;
        const legendPosition = this.config.legendPosition || 'top';
        const showLabels = this.config.showLabel !== false;
        const labelPosition = this.config.labelPosition || 'outside';

        // Base radius values
        let innerRadius = isDonut ? (this.config.innerRadius || 40) : 0;
        let outerRadius = this.config.outerRadius || 70;

        // Slightly reduce radius when labels are outside to make room
        if (showLabels && labelPosition === 'outside') {
            outerRadius = Math.min(outerRadius, 65);
            if (isDonut) {
                innerRadius = Math.min(innerRadius, 35);
            }
        }

        // Adjust center position based on legend position to avoid overlap
        let centerX = '50%';
        let centerY = '50%';

        if (showLegend) {
            if (legendPosition === 'top') {
                centerY = '55%';
            } else if (legendPosition === 'bottom') {
                centerY = '45%';
            } else if (legendPosition === 'left') {
                centerX = '55%';
            } else if (legendPosition === 'right') {
                centerX = '45%';
            }
        }

        return {
            ...baseOption,
            series: [{
                type: 'pie',
                radius: [`${innerRadius}%`, `${outerRadius}%`],
                center: [centerX, centerY],
                data: this.data.items || [],
                label: {
                    show: showLabels,
                    position: labelPosition,
                    formatter: '{b}: {d}%',
                    color: textColor
                },
                labelLine: {
                    show: showLabels && labelPosition !== 'inside',
                    lineStyle: {
                        color: borderColor
                    }
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }]
        };
    }

    /**
     * Get chart colors from config or defaults
     */
    getColors() {
        if (this.config.colors && this.config.colors.length > 0) {
            return this.config.colors;
        }

        // Material design colors
        return [
            '#1976D2', '#4CAF50', '#FF9800', '#F44336',
            '#9C27B0', '#00BCD4', '#795548', '#607D8B'
        ];
    }

    /**
     * Resize chart
     */
    resize() {
        if (this.chart) {
            this.chart.resize();
        }
    }

    /**
     * Destroy chart instance
     */
    destroy() {
        if (this.chart) {
            this.chart.dispose();
            this.chart = null;
        }
    }
}
