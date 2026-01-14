/**
 * GraphExporter - Reusable chart export component
 * Captures ECharts instance as image and downloads
 */

export default class GraphExporter {
    constructor(options = {}) {
        this.chart = options.chart || null;
        this.filename = options.filename || 'chart';
        this.container = options.container || null;
        this.buttonClass = options.buttonClass || 'btn btn-sm btn-outline';
        this.showLabel = options.showLabel !== false;

        if (this.container) {
            this.render();
        }
    }

    /**
     * Set the ECharts instance
     */
    setChart(chart) {
        this.chart = chart;
    }

    /**
     * Set the filename for download
     */
    setFilename(filename) {
        this.filename = filename || 'chart';
    }

    /**
     * Render export button into container
     */
    render() {
        if (!this.container) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = this.buttonClass;
        button.innerHTML = this.showLabel
            ? '<i class="fas fa-download"></i> Export'
            : '<i class="fas fa-download"></i>';
        button.title = 'Download chart as image';

        button.addEventListener('click', () => this.exportImage());

        this.container.appendChild(button);
        this.button = button;
    }

    /**
     * Create button element (for manual placement)
     */
    createButton() {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = this.buttonClass;
        button.innerHTML = this.showLabel
            ? '<i class="fas fa-download"></i> Export'
            : '<i class="fas fa-download"></i>';
        button.title = 'Download chart as image';

        button.addEventListener('click', () => this.exportImage());

        this.button = button;
        return button;
    }

    /**
     * Export chart as PNG image
     */
    exportImage(format = 'png') {
        if (!this.chart) {
            console.error('No chart instance set');
            return;
        }

        try {
            // Get data URL from ECharts
            const dataUrl = this.chart.getDataURL({
                type: format,
                pixelRatio: 2, // Higher resolution
                backgroundColor: '#fff'
            });

            // Create download link
            const link = document.createElement('a');
            link.download = `${this.sanitizeFilename(this.filename)}.${format}`;
            link.href = dataUrl;

            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

        } catch (error) {
            console.error('Failed to export chart:', error);
        }
    }

    /**
     * Export as SVG (vector format)
     */
    exportSvg() {
        if (!this.chart) {
            console.error('No chart instance set');
            return;
        }

        try {
            const dataUrl = this.chart.getDataURL({
                type: 'svg'
            });

            const link = document.createElement('a');
            link.download = `${this.sanitizeFilename(this.filename)}.svg`;
            link.href = dataUrl;

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

        } catch (error) {
            console.error('Failed to export chart as SVG:', error);
        }
    }

    /**
     * Sanitize filename
     */
    sanitizeFilename(name) {
        return String(name)
            .replace(/[^a-z0-9\-_\s]/gi, '')
            .replace(/\s+/g, '_')
            .substring(0, 100) || 'chart';
    }

    /**
     * Enable/disable button
     */
    setEnabled(enabled) {
        if (this.button) {
            this.button.disabled = !enabled;
        }
    }

    /**
     * Destroy component
     */
    destroy() {
        if (this.button && this.button.parentNode) {
            this.button.parentNode.removeChild(this.button);
        }
        this.button = null;
        this.chart = null;
    }
}
