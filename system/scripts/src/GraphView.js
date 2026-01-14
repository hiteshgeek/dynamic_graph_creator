/**
 * GraphView - Graph viewing page controller
 * Handles graph display and filter application
 */

import GraphPreview from './GraphPreview.js';
import GraphExporter from './GraphExporter.js';

// Use global helpers from main.js
const Ajax = window.Ajax;
const Loading = window.Loading;
const Toast = window.Toast;

export default class GraphView {
    constructor(container, options = {}) {
        this.container = container;
        this.graphId = options.graphId || null;
        this.graphType = options.graphType || 'bar';
        this.graphName = options.graphName || 'Chart';
        this.config = options.config || {};

        this.preview = null;
        this.exporter = null;

        if (this.container && this.graphId) {
            this.init();
        }
    }

    /**
     * Initialize graph view
     */
    init() {
        const previewContainer = this.container.querySelector('.graph-preview-container');

        if (previewContainer) {
            // Initialize preview
            this.preview = new GraphPreview(previewContainer);
            this.preview.setType(this.graphType);
            this.preview.setConfig(this.config);

            // Initialize exporter
            this.exporter = new GraphExporter({
                filename: this.graphName
            });
        }

        this.bindEvents();
        this.loadGraphData();
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Export button
        const exportBtn = this.container.querySelector('#export-chart');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportChart());
        }

        // Apply filters button
        const applyBtn = this.container.querySelector('.filter-apply-btn');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => this.loadGraphData());
        }
    }

    /**
     * Export chart as image
     */
    exportChart() {
        if (this.preview && this.preview.chart) {
            this.exporter.setChart(this.preview.chart);
            this.exporter.exportImage();
        }
    }

    /**
     * Load graph data from server
     */
    loadGraphData() {
        const filterValues = this.getFilterValues();

        Loading.show('Loading graph...');

        Ajax.post('preview_graph', {
            id: this.graphId,
            filters: filterValues
        }).then(result => {
            Loading.hide();
            if (result.success && result.data) {
                this.preview.setData(result.data.chartData);
                this.preview.render();
            } else {
                Toast.error(result.message || 'Failed to load graph');
            }
        }).catch(error => {
            Loading.hide();
            Toast.error('Failed to load graph');
        });
    }

    /**
     * Get filter values from inputs
     */
    getFilterValues() {
        const values = {};
        this.container.querySelectorAll('[data-filter-key]').forEach(input => {
            const key = input.dataset.filterKey;
            if (input.type === 'checkbox') {
                if (!values[key]) values[key] = [];
                if (input.checked) values[key].push(input.value);
            } else if (input.multiple) {
                values[key] = Array.from(input.selectedOptions).map(o => o.value);
            } else {
                values[key] = input.value;
            }
        });
        return values;
    }
}
