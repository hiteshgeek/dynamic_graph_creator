/**
 * Main entry point for Graph Creator application
 * Initializes components based on page context
 */

import GraphCreator from './GraphCreator.js';
import GraphPreview from './GraphPreview.js';
import GraphExporter from './GraphExporter.js';
import QueryBuilder from './QueryBuilder.js';
import DataMapper from './DataMapper.js';
import FilterManager from './FilterManager.js';
import ConfigPanel from './ConfigPanel.js';
import GraphView from './GraphView.js';
import FilterManagerPage from './FilterManagerPage.js';

// Make classes available globally
window.GraphCreator = GraphCreator;
window.GraphPreview = GraphPreview;
window.GraphExporter = GraphExporter;
window.QueryBuilder = QueryBuilder;
window.DataMapper = DataMapper;
window.FilterManager = FilterManager;
window.ConfigPanel = ConfigPanel;
window.GraphView = GraphView;
window.FilterManagerPage = FilterManagerPage;

// Toast notification helper
window.Toast = {
    container: null,

    init() {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    },

    show(message, type = 'success', duration = 3000) {
        if (!this.container) this.init();

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle'
        };

        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.success}"></i>
            <span class="toast-message">${message}</span>
            <button class="toast-close"><i class="fas fa-times"></i></button>
        `;

        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });

        this.container.appendChild(toast);

        if (duration > 0) {
            setTimeout(() => toast.remove(), duration);
        }
    },

    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error', 5000); },
    warning(message) { this.show(message, 'warning', 4000); }
};

// Loading overlay helper
window.Loading = {
    overlay: null,

    show(message = 'Loading...') {
        if (!this.overlay) {
            this.overlay = document.createElement('div');
            this.overlay.className = 'loading-overlay';
            this.overlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(this.overlay);
        }

        this.overlay.querySelector('span').textContent = message;
        this.overlay.classList.add('active');
    },

    hide() {
        if (this.overlay) {
            this.overlay.classList.remove('active');
        }
    }
};

// AJAX helper
window.Ajax = {
    baseUrl: window.location.pathname,

    async post(submit, data = {}) {
        const formData = new FormData();
        formData.append('submit', submit);

        for (const key in data) {
            if (typeof data[key] === 'object') {
                formData.append(key, JSON.stringify(data[key]));
            } else {
                formData.append(key, data[key]);
            }
        }

        const response = await fetch(this.baseUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        return response.json();
    }
};

// Bootstrap Tooltip helper - global initialization
window.Tooltips = {
    init() {
        // Dispose existing tooltips to prevent duplicates
        this.disposeAll();

        // Initialize new tooltips with delay for slower appearance
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl, {
                delay: { show: 400, hide: 100 }
            });
        });
    },

    disposeAll() {
        const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        existingTooltips.forEach(el => {
            const tooltip = bootstrap.Tooltip.getInstance(el);
            if (tooltip) {
                tooltip.dispose();
            }
        });

        // Remove any orphaned tooltip elements
        document.querySelectorAll('.tooltip.bs-tooltip-auto, .tooltip.bs-tooltip-top, .tooltip.bs-tooltip-bottom, .tooltip.bs-tooltip-start, .tooltip.bs-tooltip-end').forEach(el => {
            el.remove();
        });
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize toast system
    Toast.init();

    // Initialize Bootstrap tooltips globally
    Tooltips.init();

    // Auto-init graph creator if container exists
    const creatorContainer = document.getElementById('graph-creator');
    if (creatorContainer) {
        const graphId = creatorContainer.dataset.graphId || null;
        window.graphCreator = new GraphCreator(creatorContainer, { graphId });
        window.graphCreator.init();
    }

    // Auto-init graph list page
    const listContainer = document.getElementById('graph-list');
    if (listContainer) {
        initGraphList(listContainer);
    }

    // Auto-init graph view page
    const viewContainer = document.getElementById('graph-view');
    if (viewContainer) {
        const graphId = viewContainer.dataset.graphId;
        const graphType = viewContainer.dataset.graphType || 'bar';
        const graphName = viewContainer.dataset.graphName || 'Chart';
        const config = viewContainer.dataset.config ? JSON.parse(viewContainer.dataset.config) : {};
        window.graphView = new GraphView(viewContainer, { graphId, graphType, graphName, config });
    }

    // Auto-init filter manager page
    const filterManagerContainer = document.querySelector('.filter-manager-page');
    if (filterManagerContainer) {
        const graphId = filterManagerContainer.dataset.graphId;
        window.filterManagerPage = new FilterManagerPage(filterManagerContainer, { graphId });
    }
});

// Initialize graph list page
function initGraphList(container) {
    // Delete button handlers
    container.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const graphId = btn.dataset.id;
            const graphName = btn.dataset.name;
            showDeleteModal(graphId, graphName);
        });
    });
}

// Show delete confirmation modal
function showDeleteModal(graphId, graphName) {
    const modal = document.getElementById('delete-modal');
    if (!modal) return;

    modal.querySelector('.graph-name').textContent = graphName;
    modal.classList.add('active');

    const confirmBtn = modal.querySelector('.confirm-delete');
    const cancelBtn = modal.querySelector('.cancel-delete');

    const closeModal = () => modal.classList.remove('active');

    cancelBtn.onclick = closeModal;
    modal.onclick = (e) => {
        if (e.target === modal) closeModal();
    };

    confirmBtn.onclick = async () => {
        Loading.show('Deleting...');
        try {
            const result = await Ajax.post('delete_graph', { id: graphId });
            if (result.success) {
                Toast.success(result.message);
                location.reload();
            } else {
                Toast.error(result.message);
            }
        } catch (error) {
            Toast.error('Failed to delete graph');
        } finally {
            Loading.hide();
            closeModal();
        }
    };
}

// Initialize graph view page
function initGraphView(container) {
    const graphId = container.dataset.graphId;
    const previewContainer = container.querySelector('.graph-preview-container');
    const filterContainer = container.querySelector('.filter-inputs');
    const applyBtn = container.querySelector('.filter-apply-btn');

    if (!previewContainer) return;

    const preview = new GraphPreview(previewContainer);

    // Load initial graph
    loadGraph(graphId, preview, filterContainer);

    // Apply filters button
    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            const filterValues = getFilterValues(filterContainer);
            loadGraph(graphId, preview, filterContainer, filterValues);
        });
    }
}

// Load graph with optional filters
async function loadGraph(graphId, preview, filterContainer, filterValues = {}) {
    Loading.show('Loading graph...');
    try {
        const result = await Ajax.post('preview_graph', {
            id: graphId,
            filters: filterValues
        });

        if (result.success) {
            preview.setData(result.data.chartData);
            preview.setConfig(result.data.config);
            preview.render();
        } else {
            Toast.error(result.message);
        }
    } catch (error) {
        Toast.error('Failed to load graph');
    } finally {
        Loading.hide();
    }
}

// Get filter values from inputs
function getFilterValues(container) {
    if (!container) return {};

    const values = {};
    container.querySelectorAll('[data-filter-key]').forEach(input => {
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
