/**
 * WidgetLoader - Shared utility for loading and rendering widgets (graphs, counters, and tables)
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
    this.onTableLoaded = options.onTableLoaded || null;
    this.onTableError = options.onTableError || null;
  }

  /**
   * Load all widgets (graphs, counters, and tables) in a container
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

    // Load tables
    const tableContainers = rootContainer.querySelectorAll('.widget-table-container[data-table-id]');
    Array.prototype.slice.call(tableContainers).forEach((container) => {
      const tableId = parseInt(container.dataset.tableId, 10);
      if (!tableId) return;

      this.loadTable(container, tableId, filters);
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
   * Load and render a single widget table
   * @param {HTMLElement} container - The container element
   * @param {number} tableId - The table ID
   * @param {Object} filters - Filter values to apply
   */
  async loadTable(container, tableId, filters = {}) {
    try {
      const result = await Ajax.post('preview_table', {
        id: tableId,
        filters: filters
      });

      if (!result.success || !result.data) {
        const errorMsg = result.message || 'Failed to load table';
        console.error(this.logPrefix, 'API returned error:', errorMsg);
        this.showTableError(container, errorMsg);
        if (this.onTableError) this.onTableError(tableId, errorMsg);
        return;
      }

      const tableData = result.data.tableData;
      if (tableData && tableData.error) {
        this.showTableError(container, tableData.error);
        if (this.onTableError) this.onTableError(tableId, tableData.error);
        return;
      }

      const config = result.data.config || {};

      // Clear loading state
      container.innerHTML = '';

      // Use TablePreview if available, otherwise render basic table
      if (window.TablePreview) {
        const preview = new window.TablePreview(container, { showSkeleton: false });
        preview.setConfig(config);
        preview.setData(tableData);
        preview.render();
      } else {
        // Fallback: render basic table
        this.renderBasicTable(container, tableData, config);
      }

      if (this.onTableLoaded) this.onTableLoaded(tableId);

    } catch (error) {
      console.error(this.logPrefix, 'Error loading widget table:', error);
      this.showTableError(container, 'Failed to load table');
      if (this.onTableError) this.onTableError(tableId, error.message);
    }
  }

  /**
   * Render a basic table (fallback when TablePreview is not available)
   * @param {HTMLElement} container - The container element
   * @param {Object} tableData - The table data
   * @param {Object} config - The table config
   */
  renderBasicTable(container, tableData, config) {
    if (!tableData || !tableData.columns || !tableData.rows) {
      container.innerHTML = '<div class="widget-table-empty"><i class="fas fa-table"></i><span>No data available</span></div>';
      return;
    }

    const columns = tableData.columns;
    const rows = tableData.rows;
    const styleConfig = config.style || {};

    // Build table classes
    const tableClasses = ['dgc-table'];
    if (styleConfig.striped) tableClasses.push('table-striped');
    if (styleConfig.bordered) tableClasses.push('table-bordered');
    if (styleConfig.hover) tableClasses.push('table-hover');
    if (styleConfig.density) tableClasses.push('table-' + styleConfig.density);

    let html = '<div class="table-responsive"><table class="' + tableClasses.join(' ') + '">';

    // Header
    html += '<thead><tr>';
    for (const col of columns) {
      const alignClass = col.align ? ' class="text-' + col.align + '"' : '';
      html += '<th' + alignClass + '>' + this.escapeHtml(col.label || col.key) + '</th>';
    }
    html += '</tr></thead>';

    // Body
    html += '<tbody>';
    if (rows.length === 0) {
      html += '<tr class="empty-row"><td colspan="' + columns.length + '">No data available</td></tr>';
    } else {
      for (const row of rows) {
        html += '<tr>';
        for (const col of columns) {
          const value = row[col.key] !== undefined ? row[col.key] : '';
          const alignClass = col.align ? ' class="text-' + col.align + '"' : '';
          html += '<td' + alignClass + '>' + this.escapeHtml(String(value)) + '</td>';
        }
        html += '</tr>';
      }
    }
    html += '</tbody></table></div>';

    container.innerHTML = html;
  }

  /**
   * Show error message in table container
   * @param {HTMLElement} container - The container element
   * @param {string} message - Error message
   */
  showTableError(container, message) {
    container.innerHTML = '<div class="widget-table-error"><i class="fas fa-exclamation-triangle"></i><span>' + this.escapeHtml(message) + '</span></div>';
  }

  /**
   * Escape HTML special characters
   * @param {string} str - String to escape
   * @returns {string} Escaped string
   */
  escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
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
