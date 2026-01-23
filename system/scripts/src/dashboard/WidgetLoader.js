/**
 * WidgetLoader - Shared utility for loading and rendering widgets (graphs and counters)
 * Used by dashboard-preview.js and template-preview.js
 */
const Ajax = window.Ajax;
import { CounterFormatter } from '../CounterFormatter.js';

export class WidgetLoader {
  constructor(options = {}) {
    this.logPrefix = options.logPrefix || '[WidgetLoader]';
    this.onGraphLoaded = options.onGraphLoaded || null;
    this.onGraphError = options.onGraphError || null;
    this.onCounterLoaded = options.onCounterLoaded || null;
    this.onCounterError = options.onCounterError || null;
  }

  /**
   * Load all widgets (graphs and counters) in a container
   * @param {HTMLElement} rootContainer - Container to search for widgets
   * @param {Object} filters - Filter values to apply
   */
  loadAll(rootContainer, filters = {}) {
    // Load graphs
    const graphContainers = rootContainer.querySelectorAll('.widget-graph-container[data-graph-id]');
    Array.prototype.slice.call(graphContainers).forEach((container) => {
      const graphId = parseInt(container.dataset.graphId, 10);
      if (!graphId) return;

      const areaContent = container.closest('.area-content');
      const graphType = (areaContent && areaContent.dataset.graphType) ? areaContent.dataset.graphType : 'bar';

      this.loadWidget(container, graphId, graphType, filters);
    });

    // Load counters
    const counterContainers = rootContainer.querySelectorAll('.widget-counter-container[data-counter-id]');
    Array.prototype.slice.call(counterContainers).forEach((container) => {
      const counterId = parseInt(container.dataset.counterId, 10);
      if (!counterId) return;

      this.loadCounter(container, counterId, filters);
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
   * Load and render a single widget counter
   * @param {HTMLElement} container - The container element
   * @param {number} counterId - The counter ID
   * @param {Object} filters - Filter values to apply
   */
  async loadCounter(container, counterId, filters = {}) {
    try {
      const result = await Ajax.post('preview_counter', {
        id: counterId,
        filters: filters
      });

      if (!result.success || !result.data) {
        const errorMsg = result.message || 'Failed to load counter';
        console.error(this.logPrefix, 'API returned error:', errorMsg);
        this.showCounterError(container, errorMsg);
        if (this.onCounterError) this.onCounterError(counterId, errorMsg);
        return;
      }

      const counterData = result.data.counterData;
      if (counterData && counterData.error) {
        this.showCounterError(container, counterData.error);
        if (this.onCounterError) this.onCounterError(counterId, counterData.error);
        return;
      }

      const value = counterData?.value ?? 0;
      const config = result.data.config || {};

      // Get icon and color from API response (preferred) or fallback to data attributes
      const counterColor = result.data.color || container.dataset.counterColor || '#4361ee';
      const counterIcon = result.data.icon || container.dataset.counterIcon || 'analytics';
      const counterName = result.data.name || `Counter #${counterId}`;

      // Render counter display using shared CounterFormatter
      container.innerHTML = CounterFormatter.renderCard({
        value,
        name: counterName,
        icon: counterIcon,
        color: counterColor,
        config
      }, 'compact');

      if (this.onCounterLoaded) this.onCounterLoaded(counterId);

    } catch (error) {
      console.error(this.logPrefix, 'Error loading widget counter:', error);
      this.showCounterError(container, 'Failed to load counter');
      if (this.onCounterError) this.onCounterError(counterId, error.message);
    }
  }

  /**
   * Show error message in counter container
   * @param {HTMLElement} container - The container element
   * @param {string} message - Error message
   */
  showCounterError(container, message) {
    // Use widget-counter-error class for dashboard context (extends shared counter-error styles)
    container.innerHTML = `<div class="widget-counter-error"><i class="fas fa-exclamation-triangle"></i><span>${CounterFormatter.escapeHtml(message)}</span></div>`;
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
