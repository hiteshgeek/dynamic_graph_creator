/**
 * GraphExporter - Reusable chart export component
 * Captures ECharts instance as image and downloads or saves to database
 */

export default class GraphExporter {
    constructor(options = {}) {
        this.chart = options.chart || null;
        this.filename = options.filename || 'chart';
        this.container = options.container || null;
        this.graphId = options.graphId || null;
        this.buttonClass = options.buttonClass || 'btn btn-sm btn-outline-secondary';
        this.showLabel = options.showLabel !== false;
        this.onSaveSuccess = options.onSaveSuccess || null;
        this.onSaveError = options.onSaveError || null;

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
     * Set the graph ID for database save
     */
    setGraphId(graphId) {
        this.graphId = graphId;
    }

    /**
     * Set the filename for download
     */
    setFilename(filename) {
        this.filename = filename || 'chart';
    }

    /**
     * Render export dropdown into container
     * Returns the dropdown wrapper element
     */
    render() {
        const wrapper = document.createElement('div');
        wrapper.className = 'btn-group';
        wrapper.innerHTML = `
            <button type="button" class="${this.buttonClass}" id="export-chart-download" title="Download chart as PNG">
                <i class="fas fa-download"></i>${this.showLabel ? ' Download' : ''}
            </button>
            <button type="button" class="${this.buttonClass} dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false" title="More options">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" id="export-chart-download-menu">
                    <i class="fas fa-download me-2"></i>Download PNG</a></li>
                <li><a class="dropdown-item" href="#" id="export-chart-save-db">
                    <i class="fas fa-database me-2"></i>Save to Database</a></li>
            </ul>
        `;

        // Bind click handlers
        wrapper.querySelector('#export-chart-download').addEventListener('click', (e) => {
            e.preventDefault();
            this.exportImage();
        });

        wrapper.querySelector('#export-chart-download-menu').addEventListener('click', (e) => {
            e.preventDefault();
            this.exportImage();
        });

        wrapper.querySelector('#export-chart-save-db').addEventListener('click', (e) => {
            e.preventDefault();
            this.saveToDatabase();
        });

        if (this.container) {
            this.container.appendChild(wrapper);
        }

        this.wrapper = wrapper;
        return wrapper;
    }

    /**
     * Create button element (for manual placement) - legacy support
     */
    createButton() {
        return this.render();
    }

    /**
     * Export chart as PNG image (download)
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
     * Save chart image to database
     */
    async saveToDatabase() {
        if (!this.chart) {
            console.error('No chart instance set');
            this.showToast('No chart to save', 'error');
            return;
        }

        if (!this.graphId) {
            console.error('No graph ID set');
            this.showToast('Cannot save: Graph must be saved first', 'warning');
            return;
        }

        try {
            // Disable button during save
            this.setEnabled(false);

            // Get high-resolution data URL from ECharts
            const dataUrl = this.chart.getDataURL({
                type: 'png',
                pixelRatio: 2,
                backgroundColor: '#fff'
            });

            // Send to server
            const response = await $.ajax({
                url: '?urlq=graph',
                type: 'POST',
                data: {
                    submit: 'graph-save-snapshot',
                    gid: this.graphId,
                    image_data: dataUrl
                }
            });

            if (response.status) {
                this.showToast('Image saved successfully', 'success');
                if (this.onSaveSuccess) {
                    this.onSaveSuccess(response.data);
                }
            } else {
                this.showToast(response.message || 'Failed to save image', 'error');
                if (this.onSaveError) {
                    this.onSaveError(response.message);
                }
            }

        } catch (error) {
            console.error('Failed to save chart to database:', error);
            this.showToast('Failed to save image', 'error');
            if (this.onSaveError) {
                this.onSaveError(error.message);
            }
        } finally {
            this.setEnabled(true);
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        // Use global Toast helper (from common.js)
        if (typeof window.Toast !== 'undefined') {
            if (type === 'success') {
                window.Toast.success(message);
            } else if (type === 'error') {
                window.Toast.error(message);
            } else if (type === 'warning') {
                window.Toast.warning(message);
            } else {
                window.Toast.info(message);
            }
        } else if (typeof DGCToast !== 'undefined') {
            DGCToast.show(message, type);
        } else {
            // Fallback: create a simple toast
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 200px;';
            toast.innerHTML = `
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                ${message}
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
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
     * Enable/disable buttons
     */
    setEnabled(enabled) {
        if (this.wrapper) {
            const buttons = this.wrapper.querySelectorAll('button');
            buttons.forEach(btn => btn.disabled = !enabled);
        }
    }

    /**
     * Destroy component
     */
    destroy() {
        if (this.wrapper && this.wrapper.parentNode) {
            this.wrapper.parentNode.removeChild(this.wrapper);
        }
        this.wrapper = null;
        this.chart = null;
    }
}
