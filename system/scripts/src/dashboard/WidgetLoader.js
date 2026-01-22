/**
 * WidgetLoader - Shared utility for loading and rendering widget graphs
 * Used by dashboard-preview.js and template-preview.js
 */
const Ajax = window.Ajax;

export class WidgetLoader {
  constructor(options = {}) {
    this.logPrefix = options.logPrefix || '[WidgetLoader]';
    this.onGraphLoaded = options.onGraphLoaded || null;
    this.onGraphError = options.onGraphError || null;
  }

  /**
   * Load all widget graphs in a container
   * @param {HTMLElement} rootContainer - Container to search for widgets
   * @param {Object} filters - Filter values to apply
   */
  loadAll(rootContainer, filters = {}) {
    const graphContainers = rootContainer.querySelectorAll('.widget-graph-container[data-graph-id]');

    if (graphContainers.length === 0) {
      return;
    }

    const containers = Array.prototype.slice.call(graphContainers);

    containers.forEach((container) => {
      const graphId = parseInt(container.dataset.graphId, 10);

      if (!graphId) {
        return;
      }

      const areaContent = container.closest('.area-content');
      const graphType = (areaContent && areaContent.dataset.graphType) ? areaContent.dataset.graphType : 'bar';

      this.loadWidget(container, graphId, graphType, filters);
    });
  }

  /**
   * Load and render a single widget graph
   * @param {HTMLElement} container - The container element
   * @param {number} graphId - The graph ID
   * @param {string} graphType - The graph type
   * @param {Object} filters - Filter values to apply
   */
  async loadWidget(container, graphId, graphType, filters = {}) {
    try {
      const result = await Ajax.post('preview_graph', {
        id: graphId,
        filters: filters
      });

      if (!result.success || !result.data) {
        const errorMsg = result.message || 'Failed to load chart';
        console.error(this.logPrefix, 'API returned error:', errorMsg);
        this.showError(container, errorMsg);
        if (this.onGraphError) this.onGraphError(graphId, errorMsg);
        return;
      }

      const chartData = result.data.chartData;
      if (chartData && chartData.error) {
        this.showError(container, chartData.error);
        if (this.onGraphError) this.onGraphError(graphId, chartData.error);
        return;
      }

      // Dispose existing chart instance if present (to prevent memory leaks and errors)
      if (container._graphPreviewInstance) {
        try {
          container._graphPreviewInstance.destroy();
        } catch (e) {
          console.warn(this.logPrefix, 'Error destroying previous chart instance:', e);
        }
        delete container._graphPreviewInstance;
      }

      // Clear loading state
      container.innerHTML = '';

      // Ensure container has dimensions
      if (container.offsetWidth === 0 || container.offsetHeight === 0) {
        container.style.minHeight = '300px';
        container.style.minWidth = '100%';
      }

      // Render using GraphPreview (don't show skeleton - data is already loaded)
      const preview = new window.GraphPreview(container, { showSkeleton: false });
      // Store instance reference for future disposal
      container._graphPreviewInstance = preview;
      const actualGraphType = result.data.graphType || graphType;
      preview.setType(actualGraphType);

      // Always set config (API now guarantees it's an array)
      const config = result.data.config || {};
      preview.setConfig(config);

      if (chartData && chartData.mapping) {
        preview.setMapping(chartData.mapping);
      }

      preview.setData(chartData);
      preview.render();

      if (this.onGraphLoaded) this.onGraphLoaded(graphId);

    } catch (error) {
      console.error(this.logPrefix, 'Error loading widget graph:', error);
      this.showError(container, 'Failed to load chart');
      if (this.onGraphError) this.onGraphError(graphId, error.message);
    }
  }

  /**
   * Show error message in container
   * @param {HTMLElement} container - The container element
   * @param {string} message - Error message
   */
  showError(container, message) {
    // Dispose existing chart instance if present
    if (container._graphPreviewInstance) {
      try {
        container._graphPreviewInstance.destroy();
      } catch (e) {
        console.warn(this.logPrefix, 'Error destroying chart instance on error:', e);
      }
      delete container._graphPreviewInstance;
    }

    container.innerHTML = '<div class="widget-graph-error"><i class="fas fa-exclamation-triangle"></i><span>' + message + '</span></div>';
  }
}

// Export for non-module usage
window.WidgetLoader = WidgetLoader;
