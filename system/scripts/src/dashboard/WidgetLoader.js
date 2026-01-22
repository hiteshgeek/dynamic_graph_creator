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
    console.log(this.logPrefix, 'loadAll called');

    const graphContainers = rootContainer.querySelectorAll('.widget-graph-container[data-graph-id]');
    console.log(this.logPrefix, 'Found', graphContainers.length, 'graph containers');

    if (graphContainers.length === 0) {
      console.log(this.logPrefix, 'No graph containers found');
      return;
    }

    const containers = Array.prototype.slice.call(graphContainers);

    containers.forEach((container) => {
      const graphId = parseInt(container.dataset.graphId, 10);
      console.log(this.logPrefix, 'Processing container with graphId:', graphId);

      if (!graphId) {
        console.log(this.logPrefix, 'Skipping container - no valid graphId');
        return;
      }

      const areaContent = container.closest('.area-content');
      const graphType = (areaContent && areaContent.dataset.graphType) ? areaContent.dataset.graphType : 'bar';
      console.log(this.logPrefix, 'Graph type:', graphType);

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
    console.log(this.logPrefix, 'Loading widget graph:', graphId, 'type:', graphType, 'filters:', filters);

    try {
      const result = await Ajax.post('preview_graph', {
        id: graphId,
        filters: filters
      });

      console.log(this.logPrefix, 'Graph API result:', result);

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

      // Clear loading state
      container.innerHTML = '';

      // Ensure container has dimensions
      if (container.offsetWidth === 0 || container.offsetHeight === 0) {
        container.style.minHeight = '300px';
        container.style.minWidth = '100%';
      }

      // Render using GraphPreview (don't show skeleton - data is already loaded)
      const preview = new window.GraphPreview(container, { showSkeleton: false });
      const actualGraphType = result.data.graphType || graphType;
      preview.setType(actualGraphType);

      // Always set config (API now guarantees it's an array)
      const config = result.data.config || {};
      console.log(this.logPrefix, 'Graph', graphId, 'config:', config, 'showDataViewToggle:', config.showDataViewToggle);
      preview.setConfig(config);

      if (chartData && chartData.mapping) {
        preview.setMapping(chartData.mapping);
      }

      preview.setData(chartData);
      preview.render();

      console.log(this.logPrefix, 'Chart rendered successfully for graph', graphId);
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
    container.innerHTML = '<div class="widget-graph-error"><i class="fas fa-exclamation-triangle"></i><span>' + message + '</span></div>';
  }
}

// Export for non-module usage
window.WidgetLoader = WidgetLoader;
