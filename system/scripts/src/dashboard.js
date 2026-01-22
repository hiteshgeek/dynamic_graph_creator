/**
 * Dashboard Module Entry Point
 * Simplified version for initial implementation
 */

// Use Sortable from CDN (global)
const Sortable = window.Sortable;

import { TemplateManager } from "./dashboard/TemplateManager.js";
import { keyboardNavigation } from "./dashboard/KeyboardNavigation.js";
import templateModalNavigation from "./dashboard/TemplateModalNavigation.js";
import FilterRenderer from "./FilterRenderer.js";
import DatePickerInit from "./DatePickerInit.js";
import { WidgetSelectorModal } from "./dashboard/WidgetSelectorModal.js";
import GraphPreview from "./GraphPreview.js";

// Export to window for other scripts to use
window.DatePickerInit = DatePickerInit;
window.FilterRenderer = FilterRenderer;
window.GraphPreview = GraphPreview;

// Use globals from common.js (Toast, Loading, Ajax, ConfirmDialog)
const Toast = window.Toast;
const Loading = window.Loading;
const Ajax = window.Ajax;
const ConfirmDialog = window.ConfirmDialog;

/**
 * Get the max total row height (fr) across all columns in a section
 * This represents the "perspective height" of the section
 * @param {HTMLElement} section - The dashboard-section element
 * @returns {number} The maximum total row fr across all columns
 */
function getSectionMaxRowFr(section) {
  if (!section) return 1;

  const areas = section.querySelectorAll(":scope > .dashboard-area");
  let maxRowFr = 1;

  areas.forEach((area) => {
    // Check if area has nested rows (data-rows attribute stores total)
    const areaRows = parseInt(area.dataset.rows) || 1;
    if (areaRows > maxRowFr) {
      maxRowFr = areaRows;
    }
  });

  return maxRowFr;
}

/**
 * Get the grid dimensions (fr units) for a cell
 * @param {HTMLElement} cell - The cell element (can be empty cell or any element inside an area)
 * @returns {{ colFr: number, rowFr: number, perspectiveHeight: number }} The column fr, row fr, and perspective height
 */
function getCellGridDimensions(cell) {
  const area = cell.closest(".dashboard-area");
  const subRow = cell.closest(".dashboard-sub-row");
  const section = cell.closest(".dashboard-section");

  // Get column width (fr) from section's grid-template-columns
  let colFr = 1;
  if (section && area) {
    const areaIndex = parseInt(area.dataset.areaIndex) || 0;
    const gridTemplate = section.style.gridTemplateColumns;
    if (gridTemplate) {
      // Parse "1fr 2fr 1fr" into array of numbers [1, 2, 1]
      const frValues = gridTemplate.split(/\s+/).map((val) => {
        const match = val.match(/^(\d+)fr$/);
        return match ? parseInt(match[1]) : 1;
      });
      colFr = frValues[areaIndex] || 1;
    }
  }

  // Get row height (fr) for this specific cell
  let rowFr = 1;
  if (subRow) {
    // Cell is in a sub-row, get its specific height
    rowFr = parseInt(subRow.dataset.rows) || 1;
  } else if (area) {
    // For non-nested areas, get area's total height (or 1 if not set)
    rowFr = parseInt(area.dataset.rows) || 1;
  }

  // Calculate perspective height
  // For sub-rows: use the row's own height (it's a slice of the column)
  // For non-nested areas: use the section's max row fr
  let perspectiveHeight;
  if (subRow) {
    // Sub-row perspective height is just its own height
    perspectiveHeight = rowFr;
  } else {
    // Non-nested area uses section's max row height
    perspectiveHeight = getSectionMaxRowFr(section);
  }

  return { colFr, rowFr, perspectiveHeight };
}

function handleEmptyCellAction(cell) {
  // Get context information from the cell's parent elements
  const area = cell.closest(".dashboard-area");
  const subRow = cell.closest(".dashboard-sub-row");
  const section = cell.closest(".dashboard-section");

  // Get grid dimensions (includes perspective height)
  const { colFr, rowFr, perspectiveHeight } = getCellGridDimensions(cell);

  const context = {
    cell,
    areaId: area?.dataset.areaId || null,
    areaIndex: area?.dataset.areaIndex || null,
    rowId: subRow?.dataset.rowId || null,
    rowIndex: subRow?.dataset.rowIndex || null,
    sectionId: section?.dataset.sectionId || null,
    colFr,
    rowFr,
    perspectiveHeight,
  };

  // Dispatch custom event so any page can listen and handle it
  const event = new CustomEvent("emptyCellClick", {
    detail: context,
    bubbles: true,
  });
  cell.dispatchEvent(event);

  // Open widget selector modal if dashboard builder is available
  if (window.dashboardBuilderInstance && window.dashboardBuilderInstance.openWidgetSelector) {
    window.dashboardBuilderInstance.openWidgetSelector(context);
  }
}

/**
 * Check if tweak mode is enabled (layout editing controls visible)
 * @param {HTMLElement} cell - Element to check from
 * @returns {boolean} True if tweak mode is ON (controls visible)
 */
function isTweakModeEnabled(cell) {
  // Tweak mode only exists on builder pages (.dashboard-builder)
  // Preview pages don't have tweak mode, so always return false for them
  const builder = cell.closest(".dashboard-builder");
  if (!builder) return false;
  // Tweak mode is ON when builder does NOT have layout-edit-disabled class
  return !builder.classList.contains("layout-edit-disabled");
}

// Global event listeners for empty cell (works on any page)
document.addEventListener("DOMContentLoaded", () => {
  // Click handler - trigger on dashboard-area or dashboard-sub-row
  document.addEventListener("click", (e) => {
    // Check if clicked on a dashboard-area or dashboard-sub-row
    const area = e.target.closest(".dashboard-sub-row") || e.target.closest(".dashboard-area:not(.dashboard-area-nested)");
    if (!area) return;

    // Skip if in tweak mode (layout controls are active)
    if (isTweakModeEnabled(area)) return;

    // Check if the area contains an empty cell
    const cell = area.querySelector(".dashboard-cell-empty");
    if (cell) {
      handleEmptyCellAction(cell);
    }
  });

  // Keyboard handler (Enter/Space)
  document.addEventListener("keydown", (e) => {
    if (e.key === "Enter" || e.key === " ") {
      // Check if focused on a dashboard-area or dashboard-sub-row
      const area = e.target.closest(".dashboard-sub-row") || e.target.closest(".dashboard-area:not(.dashboard-area-nested)");
      if (!area) return;

      // Skip if in tweak mode (layout controls are active)
      if (isTweakModeEnabled(area)) return;

      // Check if the area contains an empty cell
      const cell = area.querySelector(".dashboard-cell-empty");
      if (cell) {
        e.preventDefault();
        handleEmptyCellAction(cell);
      }
    }
  });

  // Initialize keyboard navigation module
  keyboardNavigation.init();

  // Initialize template modal navigation
  templateModalNavigation.init();

  // Expose to window for cross-module access
  window.keyboardNavigation = keyboardNavigation;

  // Initialize dashboard filter bar if present
  initDashboardFilterBar();
});

/**
 * Initialize dashboard filter bar
 * Filters are rendered via PHP, this just initializes the pickers
 * Same approach as GraphView.js initFilters()
 */
function initDashboardFilterBar() {
  const filterBar = document.querySelector(".dashboard-filter-bar");
  if (!filterBar) return;

  const filtersContainer = filterBar.querySelector("#dashboard-filters");
  if (!filtersContainer) return;

  // Use FilterRenderer for initialization (handles datepickers, multi-selects, etc.)
  // Same as GraphView.initFilters()
  if (typeof FilterRenderer !== "undefined") {
    FilterRenderer.initPickers(filtersContainer);
  } else if (typeof DatePickerInit !== "undefined") {
    // Fallback to just datepickers
    DatePickerInit.init(filtersContainer);
  }

  // Get UI elements
  const applyBtn = filterBar.querySelector(".filter-apply-btn");
  const separator = filterBar.querySelector(".filter-actions-separator:not(:first-of-type)");
  const autoApplySwitch = filterBar.querySelector("#dashboard-auto-apply-switch");
  const collapseBtn = filterBar.querySelector(".filter-collapse-btn");

  // Track auto-apply state
  let autoApplyEnabled = false;

  // Collapse/Expand functionality
  const COLLAPSE_KEY = "dgc_dashboard_filters_collapsed";

  function updateCollapseState(collapsed) {
    if (collapsed) {
      filterBar.classList.add("collapsed");
      if (collapseBtn) collapseBtn.title = "Expand Filters";
    } else {
      filterBar.classList.remove("collapsed");
      if (collapseBtn) collapseBtn.title = "Collapse Filters";
    }
  }

  // Restore collapse state from localStorage (without transitions)
  const savedCollapsed = localStorage.getItem(COLLAPSE_KEY) === "1";
  updateCollapseState(savedCollapsed);

  // Enable transitions after initial state is applied (use requestAnimationFrame to ensure DOM is settled)
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      filterBar.classList.add("transitions-enabled");
    });
  });

  // Collapse button handler
  if (collapseBtn) {
    collapseBtn.addEventListener("click", () => {
      const isCollapsed = filterBar.classList.contains("collapsed");
      const newState = !isCollapsed;
      updateCollapseState(newState);
      localStorage.setItem(COLLAPSE_KEY, newState ? "1" : "0");
    });
  }

  // Update UI based on auto-apply state
  function updateAutoApplyUI() {
    if (autoApplyEnabled) {
      // Hide apply button and separator when live filtering is ON
      applyBtn?.classList.remove("visible");
      separator?.classList.remove("visible");
    } else {
      // Show apply button and separator when live filtering is OFF
      applyBtn?.classList.add("visible");
      separator?.classList.add("visible");
    }
  }

  // Restore setting from localStorage
  const savedSetting = localStorage.getItem("dgc_dashboard_auto_apply");
  autoApplyEnabled = savedSetting === "1";
  if (autoApplySwitch) {
    autoApplySwitch.checked = autoApplyEnabled;
  }
  updateAutoApplyUI();

  // Auto-apply toggle handler
  if (autoApplySwitch) {
    autoApplySwitch.addEventListener("change", () => {
      autoApplyEnabled = autoApplySwitch.checked;
      localStorage.setItem("dgc_dashboard_auto_apply", autoApplyEnabled ? "1" : "0");
      updateAutoApplyUI();

      // If auto-apply is enabled, apply filters immediately
      if (autoApplyEnabled) {
        applyFilters();
      }
    });
  }

  // Apply filters function
  function applyFilters() {
    if (typeof FilterRenderer !== "undefined") {
      const values = FilterRenderer.getValues(filtersContainer);
      console.log("Dashboard filter values:", values);
      // TODO: Apply filters to dashboard graphs
      Toast.success("Filters applied");
    }
  }

  // Apply button handler
  if (applyBtn) {
    applyBtn.addEventListener("click", applyFilters);
  }

  // Listen for filter changes (for live filtering)
  filtersContainer.addEventListener("change", () => {
    if (autoApplyEnabled) {
      applyFilters();
    }
  });
}

// Grid configuration constants
// Max total fr for a section is MAX_COLUMNS (6fr), not numColumns * MAX_FR_UNITS
const GRID_CONFIG = {
  MAX_COL_FR_UNITS: 6, // Maximum fr units for any single column (6fr max)
  MAX_ROW_FR_UNITS: 4, // Maximum fr units for any single row (4fr max)
  MIN_FR_UNITS: 1, // Minimum fr units for any column/row
  MAX_COLUMNS: 6, // Maximum columns per section AND max total fr
  MIN_COLUMNS: 1, // Minimum columns (can't remove last)
  MAX_ROWS_PER_COLUMN: 4, // Maximum rows in a column AND max total row fr
  MIN_ROWS_PER_COLUMN: 1, // Minimum rows (can't remove last)
  DEFAULT_NEW_COLUMN_FR: 1,
  DEFAULT_NEW_ROW_FR: 1,
  ALLOW_NESTED_ROWS: false, // Only one level of sub-rows allowed
};

/**
 * Dashboard Builder - Main orchestrator
 */
class DashboardBuilder {
  constructor(container, options = {}) {
    this.container = container;
    this.mode = options.mode || "dashboard"; // 'dashboard' or 'template'
    this.dashboardId = options.dashboardId || null;
    this.templateId = options.templateId || null;
    this.isReadOnly = options.isReadOnly || false;
    this.currentDashboard = null;
    this.isDirty = false;
    this.isSaving = false;
    this.sortableInstance = null;
    this.eventHandlersInitialized = false;
    this.autoSaveTimeout = null;
    this.templateSelectorMode = "create-dashboard"; // or 'add-section'
    this.lastFocusedCell = null; // Track cell that had focus before modal opened
    this.widgetSelectorModal = null; // Widget selector modal instance
    this.widgetCharts = new Map(); // Map of graphId -> GraphPreview instance
  }

  async init() {
    // Initialize widget selector modal and wait for data to load
    await this.initWidgetSelector();

    if (this.mode === "template" && this.templateId) {
      this.loadTemplate();
    } else if (this.dashboardId) {
      this.loadDashboard();
    } else {
      this.showTemplateSelector();
    }
    this.initEventHandlers();
  }

  /**
   * Initialize the widget selector modal
   * @returns {Promise} Resolves when widget data is loaded
   */
  async initWidgetSelector() {
    this.widgetSelectorModal = new WidgetSelectorModal({
      onSelect: (graphId, context) => this.handleWidgetSelect(graphId, context),
      onDeselect: (context) => this.handleWidgetDeselect(context),
    });
    this.widgetSelectorModal.init();

    // Preload graph data so we can show widget names in the builder
    // Wait for this to complete before continuing
    await this.widgetSelectorModal.loadData();
  }

  /**
   * Handle widget selection from modal
   * @param {number} graphId - Selected graph ID
   * @param {Object} context - Area context
   */
  async handleWidgetSelect(graphId, context) {
    const content = {
      type: "graph",
      widgetId: graphId,
      widgetType: "graph",
      config: {},
    };

    // Update the area content in the structure
    this.updateAreaContentInStructure(context.sectionId, context.areaId, context.rowId, content);

    // Mark as dirty and trigger auto-save
    this.markDirty();
    this.renderDashboard();

    Toast.success("Widget added");
  }

  /**
   * Handle widget deselection (remove widget)
   * @param {Object} context - Area context
   */
  async handleWidgetDeselect(context) {
    const content = {
      type: "empty",
      widgetId: null,
      widgetType: null,
      config: {},
    };

    // Update the area content in the structure
    this.updateAreaContentInStructure(context.sectionId, context.areaId, context.rowId, content);

    // Mark as dirty and trigger auto-save
    this.markDirty();
    this.renderDashboard();

    Toast.success("Widget removed");
  }

  /**
   * Update area content in the dashboard structure
   * @param {string} sectionId
   * @param {string} areaId
   * @param {string|null} rowId - Sub-row ID if applicable
   * @param {Object} content - New content object
   */
  updateAreaContentInStructure(sectionId, areaId, rowId, content) {
    if (!this.currentDashboard) return;

    const structure =
      typeof this.currentDashboard.structure === "string"
        ? JSON.parse(this.currentDashboard.structure)
        : this.currentDashboard.structure;

    if (!structure || !structure.sections) return;

    for (const section of structure.sections) {
      if (section.sid === sectionId) {
        for (const area of section.areas) {
          if (area.aid === areaId) {
            // Check if we're updating a sub-row
            if (rowId && area.subRows) {
              for (const subRow of area.subRows) {
                if (subRow.rowId === rowId) {
                  subRow.content = content;
                  break;
                }
              }
            } else {
              // Update the area directly
              area.content = content;
            }
            break;
          }
        }
        break;
      }
    }

    // Update the dashboard structure
    this.currentDashboard.structure = JSON.stringify(structure);
  }

  /**
   * Open widget selector modal for an area
   * @param {Object} context - Area context from emptyCellClick event
   * @param {number|null} widgetId - Optional widget ID for edit mode
   */
  openWidgetSelector(context, widgetId = null) {
    if (!this.widgetSelectorModal) return;

    // Use provided widgetId or get from data structure
    const currentWidgetId = widgetId !== null
      ? widgetId
      : this.getAreaWidgetId(context.sectionId, context.areaId, context.rowId);

    // Get all widget IDs used in the dashboard
    const usedWidgetIds = this.getAllUsedWidgetIds();

    this.widgetSelectorModal.show(context, currentWidgetId, usedWidgetIds);
  }

  /**
   * Get all widget IDs currently used in the dashboard
   * @returns {Set<number>} Set of widget IDs
   */
  getAllUsedWidgetIds() {
    const usedIds = new Set();

    if (!this.currentDashboard) return usedIds;

    const structure =
      typeof this.currentDashboard.structure === "string"
        ? JSON.parse(this.currentDashboard.structure)
        : this.currentDashboard.structure;

    if (!structure || !structure.sections) return usedIds;

    for (const section of structure.sections) {
      if (!section.areas) continue;

      for (const area of section.areas) {
        // Check area's direct content
        if (area.content?.widgetId) {
          usedIds.add(area.content.widgetId);
        }

        // Check sub-rows
        if (area.subRows) {
          for (const subRow of area.subRows) {
            if (subRow.content?.widgetId) {
              usedIds.add(subRow.content.widgetId);
            }
          }
        }
      }
    }

    return usedIds;
  }

  /**
   * Get the widget ID for an area
   * @param {string} sectionId
   * @param {string} areaId
   * @param {string|null} rowId
   * @returns {number|null}
   */
  getAreaWidgetId(sectionId, areaId, rowId) {
    if (!this.currentDashboard) return null;

    const structure =
      typeof this.currentDashboard.structure === "string"
        ? JSON.parse(this.currentDashboard.structure)
        : this.currentDashboard.structure;

    if (!structure || !structure.sections) return null;

    for (const section of structure.sections) {
      if (section.sid === sectionId) {
        for (const area of section.areas) {
          if (area.aid === areaId) {
            if (rowId && area.subRows) {
              for (const subRow of area.subRows) {
                if (subRow.rowId === rowId) {
                  return subRow.content?.widgetId || null;
                }
              }
            }
            return area.content?.widgetId || null;
          }
        }
      }
    }
    return null;
  }

  /**
   * Get area context from DOM elements
   * @param {HTMLElement|null} area - dashboard-area element
   * @param {HTMLElement|null} subRow - dashboard-sub-row element
   * @returns {Object|null} Context object or null
   */
  getAreaContext(area, subRow) {
    if (subRow) {
      const parentArea = subRow.closest(".dashboard-area-nested");
      const section = subRow.closest(".dashboard-section");
      if (parentArea && section) {
        return {
          dashboardId: this.dashboardId,
          sectionId: section.dataset.sectionId,
          areaId: parentArea.dataset.areaId,
          rowId: subRow.dataset.rowId,
        };
      }
    } else if (area) {
      const section = area.closest(".dashboard-section");
      if (section) {
        return {
          dashboardId: this.dashboardId,
          sectionId: section.dataset.sectionId,
          areaId: area.dataset.areaId,
          rowId: null,
        };
      }
    }
    return null;
  }

  async loadTemplate() {
    if (!this.templateId) {
      console.error("No template ID specified");
      return;
    }

    Loading.show("Loading template...");

    try {
      // Load single template data
      const result = await Ajax.post("get_template", { id: this.templateId });

      if (result.success && result.data) {
        // Convert template to dashboard-like structure for rendering
        this.currentDashboard = {
          diid: null, // No dashboard instance ID
          dtid: result.data.dtid,
          name: result.data.name,
          structure: result.data.structure,
          is_system: result.data.is_system,
        };
        this.renderDashboard();
      } else {
        Toast.error(result.message || "Failed to load template");
      }
    } catch (error) {
      console.error("Template load error:", error);
      Toast.error("Failed to load template");
    } finally {
      Loading.hide();
    }
  }

  async loadDashboard() {
    if (!this.dashboardId) {
      console.error("No dashboard ID specified");
      return;
    }

    Loading.show("Loading dashboard...");

    try {
      const result = await Ajax.post("get_dashboard", { id: this.dashboardId });

      if (result.success) {
        this.currentDashboard = result.data;
        this.renderDashboard();
      } else {
        Toast.error(result.message);
      }
    } catch (error) {
      Toast.error("Failed to load dashboard");
    } finally {
      Loading.hide();
    }
  }

  async showTemplateSelector() {
    const modalElement = document.getElementById("template-modal");
    if (!modalElement) return;

    const nameInputWrapper = modalElement.querySelector(".dashboard-name-input");
    const descriptionInputWrapper = modalElement.querySelector(".dashboard-description-input");
    const nameInput = document.getElementById("new-dashboard-name");
    const descriptionInput = document.getElementById("new-dashboard-description");
    const modalTitle = modalElement.querySelector(".modal-title");
    const hrDivider = modalElement.querySelector(".dashboard-form-divider");

    // Check if dashboard already exists
    const isAddingSection = this.dashboardId !== null;

    if (isAddingSection) {
      // Dashboard exists - hide name/description inputs and update title
      if (nameInputWrapper) nameInputWrapper.classList.add("d-none");
      if (descriptionInputWrapper) descriptionInputWrapper.classList.add("d-none");
      if (hrDivider) hrDivider.classList.add("d-none");
      if (modalTitle) modalTitle.textContent = "Add Section";

      // Set mode to add-section
      this.templateSelectorMode = "add-section";
    } else {
      // New dashboard - show name/description inputs
      if (nameInputWrapper) nameInputWrapper.classList.remove("d-none");
      if (descriptionInputWrapper) descriptionInputWrapper.classList.remove("d-none");
      if (hrDivider) hrDivider.classList.remove("d-none");
      if (modalTitle) modalTitle.textContent = "Create New Dashboard";

      // Clear inputs and validation state
      if (nameInput) {
        nameInput.value = "";
        nameInput.classList.remove("is-invalid");
      }
      if (descriptionInput) {
        descriptionInput.value = "";
      }

      // Set mode to create-dashboard
      this.templateSelectorMode = "create-dashboard";
    }

    // Get or create Bootstrap modal instance
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) {
      modal = new bootstrap.Modal(modalElement);
    }

    // Track state for focus management
    let modalShown = false;
    let templatesLoaded = false;

    // Function to focus first card when both conditions are met
    const focusFirstCard = () => {
      if (isAddingSection && modalShown && templatesLoaded) {
        const firstCard = modalElement.querySelector(".item-card");
        if (firstCard) {
          firstCard.focus();
        }
      }
    };

    // Focus handling when modal is shown
    modalElement.addEventListener("shown.bs.modal", function focusHandler() {
      modalShown = true;
      if (!isAddingSection && nameInput) {
        nameInput.focus();
      } else {
        focusFirstCard();
      }
      modalElement.removeEventListener("shown.bs.modal", focusHandler);
    });

    // Track if modal was closed due to successful selection (not cancellation)
    this.templateModalSuccess = false;

    // Helper to focus with visible focus ring
    const focusWithVisible = (element) => {
      if (!element) return;
      element.classList.add("focus-visible");
      element.focus();
      element.addEventListener("blur", () => {
        element.classList.remove("focus-visible");
      }, { once: true });
    };

    // Restore focus when modal is hidden (only if cancelled, not on success)
    modalElement.addEventListener("hidden.bs.modal", function hiddenHandler() {
      // Only restore focus if modal was cancelled (not successful selection)
      if (!this.templateModalSuccess) {
        // Use setTimeout to ensure Bootstrap modal cleanup is complete
        setTimeout(() => {
          if (!isAddingSection) {
            // New dashboard mode - focus the choose template button
            const chooseTemplateBtn = document.querySelector(".choose-template-btn");
            focusWithVisible(chooseTemplateBtn);
          } else {
            // Adding section mode - restore focus to previously focused cell if navigation is enabled
            if (keyboardNavigation.isNavigationEnabled() && this.lastFocusedCell && document.body.contains(this.lastFocusedCell)) {
              if (!this.lastFocusedCell.hasAttribute("tabindex")) {
                this.lastFocusedCell.setAttribute("tabindex", "0");
              }
              focusWithVisible(this.lastFocusedCell);
            } else {
              // Fallback - focus the add section button
              const addFirstBtn = document.querySelector(".add-first-section-btn");
              focusWithVisible(addFirstBtn);
            }
          }
        });
      }
      // Reset the flag for next time
      this.templateModalSuccess = false;
      this.lastFocusedCell = null;
      modalElement.removeEventListener("hidden.bs.modal", hiddenHandler);
    }.bind(this));

    modal.show();

    try {
      const result = await Ajax.post("get_templates", {});

      if (result.success) {
        this.renderTemplates(result.data);
        templatesLoaded = true;
        focusFirstCard();
      } else {
        Toast.error("Failed to load templates");
      }
    } catch (error) {
      Toast.error("Failed to load templates");
    }
  }

  renderTemplatePreview(template) {
    try {
      const structure = JSON.parse(template.structure);
      let previewHtml = '<div class="template-preview-grid">';

      if (structure.sections && structure.sections.length > 0) {
        structure.sections.forEach((section) => {
          previewHtml += `<div class="preview-section" style="display: grid; grid-template-columns: ${section.gridTemplate};">`;

          if (section.areas) {
            section.areas.forEach((area) => {
              // Check if area has sub-rows
              if (area.hasSubRows && area.subRows && area.subRows.length > 0) {
                const rowHeights = area.subRows
                  .map((row) => row.height || "1fr")
                  .join(" ");
                previewHtml += `<div class="preview-area-nested" style="display: grid; grid-template-rows: ${rowHeights};">`;

                area.subRows.forEach(() => {
                  previewHtml += '<div class="preview-sub-row"></div>';
                });

                previewHtml += "</div>";
              } else {
                // Regular area
                previewHtml += '<div class="preview-area"></div>';
              }
            });
          }

          previewHtml += "</div>";
        });
      }

      previewHtml += "</div>";
      return previewHtml;
    } catch (e) {
      return '<div class="template-preview-fallback"><i class="fas fa-th-large"></i></div>';
    }
  }

  renderTemplates(templates) {
    const templateList = document.getElementById("template-list");
    if (!templateList) return;

    let html = "";

    // Iterate through each category group
    for (const [categorySlug, categoryData] of Object.entries(templates)) {
      if (categoryData.templates && categoryData.templates.length > 0) {
        const categoryName = categoryData.category.name || categorySlug;
        const categoryDescription = categoryData.category.description || "";

        html += `<div class="template-category">
                    <div class="template-category-header">
                        <h3>${categoryName.toUpperCase()}</h3>
                        ${categoryDescription ? `<p>${categoryDescription}</p>` : ""}
                    </div>
                    <div class="item-card-grid">`;

        categoryData.templates.forEach((template) => {
          const systemBadge =
            template.is_system == 1
              ? '<span class="badge badge-system"><i class="fas fa-lock"></i> System</span>'
              : "";
          html += `<div class="item-card" data-template-id="${template.dtid}" tabindex="0">
                        <div class="template-preview">
                            ${this.renderTemplatePreview(template)}
                        </div>
                        <div class="item-card-content">
                            <h3>${template.name}</h3>
                            ${template.description ? `<p class="item-card-description">${template.description}</p>` : ""}
                            ${systemBadge ? `<div class="item-card-tags">${systemBadge}</div>` : ""}
                        </div>
                    </div>`;
        });

        html += `</div></div>`;
      }
    }

    templateList.innerHTML = html;

    // Add click and keyboard handlers
    templateList.querySelectorAll(".item-card").forEach((card) => {
      const selectCard = () => {
        const templateId = parseInt(card.dataset.templateId);
        if (this.templateSelectorMode === "add-section") {
          this.addSectionFromTemplate(templateId);
        } else {
          this.createFromTemplate(templateId);
        }
      };

      card.addEventListener("click", selectCard);

      // Enter key selects the card (keyboard navigation support)
      card.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          selectCard();
        }
      });
    });
  }

  async createFromTemplate(templateId) {
    // Validate dashboard name
    const nameInput = document.getElementById("new-dashboard-name");
    const descriptionInput = document.getElementById("new-dashboard-description");
    const dashboardName = nameInput ? nameInput.value.trim() : "";
    const dashboardDescription = descriptionInput ? descriptionInput.value.trim() : "";

    if (!dashboardName) {
      if (nameInput) {
        nameInput.classList.add("is-invalid");
        nameInput.focus();
      }
      Toast.error("Please enter a dashboard name");
      return;
    }

    // Clear validation state
    if (nameInput) {
      nameInput.classList.remove("is-invalid");
    }

    Loading.show("Creating dashboard...");

    try {
      const result = await Ajax.post("create_from_template", {
        template_id: templateId,
        name: dashboardName,
        description: dashboardDescription,
      });

      if (result.success) {
        this.dashboardId = result.data.id;

        // Update URL to include dashboard ID (for refresh persistence)
        const newUrl = `?urlq=dashboard/builder/${this.dashboardId}`;
        window.history.pushState({ dashboardId: this.dashboardId }, "", newUrl);

        // Update container data attribute
        this.container.dataset.dashboardId = this.dashboardId;

        // Update page title
        document.title = `${dashboardName} - Edit Dashboard`;

        // Update header title
        const headerTitle = document.querySelector(".page-header-left h1");
        if (headerTitle) {
          headerTitle.textContent = dashboardName;
        }

        // Show save indicator and View Dashboard button
        this.updateHeaderAfterCreation();

        // Update sidebar to show "Add Section" button instead of "Choose Template"
        this.updateSidebarAfterCreation();

        // Load the dashboard
        await this.loadDashboard();

        // Mark as successful selection before closing modal
        this.templateModalSuccess = true;

        // Close modal using Bootstrap API
        const modalElement = document.getElementById("template-modal");
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }

        Toast.success("Dashboard created successfully");
      } else {
        Toast.error(result.message);
      }
    } catch (error) {
      Toast.error("Failed to create dashboard");
    } finally {
      Loading.hide();
    }
  }

  async addSectionFromTemplate(templateId) {
    Loading.show("Adding section...");

    // Store position before async operations
    const addPosition = this.pendingAddPosition;

    try {
      const result = await Ajax.post("add_section_from_template", {
        dashboard_id: this.dashboardId,
        template_id: templateId,
        position: addPosition,
      });

      if (result.success) {
        await this.loadDashboard();

        // Mark as successful selection before closing modal
        this.templateModalSuccess = true;

        // Close modal using Bootstrap API
        const modalElement = document.getElementById("template-modal");
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }

        // Reset mode
        this.templateSelectorMode = "create-dashboard";

        Toast.success("Section added successfully");

        // Focus on first area of newly added section if keyboard navigation is enabled
        // Use setTimeout to ensure DOM is fully ready after modal close and render
        if (window.keyboardNavigation?.isNavigationEnabled()) {
          setTimeout(() => {
            // Get the section at the position where we added
            const sections = this.container.querySelectorAll(".dashboard-section");
            const targetSection = sections[addPosition];
            if (targetSection) {
              const sectionId = targetSection.dataset.sectionId;
              window.keyboardNavigation.restoreFocusToArea(sectionId, 0, null);
            }
          }, 100);
        }
      } else {
        Toast.error(result.message);
      }
    } catch (error) {
      Toast.error("Failed to add section");
    } finally {
      Loading.hide();
    }
  }

  updateHeaderAfterCreation() {
    // Show save indicator with proper styling
    const saveIndicator = document.querySelector(".save-indicator");
    if (saveIndicator) {
      saveIndicator.style.display = "flex";
      saveIndicator.className = "save-indicator saved";
      const icon = saveIndicator.querySelector("i");
      const text = saveIndicator.querySelector("span");
      if (icon) icon.className = "fas fa-check-circle";
      if (text) text.textContent = "Saved";
    }

    // Add View Mode button and Tweak switch if they don't exist
    // Order should be: Saved → View Mode → Tweak → Separator → Theme Toggle
    const headerRight = document.querySelector(".page-header-right");
    if (headerRight && this.dashboardId) {
      // Find the separator before theme toggle to insert before it
      const headerSeparator = headerRight.querySelector(".header-separator");

      // Add View Mode button
      const existingViewBtn = headerRight.querySelector(
        'a[href*="dashboard/preview"]',
      );
      if (!existingViewBtn) {
        const viewBtn = document.createElement("a");
        viewBtn.href = `?urlq=dashboard/preview/${this.dashboardId}`;
        viewBtn.className = "btn btn-icon btn-outline-primary btn-view-mode";
        viewBtn.setAttribute("data-bs-toggle", "tooltip");
        viewBtn.title = "View Mode";
        viewBtn.innerHTML = '<i class="fas fa-eye"></i>';
        if (headerSeparator) {
          headerRight.insertBefore(viewBtn, headerSeparator);
        } else {
          headerRight.appendChild(viewBtn);
        }
        // Initialize tooltip for the new button
        new bootstrap.Tooltip(viewBtn, { delay: { show: 400, hide: 100 } });
      }

      // Add Tweak switch
      const existingTweakSwitch = headerRight.querySelector(
        "#toggle-layout-edit-switch",
      );
      if (!existingTweakSwitch) {
        const tweakDiv = document.createElement("div");
        tweakDiv.className =
          "form-check form-switch text-switch text-switch-purple";
        tweakDiv.innerHTML = `
          <input class="form-check-input" type="checkbox" role="switch" id="toggle-layout-edit-switch">
          <div class="text-switch-track">
            <span class="text-switch-knob"></span>
            <span class="text-switch-label label-text">Tweak</span>
          </div>
        `;
        if (headerSeparator) {
          headerRight.insertBefore(tweakDiv, headerSeparator);
        } else {
          headerRight.appendChild(tweakDiv);
        }

        // Initialize tweak switch functionality
        const tweakSwitch = tweakDiv.querySelector(
          "#toggle-layout-edit-switch",
        );
        const tweakEnabled =
          localStorage.getItem("dashboardTweakEnabled") === "true";
        tweakSwitch.checked = tweakEnabled;
        if (!tweakEnabled) {
          this.container.classList.add("layout-edit-disabled");
        }

        tweakSwitch.addEventListener("change", () => {
          if (tweakSwitch.checked) {
            this.container.classList.remove("layout-edit-disabled");
            localStorage.setItem("dashboardTweakEnabled", "true");
          } else {
            this.container.classList.add("layout-edit-disabled");
            localStorage.setItem("dashboardTweakEnabled", "false");
          }
        });
      }
    }
  }

  updateSidebarAfterCreation() {
    // Replace the choose-item-card with the dashboard sections container
    const gridEditor = document.querySelector(".grid-editor");
    if (!gridEditor) return;

    gridEditor.innerHTML = `
            <div class="dashboard-sections">
                <div class="loader">
                    <div class="spinner"></div>
                    <span class="loader-text">Loading dashboard...</span>
                </div>
            </div>
        `;
  }

  renderDashboard() {
    const sectionsContainer = this.container.querySelector(
      ".dashboard-sections",
    );
    if (!sectionsContainer) return;

    // Dispose all tooltips before re-rendering to prevent orphaned tooltips
    if (window.Tooltips) {
      window.Tooltips.disposeAll();
    }

    const structure = JSON.parse(this.currentDashboard.structure);

    let html = "";

    if (structure.sections && structure.sections.length > 0) {
      structure.sections.forEach((section, index) => {
        // Render the section with add button on top border
        html += this.renderSection(section, structure.sections.length, index);
      });
    } else {
      // Show empty state when no sections exist - use common empty state pattern
      html = `
                <div class="empty-state empty-state-blue">
                    <div class="empty-state-content">
                        <div class="empty-state-icon">
                            <i class="fas fa-th-large"></i>
                        </div>
                        <h3>No Sections Yet</h3>
                        <p>Start building your dashboard by adding a section or choosing a template</p>
                        <button class="btn btn-primary btn-sm add-first-section-btn" autofocus>
                            <i class="fas fa-plus"></i> Add Section
                        </button>
                    </div>
                </div>
            `;
    }

    sectionsContainer.innerHTML = html;

    // If we have sections, enable drag-drop and border buttons
    if (structure.sections && structure.sections.length > 0) {
      // Enable drag-drop
      this.initDragDrop();

      // Attach event listeners to border buttons
      this.initAddSectionBorderButtons();
    } else {
      // Attach event listener to add first section button
      const addFirstBtn = sectionsContainer.querySelector(
        ".add-first-section-btn",
      );
      if (addFirstBtn) {
        addFirstBtn.addEventListener("click", async () => {
          this.pendingAddPosition = 0;

          if (this.mode === "template") {
            // In template mode, show simple add section modal
            const modal = new bootstrap.Modal(
              document.getElementById("add-section-modal"),
            );
            modal.show();
          } else {
            // In dashboard mode, show template selector
            this.templateSelectorMode = "add-section";
            await this.showTemplateSelector();
          }
        });

        // For existing dashboards with no sections, auto-open the template selector modal
        if (this.mode === "dashboard" && this.dashboardId !== null) {
          // Check if modal is already open to prevent duplicate opens
          const templateModal = document.getElementById("template-modal");
          const isModalOpen = templateModal?.classList.contains("show");

          if (!isModalOpen) {
            this.pendingAddPosition = 0;
            this.templateSelectorMode = "add-section";
            this.showTemplateSelector();
          }
        } else {
          // For template mode or other cases, just focus the button
          addFirstBtn.focus();
        }
      }
    }

    // Initialize Bootstrap tooltips (use global helper)
    if (window.Tooltips) {
      window.Tooltips.init();
    }

    // Load and render widget graphs
    this.loadWidgetGraphs();

    // Check description truncation after layout is computed
    requestAnimationFrame(() => {
      this.checkDescriptionTruncation();
    });
  }

  /**
   * Load and render all widget graphs in the dashboard
   */
  async loadWidgetGraphs() {
    // Dispose existing chart instances
    this.disposeWidgetCharts();

    // Find all widget graph containers
    const graphContainers = this.container.querySelectorAll(".widget-graph-container[data-graph-id]");

    for (const container of graphContainers) {
      const graphId = parseInt(container.dataset.graphId, 10);
      if (!graphId) continue;

      // Get graph type from parent element
      const areaContent = container.closest(".area-content");
      const graphType = areaContent?.dataset.graphType || "bar";

      // Load and render the graph
      this.loadWidgetGraph(container, graphId, graphType);
    }
  }

  /**
   * Load and render a single widget graph
   * @param {HTMLElement} container - The container element
   * @param {number} graphId - The graph ID
   * @param {string} graphType - The graph type
   */
  async loadWidgetGraph(container, graphId, graphType) {
    try {
      // Fetch graph data
      const result = await Ajax.post("preview_graph", {
        id: graphId,
        filters: {},
      });

      if (result.success && result.data) {
        // Clear loading state
        container.innerHTML = "";

        // Create GraphPreview instance and render
        const preview = new GraphPreview(container);

        // Use graph type from API response if available, fallback to provided type
        const actualGraphType = result.data.graphType || graphType;
        preview.setType(actualGraphType);

        // Get config from graph data if available
        if (result.data.config) {
          preview.setConfig(result.data.config);
        }

        preview.setData(result.data.chartData);
        preview.render();

        // Store reference for cleanup
        this.widgetCharts.set(graphId, preview);
      } else {
        // Show error state
        container.innerHTML = `
          <div class="widget-graph-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>${result.message || "Failed to load chart"}</span>
          </div>
        `;
      }
    } catch (error) {
      console.error("Error loading widget graph:", error);
      container.innerHTML = `
        <div class="widget-graph-error">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Failed to load chart</span>
        </div>
      `;
    }
  }

  /**
   * Dispose all widget chart instances
   */
  disposeWidgetCharts() {
    for (const preview of this.widgetCharts.values()) {
      if (preview && preview.chart) {
        preview.chart.dispose();
      }
    }
    this.widgetCharts.clear();
  }

  /**
   * Check description text truncation and add is-truncated class
   */
  checkDescriptionTruncation() {
    const descriptions = this.container.querySelectorAll('.widget-graph-description');
    descriptions.forEach((desc) => {
      // Only check when collapsed
      if (!desc.classList.contains('collapsed')) {
        desc.classList.add('is-truncated');
        return;
      }

      // Check the text span for truncation
      const textSpan = desc.querySelector('.description-text');
      if (textSpan && textSpan.scrollWidth > textSpan.clientWidth) {
        desc.classList.add('is-truncated');
      } else {
        desc.classList.remove('is-truncated');
      }
    });
  }

  initAddSectionBorderButtons() {
    const borderBtns = document.querySelectorAll(".add-section-border-btn");
    if (borderBtns.length > 0) {
      borderBtns.forEach((btn) => {
        btn.addEventListener("click", async () => {
          const position = parseInt(btn.dataset.position);
          // Store the position for the add section handler
          this.pendingAddPosition = position;

          // Track the currently focused area to restore focus on cancel
          const focusedArea =
            document.activeElement?.closest(".dashboard-sub-row") ||
            document.activeElement?.closest(".dashboard-area");
          if (focusedArea) {
            this.lastFocusedCell = focusedArea;
          }

          if (this.mode === "template") {
            // In template mode, show simple add section modal
            const modal = new bootstrap.Modal(
              document.getElementById("add-section-modal"),
            );
            modal.show();
          } else {
            // In dashboard mode, show template selector
            this.templateSelectorMode = "add-section";
            await this.showTemplateSelector();
          }
        });
      });
    }
  }

  renderSection(section, totalSections = 1, index = 0) {
    const columnWidths = section.gridTemplate.split(" ");
    const numColumns = columnWidths.length;
    const widths = columnWidths.map((w) => parseInt(w) || 1);
    const canAddColumn = numColumns < GRID_CONFIG.MAX_COLUMNS;
    const canRemoveColumn = numColumns > GRID_CONFIG.MIN_COLUMNS;

    let areasHtml = "";

    section.areas.forEach((area, areaIndex) => {
      const hasSubRows =
        area.hasSubRows && area.subRows && area.subRows.length > 0;

      // Calculate total fr - max is always MAX_COLUMNS (4fr) regardless of column count
      const totalFr = widths.reduce((sum, w) => sum + w, 0);
      const maxTotalFr = GRID_CONFIG.MAX_COLUMNS;
      const hasRoomToGrowResize = totalFr < maxTotalFr;

      // Calculate resize options for this column
      // Shrink (minus): can decrease if current size > MIN
      // Expand (plus): can increase if current size < MAX AND total has room
      const canShrinkCol = widths[areaIndex] > GRID_CONFIG.MIN_FR_UNITS;
      const canExpandCol =
        widths[areaIndex] < GRID_CONFIG.MAX_COL_FR_UNITS && hasRoomToGrowResize;
      const hasResizeOptions = canShrinkCol || canExpandCol;

      // Calculate row height resize options for non-nested areas
      // Non-nested areas can have a rowHeight property (defaults to 1fr)
      const areaRowHeight = parseInt(area.rowHeight) || 1;
      const canExpandAreaRow = areaRowHeight < GRID_CONFIG.MAX_ROW_FR_UNITS;
      const canShrinkAreaRow = areaRowHeight > GRID_CONFIG.MIN_FR_UNITS;

      // Calculate add column options
      // Can add if: under max columns AND (there's room to grow OR ANY column can give 1fr)
      // Max total is always MAX_COLUMNS (4fr) regardless of column count
      const hasRoomToGrow = totalFr < maxTotalFr;
      const anyColumnCanGive = widths.some((w) => w > GRID_CONFIG.MIN_FR_UNITS);
      const canAddCol = canAddColumn && (hasRoomToGrow || anyColumnCanGive);

      // Both left and right add column buttons use the same logic
      const canAddColLeft = canAddCol;
      const canAddColRight = canAddCol;

      // Column drag handle - only show when more than one column
      const columnDragHandle =
        numColumns > 1
          ? `<button class="column-drag-handle" data-bs-toggle="tooltip" data-bs-title="Drag to reorder column">
                    <i class="fas fa-grip-vertical"></i>
                </button>`
          : "";

      // Area controls - edge buttons and center resize/delete
      const areaControls = `<div class="area-controls-overlay" tabindex="0">
                ${columnDragHandle}
                <!-- Top: Add Row Above (splits column into rows) -->
                <button class="edge-btn edge-top add-row-top-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Add row">
                    <i class="fas fa-plus"></i> Row
                </button>
                <!-- Bottom: Add Row Below (splits column into rows) -->
                <button class="edge-btn edge-bottom add-row-bottom-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Add row">
                    <i class="fas fa-plus"></i> Row
                </button>
                <!-- Left: Add Column Left -->
                <button class="edge-btn edge-left add-col-left-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Add column" ${!canAddColLeft ? "disabled" : ""}>
                    <i class="fas fa-plus"></i> Column
                </button>
                <!-- Right: Add Column Right -->
                <button class="edge-btn edge-right add-col-right-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Add column" ${!canAddColRight ? "disabled" : ""}>
                    <i class="fas fa-plus"></i> Column
                </button>
                <!-- Center: Resize buttons + Delete -->
                <div class="center-controls">
                    <button class="center-btn row-resize resize-area-row-up-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Expand height" ${!canExpandAreaRow ? "disabled" : ""}>
                        <i class="fas fa-caret-up"></i>
                    </button>
                    <div class="center-row">
                        <button class="center-btn col-resize resize-col-left-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Shrink column" ${!canShrinkCol ? "disabled" : ""}>
                            <i class="fas fa-caret-left"></i>
                        </button>
                        <button class="center-btn delete-btn remove-col-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Remove column" ${!canRemoveColumn ? "disabled" : ""}>
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="center-btn col-resize resize-col-right-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Expand column" ${!canExpandCol ? "disabled" : ""}>
                            <i class="fas fa-caret-right"></i>
                        </button>
                    </div>
                    <button class="center-btn row-resize resize-area-row-down-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Shrink height" ${!canShrinkAreaRow ? "disabled" : ""}>
                        <i class="fas fa-caret-down"></i>
                    </button>
                </div>
            </div>`;

      // Check if this area has sub-rows (nested structure)
      if (hasSubRows) {
        areasHtml += this.renderAreaWithSubRows(
          area,
          section.sid,
          areaIndex,
          canRemoveColumn,
          canAddColLeft,
          canAddColRight,
          canShrinkCol,
          canExpandCol,
          numColumns,
        );
      } else {
        // Regular single area with controls inside
        // Apply row height as min-height (default 150px per fr unit)
        const rowHeightFr = parseInt(area.rowHeight) || 1;
        const minHeightStyle = rowHeightFr > 1 ? `style="min-height: ${rowHeightFr * 150}px;"` : "";

        areasHtml += `<div class="dashboard-area" data-area-id="${area.aid}" data-area-index="${areaIndex}" ${minHeightStyle}>
                    ${areaControls}
                    ${
                      area.content && area.content.type === "empty"
                        ? this.renderEmptyState(area.emptyState)
                        : this.renderContent(area.content)
                    }
                </div>`;
      }
    });

    // Section control buttons on top border
    const dragHandleHtml =
      totalSections > 1
        ? `
            <button class="section-control-btn drag-handle" data-bs-toggle="tooltip" data-bs-title="Drag to reorder">
                <i class="fas fa-grip-vertical"></i>
            </button>
        `
        : "";

    const topBorderControls = `
            <div class="section-top-border-controls">
                ${dragHandleHtml}
                <button class="section-control-btn remove-btn" data-section-id="${section.sid}" data-bs-toggle="tooltip" data-bs-title="Remove section">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

    // Add section button on top border
    const topBorderButton = `
            <button class="add-section-border-btn add-section-top-btn" data-position="${index}" data-bs-toggle="tooltip" data-bs-title="Add section">
                <i class="fas fa-plus"></i>
                <span>Section</span>
            </button>
        `;

    // Add section button on bottom border
    const bottomBorderButton = `
            <button class="add-section-border-btn add-section-bottom-btn" data-position="${
              index + 1
            }" data-bs-toggle="tooltip" data-bs-title="Add section">
                <i class="fas fa-plus"></i>
                <span>Section</span>
            </button>
        `;

    // Grid size indicator - show column widths and row heights for nested areas
    // Format: "1fr | 2fr (2fr/1fr) | 1fr" where (2fr/1fr) shows row heights
    // Each part is wrapped in a span with data-area-id to highlight when hovering over that area
    // For nested areas, each row height is also wrapped with data-row-id for individual highlighting
    const gridIndicatorParts = section.areas.map((area, idx) => {
      const colWidth = columnWidths[idx];
      const areaId = area.aid;
      if (area.hasSubRows && area.subRows && area.subRows.length > 0) {
        const rowHeightParts = area.subRows
          .map((r) => {
            const height = r.height || "1fr";
            return `<span class="grid-size-row" data-row-id="${r.rowId}">${height}</span>`;
          })
          .join('<span class="grid-size-row-sep">/</span>');
        return `<span class="grid-size-part grid-size-part-nested" data-area-id="${areaId}">${colWidth} (${rowHeightParts})</span>`;
      }
      return `<span class="grid-size-part" data-area-id="${areaId}">${colWidth}</span>`;
    });
    const gridIndicator = `<div class="grid-size-indicator" data-section-id="${section.sid}">
            ${gridIndicatorParts.join('<span class="grid-size-separator">|</span>')}
        </div>`;

    return `<div class="dashboard-section-wrapper" data-section-id="${section.sid}">
            ${topBorderButton}
            ${topBorderControls}
            <div class="dashboard-section-container">
                <div class="dashboard-section" data-section-id="${section.sid}" style="grid-template-columns: ${section.gridTemplate};">
                    ${areasHtml}
                </div>
            </div>
            ${gridIndicator}
            ${bottomBorderButton}
        </div>`;
  }

  renderAreaWithSubRows(
    area,
    sectionId,
    areaIndex,
    canRemoveColumn,
    canAddColLeft = false,
    canAddColRight = false,
    canShrinkCol = false,
    canExpandCol = false,
    numColumns = 1,
  ) {
    // Build grid-template-rows from sub-row heights
    const rowHeights = area.subRows.map((row) => row.height || "1fr").join(" ");
    const numRows = area.subRows.length;
    const canRemoveRow = numRows > GRID_CONFIG.MIN_ROWS_PER_COLUMN;

    // Calculate row heights for resize button state
    // Max total is always MAX_ROWS_PER_COLUMN (4fr) regardless of row count
    const heights = area.subRows.map((r) => parseInt(r.height) || 1);
    const totalRowFr = heights.reduce((sum, h) => sum + h, 0);
    const maxRowTotalFr = GRID_CONFIG.MAX_ROWS_PER_COLUMN;
    const hasRoomToGrowRows = totalRowFr < maxRowTotalFr;

    // Calculate add row options - same logic as columns
    // Can add if: there's room to grow OR ANY row can give 1fr
    // The MAX_ROWS limit is enforced in addRowAt(), here we just check if adding is possible
    const anyRowCanGive = heights.some((h) => h > GRID_CONFIG.MIN_FR_UNITS);
    const canAddRow = hasRoomToGrowRows || anyRowCanGive;

    // Column drag handle - only show when more than one column (shown in first row only)
    const columnDragHandle =
      numColumns > 1
        ? `<button class="column-drag-handle" data-bs-toggle="tooltip" data-bs-title="Drag to reorder column">
                <i class="fas fa-grip-vertical"></i>
            </button>`
        : "";

    let subRowsHtml = "";

    area.subRows.forEach((subRow, rowIndex) => {
      const isFirstRow = rowIndex === 0;

      // Row resize conditions
      // Expand (plus on top): can increase if current size < MAX AND total has room
      // Shrink (minus on bottom): can decrease if current size > MIN
      const canExpandRow =
        heights[rowIndex] < GRID_CONFIG.MAX_ROW_FR_UNITS && hasRoomToGrowRows;
      const canShrinkRow = heights[rowIndex] > GRID_CONFIG.MIN_FR_UNITS;

      // Row drag handle - only show when more than one row
      const rowDragHandle =
        numRows > 1
          ? `<button class="row-drag-handle" data-bs-toggle="tooltip" data-bs-title="Drag to reorder row">
                    <i class="fas fa-grip-horizontal"></i>
                </button>`
          : "";

      // All controls inside each sub-row - both column and row actions
      // Each row shows Add Row Above (inserts above this row) and Add Row Below (inserts below this row)
      // Column drag handle only shown in first row
      const controls = `<div class="area-controls-overlay" tabindex="0">
                ${isFirstRow ? columnDragHandle : ""}
                ${rowDragHandle}
                <!-- Column actions: Add Column Left/Right -->
                <button class="edge-btn edge-left add-col-left-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Add column" ${!canAddColLeft ? "disabled" : ""}>
                    <i class="fas fa-plus"></i> Column
                </button>
                <button class="edge-btn edge-right add-col-right-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Add column" ${!canAddColRight ? "disabled" : ""}>
                    <i class="fas fa-plus"></i> Column
                </button>
                <!-- Row actions: Add Row Above/Below - each row can add above or below itself -->
                <button class="edge-btn edge-top add-row-top-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" data-bs-toggle="tooltip" data-bs-title="Add row" ${!canAddRow ? "disabled" : ""}>
                    <i class="fas fa-plus"></i> Row
                </button>
                <button class="edge-btn edge-bottom add-row-bottom-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" data-bs-toggle="tooltip" data-bs-title="Add row" ${!canAddRow ? "disabled" : ""}>
                    <i class="fas fa-plus"></i> Row
                </button>
                <!-- Center: All resize buttons + Delete buttons -->
                <div class="center-controls">
                    <button class="center-btn row-resize resize-row-up-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" data-bs-toggle="tooltip" data-bs-title="Expand row" ${!canExpandRow ? "disabled" : ""}>
                        <i class="fas fa-caret-up"></i>
                    </button>
                    <div class="center-row">
                        <button class="center-btn col-resize resize-col-left-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Shrink column" ${!canShrinkCol ? "disabled" : ""}>
                            <i class="fas fa-caret-left"></i>
                        </button>
                        <button class="center-btn delete-btn remove-row-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" data-bs-toggle="tooltip" data-bs-title="Remove row" ${!canRemoveRow ? "disabled" : ""}>
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="center-btn col-resize resize-col-right-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-bs-toggle="tooltip" data-bs-title="Expand column" ${!canExpandCol ? "disabled" : ""}>
                            <i class="fas fa-caret-right"></i>
                        </button>
                    </div>
                    <button class="center-btn row-resize resize-row-down-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" data-bs-toggle="tooltip" data-bs-title="Shrink row" ${!canShrinkRow ? "disabled" : ""}>
                        <i class="fas fa-caret-down"></i>
                    </button>
                </div>
            </div>`;

      // Row fr value for data-rows attribute (CSS handles min-height via _layout-grid.scss)
      const rowFr = parseInt(subRow.height) || 1;
      subRowsHtml += `<div class="dashboard-sub-row" data-row-id="${subRow.rowId}" data-row-index="${rowIndex}" data-rows="${rowFr}">
                ${controls}
                ${
                  subRow.content && subRow.content.type === "empty"
                    ? this.renderEmptyState(subRow.emptyState)
                    : this.renderContent(subRow.content)
                }
            </div>`;
    });

    // Total fr for data-rows attribute (CSS handles min-height via _layout-grid.scss)
    const totalFrForHeight = heights.reduce((sum, h) => sum + h, 0);

    return `<div class="dashboard-area dashboard-area-nested" data-area-id="${area.aid}" data-area-index="${areaIndex}" data-rows="${totalFrForHeight}" style="grid-template-rows: ${rowHeights};">
            ${subRowsHtml}
        </div>`;
  }

  renderEmptyState(emptyState) {
    return `<div class="dashboard-cell-empty" tabindex="0" role="button">
            <div class="cell-empty-icon">
                <i class="fas ${emptyState?.icon || "fa-plus"}"></i>
            </div>
            <div class="cell-empty-message">
                ${emptyState?.message || "Add content here"}
            </div>
        </div>`;
  }

  renderContent(content) {
    const widgetId = content?.widgetId || null;
    const widgetType = content?.widgetType || "Unknown";

    // Get graph details from the widget selector modal if available
    let graphName = `Graph #${widgetId}`;
    let graphType = "bar";
    let graphDescription = "";

    if (this.widgetSelectorModal && this.widgetSelectorModal.isDataLoaded()) {
      const graph = this.widgetSelectorModal.getGraphById(widgetId);
      if (graph) {
        graphName = graph.name;
        graphType = graph.graph_type || "bar";
        graphDescription = graph.description || "";
      }
    }

    // Edit overlay for design mode (visible on hover)
    const editOverlay = `
      <div class="widget-edit-overlay">
        <div class="widget-edit-overlay-buttons">
          <button class="btn btn-primary btn-sm widget-edit-btn" data-widget-id="${widgetId}" title="Change widget">
            <i class="fas fa-edit"></i> Edit
          </button>
          <button class="btn btn-danger btn-sm widget-delete-btn" data-widget-id="${widgetId}" title="Remove widget">
            <i class="fas fa-trash"></i> Delete
          </button>
        </div>
      </div>
    `;

    // Build description HTML if available
    let descriptionHtml = "";
    if (graphDescription) {
      const escapedDesc = this.escapeHtml(graphDescription);
      descriptionHtml = `
        <div class="widget-graph-description collapsed" data-full-text="${escapedDesc}">
          <span class="description-text">${escapedDesc}</span>
          <span class="description-toggle" onclick="this.parentElement.classList.toggle('collapsed'); this.parentElement.classList.toggle('expanded'); this.textContent = this.parentElement.classList.contains('collapsed') ? 'read more' : 'read less';">read more</span>
        </div>
      `;
    }

    // Render actual graph chart container
    return `<div class="area-content has-widget" data-widget-id="${widgetId}" data-widget-type="${widgetType}" data-graph-type="${graphType}">
            <div class="widget-graph-wrapper">
              <div class="widget-graph-header">
                <div class="widget-graph-title-section">
                  <span class="widget-graph-name">${this.escapeHtml(graphName)}</span>
                  ${descriptionHtml}
                </div>
              </div>
              <div class="widget-graph-container" data-graph-id="${widgetId}">
                <div class="widget-graph-loading">
                  <div class="spinner"></div>
                  <span>Loading chart...</span>
                </div>
              </div>
            </div>
            ${editOverlay}
        </div>`;
  }

  /**
   * Escape HTML special characters
   * @param {string} str
   * @returns {string}
   */
  escapeHtml(str) {
    if (!str) return "";
    const div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }

  initDragDrop() {
    const sectionsContainer = this.container.querySelector(
      ".dashboard-sections",
    );

    // Destroy existing sortable instances
    if (this.sortableInstance) {
      this.sortableInstance.destroy();
    }
    if (this.columnSortables) {
      this.columnSortables.forEach((s) => s.destroy());
    }
    if (this.rowSortables) {
      this.rowSortables.forEach((s) => s.destroy());
    }
    this.columnSortables = [];
    this.rowSortables = [];

    // Section sortable (existing)
    this.sortableInstance = Sortable.create(sectionsContainer, {
      animation: 150,
      handle: ".drag-handle",
      draggable: ".dashboard-section-wrapper",
      ghostClass: "section-ghost",
      onEnd: () => this.onSectionsReorder(),
    });

    // Column sortables - one for each section
    const sections = this.container.querySelectorAll(".dashboard-section");
    sections.forEach((section) => {
      const sectionId = section.dataset.sectionId;
      const columnCount = section.querySelectorAll(
        ":scope > .dashboard-area",
      ).length;

      // Only enable column sorting if more than one column
      if (columnCount > 1) {
        const sortable = Sortable.create(section, {
          animation: 150,
          handle: ".column-drag-handle",
          draggable: ".dashboard-area",
          ghostClass: "column-ghost",
          onEnd: (evt) => this.onColumnsReorder(sectionId, evt),
        });
        this.columnSortables.push(sortable);
      }
    });

    // Row sortables - one for each nested area with sub-rows
    const nestedAreas = this.container.querySelectorAll(
      ".dashboard-area-nested",
    );
    nestedAreas.forEach((nestedArea) => {
      const areaId = nestedArea.dataset.areaId;
      const sectionWrapper = nestedArea.closest(".dashboard-section-wrapper");
      const sectionId = sectionWrapper?.dataset.sectionId;
      const rowCount = nestedArea.querySelectorAll(".dashboard-sub-row").length;

      // Only enable row sorting if more than one row
      if (rowCount > 1 && sectionId) {
        const sortable = Sortable.create(nestedArea, {
          animation: 150,
          handle: ".row-drag-handle",
          draggable: ".dashboard-sub-row",
          ghostClass: "row-ghost",
          onEnd: (evt) => this.onRowsReorder(sectionId, areaId, evt),
        });
        this.rowSortables.push(sortable);
      }
    });
  }

  async onSectionsReorder() {
    const sections = this.container.querySelectorAll(".dashboard-section");
    const order = Array.from(sections).map(
      (section) => section.dataset.sectionId,
    );

    Loading.show("Reordering...");

    try {
      if (this.mode === "template") {
        // In template mode, directly modify the structure
        const structure = JSON.parse(this.currentDashboard.structure);

        // Create a map of sections by ID
        const sectionMap = {};
        structure.sections.forEach((section) => {
          sectionMap[section.sid] = section;
        });

        // Reorder sections based on new order
        structure.sections = order
          .map((sid) => sectionMap[sid])
          .filter((s) => s);

        // Update current dashboard structure
        this.currentDashboard.structure = JSON.stringify(structure);

        // Auto-save
        await this.saveDashboard(false);
      } else {
        // In dashboard mode, use API call
        const result = await Ajax.post("reorder_sections", {
          dashboard_id: this.dashboardId,
          order: order,
        });

        if (!result.success) {
          Toast.error(result.message);
        }
      }
    } catch (error) {
      console.error("Reorder error:", error);
      Toast.error("Failed to reorder sections");
    } finally {
      Loading.hide();
    }
  }

  async onColumnsReorder(sectionId, evt) {
    // Get new order of columns from DOM
    const section = this.container.querySelector(
      `.dashboard-section[data-section-id="${sectionId}"]`,
    );
    const columns = section.querySelectorAll(":scope > .dashboard-area");
    const newOrder = Array.from(columns).map((col) => col.dataset.areaId);

    Loading.show("Reordering columns...");

    try {
      const structure = JSON.parse(this.currentDashboard.structure);

      // Find the section
      const sectionData = structure.sections.find((s) => s.sid === sectionId);
      if (!sectionData) {
        Toast.error("Section not found");
        return;
      }

      // Create a map of areas by ID
      const areaMap = {};
      sectionData.areas.forEach((area) => {
        areaMap[area.aid] = area;
      });

      // Also get column widths and reorder them
      const oldWidths = sectionData.gridTemplate.split(" ");
      const oldAreaIds = sectionData.areas.map((a) => a.aid);

      // Reorder areas based on new order
      const newAreas = [];
      const newWidths = [];
      newOrder.forEach((aid) => {
        const oldIndex = oldAreaIds.indexOf(aid);
        if (oldIndex !== -1 && areaMap[aid]) {
          newAreas.push(areaMap[aid]);
          newWidths.push(oldWidths[oldIndex]);
        }
      });

      sectionData.areas = newAreas;
      sectionData.gridTemplate = newWidths.join(" ");

      // Update current dashboard structure
      this.currentDashboard.structure = JSON.stringify(structure);

      // Auto-save
      await this.saveDashboard(false);

      // Re-render to update UI (area indices, etc.)
      this.renderDashboard();
    } catch (error) {
      console.error("Column reorder error:", error);
      Toast.error("Failed to reorder columns");
      // Re-render to restore original order
      this.renderDashboard();
    } finally {
      Loading.hide();
    }
  }

  async onRowsReorder(sectionId, areaId, evt) {
    // Get new order of rows from DOM
    const nestedArea = this.container.querySelector(
      `.dashboard-area-nested[data-area-id="${areaId}"]`,
    );
    const rows = nestedArea.querySelectorAll(".dashboard-sub-row");
    const newOrder = Array.from(rows).map((row) => row.dataset.rowId);

    Loading.show("Reordering rows...");

    try {
      const structure = JSON.parse(this.currentDashboard.structure);

      // Find the section and area
      const sectionData = structure.sections.find((s) => s.sid === sectionId);
      if (!sectionData) {
        Toast.error("Section not found");
        return;
      }

      const areaData = sectionData.areas.find((a) => a.aid === areaId);
      if (!areaData || !areaData.subRows) {
        Toast.error("Area not found");
        return;
      }

      // Create a map of rows by ID
      const rowMap = {};
      areaData.subRows.forEach((row) => {
        rowMap[row.rowId] = row;
      });

      // Reorder rows based on new order
      const newRows = newOrder.map((rowId) => rowMap[rowId]).filter((r) => r);

      areaData.subRows = newRows;

      // Update current dashboard structure
      this.currentDashboard.structure = JSON.stringify(structure);

      // Auto-save
      await this.saveDashboard(false);

      // Re-render to update UI (row indices, etc.)
      this.renderDashboard();
    } catch (error) {
      console.error("Row reorder error:", error);
      Toast.error("Failed to reorder rows");
      // Re-render to restore original order
      this.renderDashboard();
    } finally {
      Loading.hide();
    }
  }

  /**
   * Move a section up or down by one position
   * @param {string} sectionId - The section ID
   * @param {string} direction - "up" or "down"
   */
  async moveSection(sectionId, direction) {
    try {
      const structure = JSON.parse(this.currentDashboard.structure);
      const currentIndex = structure.sections.findIndex(
        (s) => s.sid === sectionId,
      );

      if (currentIndex === -1) return;

      const newIndex = direction === "up" ? currentIndex - 1 : currentIndex + 1;

      // Check bounds
      if (newIndex < 0 || newIndex >= structure.sections.length) return;

      // Swap sections
      const temp = structure.sections[currentIndex];
      structure.sections[currentIndex] = structure.sections[newIndex];
      structure.sections[newIndex] = temp;

      // Update structure and save
      this.currentDashboard.structure = JSON.stringify(structure);
      await this.saveDashboard(false);
      this.renderDashboard();
    } catch (error) {
      console.error("Move section error:", error);
      Toast.error("Failed to move section");
    }
  }

  /**
   * Move a column left or right by one position within a section
   * @param {string} sectionId - The section ID
   * @param {number} areaIndex - The current column index
   * @param {string} direction - "left" or "right"
   */
  async moveColumn(sectionId, areaIndex, direction) {
    try {
      const structure = JSON.parse(this.currentDashboard.structure);
      const sectionData = structure.sections.find((s) => s.sid === sectionId);

      if (!sectionData) return;

      const newIndex = direction === "left" ? areaIndex - 1 : areaIndex + 1;

      // Check bounds
      if (newIndex < 0 || newIndex >= sectionData.areas.length) return;

      // Swap areas
      const temp = sectionData.areas[areaIndex];
      sectionData.areas[areaIndex] = sectionData.areas[newIndex];
      sectionData.areas[newIndex] = temp;

      // Also swap column widths
      const widths = sectionData.gridTemplate.split(" ");
      const tempWidth = widths[areaIndex];
      widths[areaIndex] = widths[newIndex];
      widths[newIndex] = tempWidth;
      sectionData.gridTemplate = widths.join(" ");

      // Update structure and save
      this.currentDashboard.structure = JSON.stringify(structure);
      await this.saveDashboard(false);
      this.renderDashboard();
    } catch (error) {
      console.error("Move column error:", error);
      Toast.error("Failed to move column");
    }
  }

  /**
   * Move a row up or down by one position within a nested area
   * @param {string} sectionId - The section ID
   * @param {number} areaIndex - The column index
   * @param {number} rowIndex - The current row index
   * @param {string} direction - "up" or "down"
   */
  async moveRow(sectionId, areaIndex, rowIndex, direction) {
    try {
      const structure = JSON.parse(this.currentDashboard.structure);
      const sectionData = structure.sections.find((s) => s.sid === sectionId);

      if (!sectionData) return;

      const areaData = sectionData.areas[areaIndex];
      if (!areaData || !areaData.subRows) return;

      const newIndex = direction === "up" ? rowIndex - 1 : rowIndex + 1;

      // Check bounds
      if (newIndex < 0 || newIndex >= areaData.subRows.length) return;

      // Swap rows
      const temp = areaData.subRows[rowIndex];
      areaData.subRows[rowIndex] = areaData.subRows[newIndex];
      areaData.subRows[newIndex] = temp;

      // Update structure and save
      this.currentDashboard.structure = JSON.stringify(structure);
      await this.saveDashboard(false);
      this.renderDashboard();
    } catch (error) {
      console.error("Move row error:", error);
      Toast.error("Failed to move row");
    }
  }

  async saveDashboard(showToast = false) {
    if (!this.currentDashboard || this.isSaving) return;

    // Don't save if read-only (system templates)
    if (this.isReadOnly) {
      Toast.warning("Cannot modify system templates");
      return;
    }

    this.isSaving = true;
    this.updateSaveIndicator("saving");

    try {
      const endpoint =
        this.mode === "template" ? "save_template_structure" : "save_dashboard";
      const data =
        this.mode === "template"
          ? {
              id: this.templateId,
              structure: this.currentDashboard.structure,
            }
          : {
              dashboard_id: this.dashboardId,
              name: this.currentDashboard.name,
              structure: this.currentDashboard.structure,
              config: this.currentDashboard.config || "{}",
            };

      const result = await Ajax.post(endpoint, data);

      if (result.success) {
        this.isDirty = false;
        this.updateSaveIndicator("saved");
        if (showToast) {
          Toast.success(
            this.mode === "template"
              ? "Template saved successfully"
              : "Dashboard saved successfully",
          );
        }
      } else {
        this.updateSaveIndicator("error");
        console.error("Save failed:", result);
        Toast.error(result.message || "Save failed");
      }
    } catch (error) {
      this.updateSaveIndicator("error");
      console.error("Save error:", error);
      Toast.error(
        this.mode === "template"
          ? "Failed to save template"
          : "Failed to save dashboard",
      );
    } finally {
      this.isSaving = false;
    }
  }

  markDirty() {
    this.isDirty = true;
    this.updateSaveIndicator("unsaved");

    // Auto-save after 2 seconds of inactivity
    if (this.autoSaveTimeout) {
      clearTimeout(this.autoSaveTimeout);
    }
    this.autoSaveTimeout = setTimeout(() => {
      this.saveDashboard(false);
    }, 2000);
  }

  updateSaveIndicator(state) {
    const indicator = document.querySelector(".save-indicator");
    if (!indicator) return;

    const icon = indicator.querySelector("i");
    const text = indicator.querySelector("span");

    switch (state) {
      case "saving":
        indicator.className = "save-indicator saving";
        icon.className = "fas fa-spinner fa-spin";
        text.textContent = "Saving...";
        break;
      case "saved":
        indicator.className = "save-indicator saved";
        icon.className = "fas fa-check-circle";
        text.textContent = "Saved";
        break;
      case "unsaved":
        indicator.className = "save-indicator unsaved";
        icon.className = "fas fa-circle";
        text.textContent = "Unsaved changes";
        break;
      case "error":
        indicator.className = "save-indicator error";
        icon.className = "fas fa-exclamation-circle";
        text.textContent = "Save failed";
        break;
    }
  }

  initEventHandlers() {
    // Prevent duplicate event handler initialization
    if (this.eventHandlersInitialized) {
      return;
    }
    this.eventHandlersInitialized = true;

    // Warn before leaving page with unsaved changes
    window.addEventListener("beforeunload", (e) => {
      if (this.isDirty) {
        e.preventDefault();
        e.returnValue = ""; // Required for Chrome
        return "";
      }
    });

    // Re-check description truncation on window resize
    window.addEventListener("resize", () => {
      this.checkDescriptionTruncation();
    });

    // Choose template button (when no dashboard exists)
    const chooseTemplateBtn = document.querySelector(".choose-template-btn");
    if (chooseTemplateBtn) {
      chooseTemplateBtn.addEventListener("click", () => {
        this.showTemplateSelector();
      });
    }

    // Add Section Modal - new layout with buttons and template grid
    const addSectionModal = document.getElementById("add-section-modal");
    if (addSectionModal) {
      // Generate empty column buttons dynamically based on GRID_CONFIG
      this.generateEmptyColumnButtons(addSectionModal);

      // Track modal shown state for focus management
      this.addSectionModalShown = false;

      // Load templates when modal starts showing
      addSectionModal.addEventListener("show.bs.modal", () => {
        this.addSectionModalShown = false;
        this.addSectionTemplatesLoaded = false;
        this.loadTemplatesForAddSectionModal();
      });

      // Focus first card when modal is fully shown
      addSectionModal.addEventListener("shown.bs.modal", () => {
        this.addSectionModalShown = true;
        this.focusFirstAddSectionCard();
      });

      // Restore focus when modal is hidden (cancelled)
      addSectionModal.addEventListener("hidden.bs.modal", () => {
        // Only restore focus if keyboard navigation mode is enabled
        if (keyboardNavigation.isNavigationEnabled()) {
          if (this.lastFocusedCell && document.body.contains(this.lastFocusedCell)) {
            // Ensure element can receive focus
            if (!this.lastFocusedCell.hasAttribute("tabindex")) {
              this.lastFocusedCell.setAttribute("tabindex", "0");
            }
            this.lastFocusedCell.focus();
          }
        }
        this.lastFocusedCell = null;
      });

      // Handle empty column button clicks
      addSectionModal.addEventListener("click", (e) => {
        const columnBtn = e.target.closest(".add-empty-columns-btn");
        if (columnBtn) {
          const columns = parseInt(columnBtn.dataset.columns);
          this.addEmptySection(columns);
        }

        // Handle template card clicks
        const templateCard = e.target.closest(".item-card");
        if (templateCard) {
          const templateId = parseInt(templateCard.dataset.templateId);
          this.addSectionFromTemplateCard(templateId);
        }
      });

      // Handle keyboard navigation for template cards and column buttons
      addSectionModal.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          // Handle column button selection
          const columnBtn = e.target.closest(".add-empty-columns-btn");
          if (columnBtn) {
            e.preventDefault();
            const columns = parseInt(columnBtn.dataset.columns);
            this.addEmptySection(columns);
            return;
          }

          // Handle template card selection
          const templateCard = e.target.closest(".item-card");
          if (templateCard) {
            e.preventDefault();
            const templateId = parseInt(templateCard.dataset.templateId);
            this.addSectionFromTemplateCard(templateId);
          }
        }
      });
    }

    // Remove section handlers - use event delegation on container instead of document
    this.container.addEventListener("click", (e) => {
      // Prevent clicks on area-controls-overlay from triggering empty cell action (widget selector)
      // This overlay is only visible in tweak mode, so clicks on it should not open the modal
      if (e.target.closest(".area-controls-overlay")) {
        e.stopPropagation();
      }

      if (e.target.closest(".remove-btn")) {
        const btn = e.target.closest(".remove-btn");
        const sectionId = btn.dataset.sectionId;
        this.removeSection(sectionId);
      }

      // Add column left (on column edge)
      if (e.target.closest(".add-col-left-btn")) {
        const btn = e.target.closest(".add-col-left-btn");
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        this.addColumnAt(sectionId, areaIndex);
      }

      // Add column right (on last column edge)
      if (e.target.closest(".add-col-right-btn")) {
        const btn = e.target.closest(".add-col-right-btn");
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        this.addColumnAt(sectionId, areaIndex + 1);
      }

      // Remove column (both old and new class names)
      if (
        e.target.closest(".remove-column-btn") ||
        e.target.closest(".remove-col-btn")
      ) {
        const btn =
          e.target.closest(".remove-column-btn") ||
          e.target.closest(".remove-col-btn");
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        this.removeColumn(sectionId, areaIndex);
      }

      // Add row above (inserts row above the current row, or splits column if not nested)
      if (e.target.closest(".add-row-top-btn")) {
        const btn = e.target.closest(".add-row-top-btn");
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        const rowIndex =
          btn.dataset.rowIndex !== undefined
            ? parseInt(btn.dataset.rowIndex)
            : 0;
        this.addRowAt(sectionId, areaIndex, rowIndex); // Insert at this position (pushes current down)
      }

      // Add row below (inserts row below the current row, or splits column if not nested)
      if (e.target.closest(".add-row-bottom-btn")) {
        const btn = e.target.closest(".add-row-bottom-btn");
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        const rowIndex =
          btn.dataset.rowIndex !== undefined
            ? parseInt(btn.dataset.rowIndex)
            : -1;
        // Insert after this row: if rowIndex is defined, insert at rowIndex + 1, otherwise -1 means at end
        const insertPosition = rowIndex >= 0 ? rowIndex + 1 : -1;
        this.addRowAt(sectionId, areaIndex, insertPosition);
      }

      // Remove row
      if (e.target.closest(".remove-row-btn")) {
        const btn = e.target.closest(".remove-row-btn");
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        const rowIndex = parseInt(btn.dataset.rowIndex);
        this.removeRow(sectionId, areaIndex, rowIndex);
      }

      // Resize column - decrease (minus button on left)
      if (e.target.closest(".resize-col-left-btn")) {
        const btn = e.target.closest(".resize-col-left-btn");
        if (btn.hasAttribute("disabled")) return;
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        this.resizeColumn(sectionId, areaIndex, "decrease");
      }

      // Resize column - increase (plus button on right)
      if (e.target.closest(".resize-col-right-btn")) {
        const btn = e.target.closest(".resize-col-right-btn");
        if (btn.hasAttribute("disabled")) return;
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        this.resizeColumn(sectionId, areaIndex, "increase");
      }

      // Resize row - increase (plus button on top)
      if (e.target.closest(".resize-row-up-btn")) {
        const btn = e.target.closest(".resize-row-up-btn");
        if (btn.hasAttribute("disabled")) return;
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        const rowIndex = parseInt(btn.dataset.rowIndex);
        this.resizeRow(sectionId, areaIndex, rowIndex, "increase");
      }

      // Resize row - decrease (minus button on bottom)
      if (e.target.closest(".resize-row-down-btn")) {
        const btn = e.target.closest(".resize-row-down-btn");
        if (btn.hasAttribute("disabled")) return;
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        const rowIndex = parseInt(btn.dataset.rowIndex);
        this.resizeRow(sectionId, areaIndex, rowIndex, "decrease");
      }

      // Resize area row height - increase (for non-nested areas)
      if (e.target.closest(".resize-area-row-up-btn")) {
        const btn = e.target.closest(".resize-area-row-up-btn");
        if (btn.hasAttribute("disabled")) return;
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        this.resizeAreaRow(sectionId, areaIndex, "increase");
      }

      // Resize area row height - decrease (for non-nested areas)
      if (e.target.closest(".resize-area-row-down-btn")) {
        const btn = e.target.closest(".resize-area-row-down-btn");
        if (btn.hasAttribute("disabled")) return;
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        this.resizeAreaRow(sectionId, areaIndex, "decrease");
      }

      // Widget edit button - open widget selector to change the widget
      if (e.target.closest(".widget-edit-btn")) {
        e.stopPropagation();
        const btn = e.target.closest(".widget-edit-btn");
        const widgetId = parseInt(btn.dataset.widgetId);
        const areaContent = btn.closest(".area-content");
        const area = areaContent?.closest(".dashboard-area");
        const subRow = areaContent?.closest(".dashboard-sub-row");

        if (area || subRow) {
          const context = this.getAreaContext(area, subRow);
          if (context) {
            this.openWidgetSelector(context, widgetId);
          }
        }
      }

      // Widget delete button - remove widget and return to empty state
      if (e.target.closest(".widget-delete-btn")) {
        e.stopPropagation();
        const btn = e.target.closest(".widget-delete-btn");
        const areaContent = btn.closest(".area-content");
        const area = areaContent?.closest(".dashboard-area");
        const subRow = areaContent?.closest(".dashboard-sub-row");

        if (area || subRow) {
          const context = this.getAreaContext(area, subRow);
          if (context) {
            this.handleWidgetDeselect(context);
          }
        }
      }
    });

    // Hover event delegation - highlight grid-size-indicator part when hovering over area
    // Helper function to highlight indicator part (for regular areas)
    const highlightIndicatorPart = (areaId, sectionWrapper, add) => {
      if (sectionWrapper && areaId) {
        const indicator = sectionWrapper.querySelector(".grid-size-indicator");
        if (indicator) {
          const part = indicator.querySelector(
            `.grid-size-part[data-area-id="${areaId}"]`,
          );
          if (part) {
            if (add) {
              part.classList.add("active");
            } else {
              part.classList.remove("active");
            }
          }
        }
      }
    };

    // Helper function to highlight individual row in indicator (for nested sub-rows)
    const highlightIndicatorRow = (rowId, sectionWrapper, add) => {
      if (sectionWrapper && rowId) {
        const indicator = sectionWrapper.querySelector(".grid-size-indicator");
        if (indicator) {
          const rowPart = indicator.querySelector(
            `.grid-size-row[data-row-id="${rowId}"]`,
          );
          if (rowPart) {
            if (add) {
              rowPart.classList.add("active");
            } else {
              rowPart.classList.remove("active");
            }
          }
        }
      }
    };

    this.container.addEventListener("mouseover", (e) => {
      // Check for sub-row first (inside nested area) - highlight only the specific row
      const subRow = e.target.closest(".dashboard-sub-row");
      if (subRow) {
        const rowId = subRow.dataset.rowId;
        const sectionWrapper = subRow.closest(".dashboard-section-wrapper");
        highlightIndicatorRow(rowId, sectionWrapper, true);
        return;
      }

      // Regular dashboard-area (non-nested only)
      const area = e.target.closest(".dashboard-area");
      if (area && !area.classList.contains("dashboard-area-nested")) {
        highlightIndicatorPart(
          area.dataset.areaId,
          area.closest(".dashboard-section-wrapper"),
          true,
        );
      }
    });

    this.container.addEventListener("mouseout", (e) => {
      // Check for sub-row first (inside nested area)
      const subRow = e.target.closest(".dashboard-sub-row");
      if (subRow) {
        const rowId = subRow.dataset.rowId;
        const sectionWrapper = subRow.closest(".dashboard-section-wrapper");
        highlightIndicatorRow(rowId, sectionWrapper, false);
        return;
      }

      // Regular dashboard-area (non-nested only)
      const area = e.target.closest(".dashboard-area");
      if (area && !area.classList.contains("dashboard-area-nested")) {
        highlightIndicatorPart(
          area.dataset.areaId,
          area.closest(".dashboard-section-wrapper"),
          false,
        );
      }
    });
  }

  // Click-based column resize: increase or decrease a column's width
  // direction: "increase" (+) or "decrease" (-)
  async resizeColumn(sectionId, areaIndex, direction) {
    if (this.isReadOnly) return;

    const structure = JSON.parse(this.currentDashboard.structure);
    const section = structure.sections.find((s) => s.sid === sectionId);

    if (!section) return;

    const widths = section.gridTemplate.split(" ").map((w) => parseInt(w) || 1);
    const totalFr = widths.reduce((sum, w) => sum + w, 0);
    const maxTotalFr = GRID_CONFIG.MAX_COLUMNS;

    let changed = false;
    if (direction === "increase") {
      // Increase this column's width by 1fr
      if (
        widths[areaIndex] < GRID_CONFIG.MAX_COL_FR_UNITS &&
        totalFr < maxTotalFr
      ) {
        widths[areaIndex]++;
        changed = true;
      }
    } else {
      // Decrease this column's width by 1fr
      if (widths[areaIndex] > GRID_CONFIG.MIN_FR_UNITS) {
        widths[areaIndex]--;
        changed = true;
      }
    }

    if (!changed) {
      Toast.warning("Column at minimum/maximum size");
      return;
    }

    const newTemplate = widths.map((w) => `${w}fr`).join(" ");
    section.gridTemplate = newTemplate;

    // Also update each area's colSpanFr
    section.areas.forEach((area, i) => {
      if (widths[i]) {
        area.colSpanFr = `${widths[i]}fr`;
      }
    });

    this.currentDashboard.structure = JSON.stringify(structure);
    this.renderDashboard();
    await this.saveDashboard(false);
  }

  // Click-based row resize: increase or decrease a row's height
  // direction: "increase" (+) or "decrease" (-)
  async resizeRow(sectionId, areaIndex, rowIndex, direction) {
    if (this.isReadOnly) return;

    const structure = JSON.parse(this.currentDashboard.structure);
    const section = structure.sections.find((s) => s.sid === sectionId);

    if (!section) return;

    const area = section.areas[areaIndex];

    if (!area.hasSubRows || !area.subRows) return;

    const heights = area.subRows.map((r) => parseInt(r.height) || 1);
    const totalFr = heights.reduce((sum, h) => sum + h, 0);
    const maxTotalFr = GRID_CONFIG.MAX_ROWS_PER_COLUMN;

    let changed = false;
    if (direction === "increase") {
      // Increase this row's height by 1fr
      if (
        heights[rowIndex] < GRID_CONFIG.MAX_ROW_FR_UNITS &&
        totalFr < maxTotalFr
      ) {
        heights[rowIndex]++;
        changed = true;
      }
    } else {
      // Decrease this row's height by 1fr
      if (heights[rowIndex] > GRID_CONFIG.MIN_FR_UNITS) {
        heights[rowIndex]--;
        changed = true;
      }
    }

    if (!changed) {
      Toast.warning("Row at minimum/maximum size");
      return;
    }

    // Update heights in subRows
    area.subRows.forEach((row, i) => {
      row.height = `${heights[i]}fr`;
    });

    this.currentDashboard.structure = JSON.stringify(structure);
    this.renderDashboard();
    await this.saveDashboard(false);
  }

  /**
   * Resize a non-nested area's row height
   * @param {string} sectionId - Section ID
   * @param {number} areaIndex - Area index within section
   * @param {string} direction - 'increase' or 'decrease'
   */
  async resizeAreaRow(sectionId, areaIndex, direction) {
    if (this.isReadOnly) return;

    const structure = JSON.parse(this.currentDashboard.structure);
    const section = structure.sections.find((s) => s.sid === sectionId);

    if (!section) return;

    const area = section.areas[areaIndex];

    // Only for non-nested areas
    if (area.hasSubRows && area.subRows && area.subRows.length > 0) return;

    // Get current row height (default to 1fr)
    let currentHeight = parseInt(area.rowHeight) || 1;

    let changed = false;
    if (direction === "increase") {
      if (currentHeight < GRID_CONFIG.MAX_ROW_FR_UNITS) {
        currentHeight++;
        changed = true;
      }
    } else {
      if (currentHeight > GRID_CONFIG.MIN_FR_UNITS) {
        currentHeight--;
        changed = true;
      }
    }

    if (!changed) {
      Toast.warning("Height at minimum/maximum size");
      return;
    }

    // Update area's row height
    area.rowHeight = `${currentHeight}fr`;

    this.currentDashboard.structure = JSON.stringify(structure);
    this.renderDashboard();
    await this.saveDashboard(false);
  }

  // Column management methods
  async addColumnAt(sectionId, position) {
    if (this.isReadOnly) return;

    const structure = JSON.parse(this.currentDashboard.structure);
    const section = structure.sections.find((s) => s.sid === sectionId);

    if (!section || section.areas.length >= GRID_CONFIG.MAX_COLUMNS) {
      Toast.error(`Maximum ${GRID_CONFIG.MAX_COLUMNS} columns allowed`);
      return;
    }

    // Parse current widths
    const widths = section.gridTemplate.split(" ").map((w) => parseInt(w) || 1);
    const numColumns = widths.length;
    const totalFr = widths.reduce((sum, w) => sum + w, 0);
    // Max total is always MAX_COLUMNS (4fr) regardless of column count
    const maxTotalFr = GRID_CONFIG.MAX_COLUMNS;
    const hasRoomToGrow = totalFr < maxTotalFr;

    // If there's room to grow, just add 1fr without taking from anyone
    // Otherwise, find a donor column that can give space
    if (!hasRoomToGrow) {
      // Need to take from a donor - try adjacent first, then any column
      let donorIndex = -1;

      // First try adjacent columns (preferred)
      if (position >= numColumns) {
        // Adding at right edge - try left neighbor first
        if (widths[numColumns - 1] > GRID_CONFIG.MIN_FR_UNITS) {
          donorIndex = numColumns - 1;
        }
      } else if (position === 0) {
        // Adding at left edge - try right neighbor first
        if (widths[0] > GRID_CONFIG.MIN_FR_UNITS) {
          donorIndex = 0;
        }
      } else {
        // Adding in between - try adjacent columns first
        if (widths[position] > GRID_CONFIG.MIN_FR_UNITS) {
          donorIndex = position;
        } else if (widths[position - 1] > GRID_CONFIG.MIN_FR_UNITS) {
          donorIndex = position - 1;
        }
      }

      // If no adjacent donor, find ANY column that can give
      if (donorIndex === -1) {
        donorIndex = widths.findIndex((w) => w > GRID_CONFIG.MIN_FR_UNITS);
      }

      if (donorIndex === -1) {
        Toast.warning("No column can give space");
        return;
      }
      widths[donorIndex]--;
    }

    // Insert new 1fr column at the specified position
    widths.splice(position, 0, GRID_CONFIG.DEFAULT_NEW_COLUMN_FR);

    // Update gridTemplate
    section.gridTemplate = widths.map((w) => `${w}fr`).join(" ");

    // Add new area at the specified position
    const newAreaId = IdGenerator.areaId();
    section.areas.splice(position, 0, {
      aid: newAreaId,
      colSpanFr: `${GRID_CONFIG.DEFAULT_NEW_COLUMN_FR}fr`,
      content: { type: "empty" },
      emptyState: { icon: "fa-plus", message: "Add content" },
    });

    // Update colSpanFr for all areas
    section.areas.forEach((area, i) => {
      area.colSpanFr = `${widths[i]}fr`;
    });

    this.currentDashboard.structure = JSON.stringify(structure);
    this.renderDashboard();
    await this.saveDashboard(false);
    Toast.success("Column added");
  }

  async removeColumn(sectionId, areaIndex, skipConfirm = false) {
    if (this.isReadOnly) return;

    // Store focused element for restoration if cancelled
    const focusedElement = document.activeElement;

    if (!skipConfirm) {
      const confirmed = await ConfirmDialog.delete(
        "Remove this column?",
        "Confirm Delete",
      );
      if (!confirmed) {
        // Restore focus if navigation mode is on
        if (
          window.keyboardNavigation?.isNavigationEnabled() &&
          focusedElement &&
          document.contains(focusedElement)
        ) {
          focusedElement.focus();
        }
        return;
      }
    }

    const structure = JSON.parse(this.currentDashboard.structure);
    const section = structure.sections.find((s) => s.sid === sectionId);

    if (!section || section.areas.length <= GRID_CONFIG.MIN_COLUMNS) {
      Toast.error(`Minimum ${GRID_CONFIG.MIN_COLUMNS} column required`);
      return;
    }

    // Parse widths as integers
    const widths = section.gridTemplate.split(" ").map((w) => parseInt(w) || 1);
    const removedWidth = widths[areaIndex];
    const numColumns = widths.length;

    // Remove the column width
    widths.splice(areaIndex, 1);

    // Redistribute the removed column's space to adjacent column(s)
    if (widths.length > 0) {
      // If only one column remains, reset it to 1fr (no point having 4fr for a single column)
      if (widths.length === 1) {
        widths[0] = GRID_CONFIG.MIN_FR_UNITS;
      } else {
        // Determine which column gets the extra space
        // If we removed the last column, give space to the new last column
        // If we removed the first column, give space to the new first column
        // Otherwise, give space to the left neighbor
        let recipientIndex;
        if (areaIndex >= widths.length) {
          // Removed last column - give to new last
          recipientIndex = widths.length - 1;
        } else if (areaIndex === 0) {
          // Removed first column - give to new first
          recipientIndex = 0;
        } else {
          // Removed middle column - give to left neighbor
          recipientIndex = areaIndex - 1;
        }

        // Add removed width to recipient, but cap at MAX_COL_FR_UNITS
        const newWidth = widths[recipientIndex] + removedWidth;
        widths[recipientIndex] = Math.min(
          newWidth,
          GRID_CONFIG.MAX_COL_FR_UNITS,
        );
      }
    }

    // Update gridTemplate with proper format
    section.gridTemplate = widths.map((w) => `${w}fr`).join(" ");

    // Remove the area
    section.areas.splice(areaIndex, 1);

    // Update colSpanFr for remaining areas
    section.areas.forEach((area, i) => {
      area.colSpanFr = `${widths[i]}fr`;
    });

    this.currentDashboard.structure = JSON.stringify(structure);
    this.renderDashboard();
    await this.saveDashboard(false);
    Toast.success("Column removed");
  }

  async updateSectionGridTemplate(sectionId, newTemplate) {
    if (this.isReadOnly) return;

    const structure = JSON.parse(this.currentDashboard.structure);
    const section = structure.sections.find((s) => s.sid === sectionId);

    if (section) {
      section.gridTemplate = newTemplate;

      // Also update each area's colSpanFr
      const widths = newTemplate.split(" ");
      section.areas.forEach((area, i) => {
        if (widths[i]) {
          area.colSpanFr = widths[i];
        }
      });

      this.currentDashboard.structure = JSON.stringify(structure);
      await this.saveDashboard(false);
    }
  }

  // Row management methods
  // Unified method to add row at any position (or split column if no rows exist)
  async addRowAt(sectionId, areaIndex, position) {
    if (this.isReadOnly) return;

    const structure = JSON.parse(this.currentDashboard.structure);
    const section = structure.sections.find((s) => s.sid === sectionId);

    if (!section) return;

    const area = section.areas[areaIndex];

    // If area doesn't have sub-rows yet, split it
    if (!area.hasSubRows || !area.subRows || area.subRows.length === 0) {
      // Convert to sub-rows: keep existing content in one row, add empty row
      area.hasSubRows = true;
      const existingContent = area.content || { type: "empty" };
      const existingEmptyState = area.emptyState || {
        icon: "fa-plus",
        message: "Add content",
      };

      if (position === 0) {
        // Add at top: new empty row first, existing content second
        area.subRows = [
          {
            rowId: IdGenerator.rowId(),
            height: `${GRID_CONFIG.DEFAULT_NEW_ROW_FR}fr`,
            content: { type: "empty" },
            emptyState: { icon: "fa-plus", message: "Add content" },
          },
          {
            rowId: IdGenerator.rowId(),
            height: `${GRID_CONFIG.DEFAULT_NEW_ROW_FR}fr`,
            content: existingContent,
            emptyState: existingEmptyState,
          },
        ];
      } else {
        // Add at bottom: existing content first, new empty row second
        area.subRows = [
          {
            rowId: IdGenerator.rowId(),
            height: `${GRID_CONFIG.DEFAULT_NEW_ROW_FR}fr`,
            content: existingContent,
            emptyState: existingEmptyState,
          },
          {
            rowId: IdGenerator.rowId(),
            height: `${GRID_CONFIG.DEFAULT_NEW_ROW_FR}fr`,
            content: { type: "empty" },
            emptyState: { icon: "fa-plus", message: "Add content" },
          },
        ];
      }

      // Clear area's direct content since it's now in subRows
      delete area.content;
      delete area.emptyState;

      this.currentDashboard.structure = JSON.stringify(structure);
      this.renderDashboard();
      await this.saveDashboard(false);
      Toast.success("Row added");
      return;
    }

    // Area already has sub-rows, add new row at position
    if (area.subRows.length >= GRID_CONFIG.MAX_ROWS_PER_COLUMN) {
      Toast.error(`Maximum ${GRID_CONFIG.MAX_ROWS_PER_COLUMN} rows allowed`);
      return;
    }

    // Parse current heights
    const heights = area.subRows.map((r) => parseInt(r.height) || 1);
    const numRows = heights.length;
    const totalFr = heights.reduce((sum, h) => sum + h, 0);
    // Max total is always MAX_ROWS_PER_COLUMN (4fr) regardless of row count
    const maxTotalFr = GRID_CONFIG.MAX_ROWS_PER_COLUMN;
    const hasRoomToGrow = totalFr < maxTotalFr;

    // If no room to grow, find a donor row
    if (!hasRoomToGrow) {
      // Try adjacent rows first, then any row
      let donorIndex = -1;
      const insertPos = position === -1 ? numRows : position;

      // First try adjacent rows (preferred)
      if (insertPos >= numRows) {
        // Adding at bottom - try last row first
        if (heights[numRows - 1] > GRID_CONFIG.MIN_FR_UNITS) {
          donorIndex = numRows - 1;
        }
      } else if (insertPos === 0) {
        // Adding at top - try first row first
        if (heights[0] > GRID_CONFIG.MIN_FR_UNITS) {
          donorIndex = 0;
        }
      } else {
        // Adding in between - try adjacent rows first
        if (heights[insertPos] > GRID_CONFIG.MIN_FR_UNITS) {
          donorIndex = insertPos;
        } else if (heights[insertPos - 1] > GRID_CONFIG.MIN_FR_UNITS) {
          donorIndex = insertPos - 1;
        }
      }

      // If no adjacent donor, find ANY row that can give
      if (donorIndex === -1) {
        donorIndex = heights.findIndex((h) => h > GRID_CONFIG.MIN_FR_UNITS);
      }

      if (donorIndex === -1) {
        Toast.warning("No row can give space");
        return;
      }

      // Reduce donor row height
      area.subRows[donorIndex].height = `${heights[donorIndex] - 1}fr`;
    }

    const newRowId = IdGenerator.rowId();
    const newRow = {
      rowId: newRowId,
      height: `${GRID_CONFIG.DEFAULT_NEW_ROW_FR}fr`,
      content: { type: "empty" },
      emptyState: { icon: "fa-plus", message: "Add content" },
    };

    if (position === 0) {
      area.subRows.unshift(newRow);
    } else if (position === -1) {
      area.subRows.push(newRow);
    } else {
      area.subRows.splice(position, 0, newRow);
    }

    this.currentDashboard.structure = JSON.stringify(structure);
    this.renderDashboard();
    await this.saveDashboard(false);
    Toast.success("Row added");
  }

  // Legacy methods for compatibility (now unused)
  async splitColumn(sectionId, areaIndex) {
    await this.addRowAt(sectionId, areaIndex, -1);
  }

  async addRowAbove(sectionId, areaIndex) {
    await this.addRowAt(sectionId, areaIndex, 0);
  }

  async addRowBelow(sectionId, areaIndex, rowIndex) {
    // Note: This was adding at end, not after specific row
    await this.addRowAt(sectionId, areaIndex, -1);
  }

  async removeRow(sectionId, areaIndex, rowIndex, skipConfirm = false) {
    if (this.isReadOnly) return;

    // Store focused element for restoration if cancelled
    const focusedElement = document.activeElement;

    if (!skipConfirm) {
      const confirmed = await ConfirmDialog.delete(
        "Remove this row?",
        "Confirm Delete",
      );
      if (!confirmed) {
        // Restore focus if navigation mode is on
        if (
          window.keyboardNavigation?.isNavigationEnabled() &&
          focusedElement &&
          document.contains(focusedElement)
        ) {
          focusedElement.focus();
        }
        return;
      }
    }

    const structure = JSON.parse(this.currentDashboard.structure);
    const section = structure.sections.find((s) => s.sid === sectionId);

    if (!section) return;

    const area = section.areas[areaIndex];

    if (
      !area.hasSubRows ||
      !area.subRows ||
      area.subRows.length <= GRID_CONFIG.MIN_ROWS_PER_COLUMN
    ) {
      Toast.error(`Minimum ${GRID_CONFIG.MIN_ROWS_PER_COLUMN} row required`);
      return;
    }

    // Remove the row
    area.subRows.splice(rowIndex, 1);

    // If only one row remains, convert back to single area
    if (area.subRows.length === 1) {
      area.content = area.subRows[0].content;
      area.emptyState = area.subRows[0].emptyState;
      delete area.hasSubRows;
      delete area.subRows;
    }

    this.currentDashboard.structure = JSON.stringify(structure);
    this.renderDashboard();
    await this.saveDashboard(false);
    Toast.success("Row removed");
  }

  async updateRowHeights(sectionId, areaIndex, heights) {
    if (this.isReadOnly) return;

    const structure = JSON.parse(this.currentDashboard.structure);
    const section = structure.sections.find((s) => s.sid === sectionId);

    if (!section) return;

    const area = section.areas[areaIndex];

    if (area.hasSubRows && area.subRows) {
      area.subRows.forEach((row, i) => {
        if (heights[i] !== undefined) {
          row.height = `${heights[i]}fr`;
        }
      });

      this.currentDashboard.structure = JSON.stringify(structure);
      await this.saveDashboard(false);
    }
  }

  /**
   * Close the add section modal with cleanup
   */
  async closeAddSectionModal() {
    const modalElement = document.getElementById("add-section-modal");
    const modalInstance = bootstrap.Modal.getInstance(modalElement);

    if (modalInstance) {
      modalInstance.hide();

      // Wait for modal to fully close
      await new Promise((resolve) => {
        modalElement.addEventListener("hidden.bs.modal", resolve, {
          once: true,
        });
      });

      // Force cleanup of backdrop if it still exists
      const backdrop = document.querySelector(".modal-backdrop");
      if (backdrop) {
        backdrop.remove();
      }

      // Remove modal-open class from body
      document.body.classList.remove("modal-open");
      document.body.style.overflow = "";
      document.body.style.paddingRight = "";
    }
  }

  /**
   * Add empty section with specified columns
   */
  async addEmptySection(columns) {
    const position =
      this.pendingAddPosition !== undefined ? this.pendingAddPosition : 0;

    await this.closeAddSectionModal();
    Loading.show("Adding section...");

    try {
      if (this.mode === "template") {
        // In template mode, directly modify the structure
        const structure = JSON.parse(this.currentDashboard.structure);

        // Generate new section ID
        const newSectionId = IdGenerator.sectionId();

        // Create column template
        const colWidths = Array(columns).fill("1fr").join(" ");

        // Create areas for the new section
        const areas = [];
        for (let i = 0; i < columns; i++) {
          areas.push({
            aid: IdGenerator.areaId(),
            colSpanFr: "1fr",
            content: { type: "empty" },
            emptyState: {
              icon: "fa-plus",
              message: "Add content",
            },
          });
        }

        // Create new section
        const newSection = {
          sid: newSectionId,
          gridTemplate: colWidths,
          areas: areas,
        };

        // Insert section at position
        structure.sections.splice(position, 0, newSection);

        // Update current dashboard structure
        this.currentDashboard.structure = JSON.stringify(structure);

        // Re-render
        this.renderDashboard();

        // Auto-save
        await this.saveDashboard(false);

        Toast.success("Section added");
      } else {
        // In dashboard mode, use API call
        const result = await Ajax.post("add_section", {
          dashboard_id: this.dashboardId,
          columns: columns,
          position: position,
        });

        if (result.success) {
          await this.loadDashboard();
          Toast.success("Section added");
        } else {
          Toast.error(result.message);
        }
      }
    } catch (error) {
      console.error("Add section error:", error);
      Toast.error("Failed to add section");
    } finally {
      Loading.hide();
    }
  }

  /**
   * Generate empty column buttons dynamically based on GRID_CONFIG.MAX_COLUMNS
   * @param {HTMLElement} modal - The modal element containing the buttons container
   */
  generateEmptyColumnButtons(modal) {
    const container = modal.querySelector(
      ".add-section-empty-columns .d-flex.gap-2",
    );
    if (!container) return;

    // Clear existing buttons
    container.innerHTML = "";

    // Generate buttons from 1 to MAX_COLUMNS
    for (let i = 1; i <= GRID_CONFIG.MAX_COLUMNS; i++) {
      const button = document.createElement("button");
      button.type = "button";
      button.className = "btn btn-sm btn-outline-primary add-empty-columns-btn";
      button.dataset.columns = i;
      button.textContent = `${i} Column${i > 1 ? "s" : ""}`;
      container.appendChild(button);
    }
  }

  /**
   * Load templates for the Add Section modal and render template grid
   */
  async loadTemplatesForAddSectionModal() {
    const templateList = document.getElementById("add-section-template-list");
    if (!templateList) return;

    // Show loading state
    templateList.innerHTML = `<div class="loader">
      <div class="spinner"></div>
      <span class="loader-text">Loading templates...</span>
    </div>`;

    try {
      const result = await Ajax.post("get_templates", {});

      if (result.success && result.data) {
        let html = "";

        // Iterate through each category group
        for (const [categorySlug, categoryData] of Object.entries(
          result.data,
        )) {
          // Filter out current template if in template mode
          const filteredTemplates = categoryData.templates
            ? categoryData.templates.filter(
                (t) =>
                  !(this.mode === "template" && t.dtid === this.templateId),
              )
            : [];

          if (filteredTemplates.length > 0) {
            const categoryName = categoryData.category?.name || categorySlug;
            const categoryDescription =
              categoryData.category?.description || "";

            html += `<div class="template-category">
                      <div class="template-category-header">
                        <h3>${categoryName.toUpperCase()}</h3>
                        ${categoryDescription ? `<p>${categoryDescription}</p>` : ""}
                      </div>
                      <div class="item-card-grid">`;

            filteredTemplates.forEach((template) => {
              const systemBadge =
                template.is_system == 1
                  ? '<span class="badge badge-system"><i class="fas fa-lock"></i> System</span>'
                  : "";
              html += `<div class="item-card" data-template-id="${template.dtid}" tabindex="0">
                        <div class="template-preview">
                          ${this.renderTemplatePreview(template)}
                        </div>
                        <div class="item-card-content">
                          <h3>${template.name}</h3>
                          ${template.description ? `<p class="item-card-description">${template.description}</p>` : ""}
                          ${systemBadge ? `<div class="item-card-tags">${systemBadge}</div>` : ""}
                        </div>
                      </div>`;
            });

            html += `</div></div>`;
          }
        }

        if (html) {
          templateList.innerHTML = html;
          this.addSectionTemplatesLoaded = true;
          this.focusFirstAddSectionCard();
        } else {
          templateList.innerHTML = `<div class="empty-state">
            <i class="fas fa-th-large"></i>
            <p>No templates available</p>
          </div>`;
        }
      } else {
        templateList.innerHTML = `<div class="empty-state">
          <i class="fas fa-th-large"></i>
          <p>No templates available</p>
        </div>`;
      }
    } catch (error) {
      console.error("Failed to load templates:", error);
      templateList.innerHTML = `<div class="empty-state">
        <i class="fas fa-exclamation-triangle"></i>
        <p>Failed to load templates</p>
      </div>`;
    }
  }

  /**
   * Focus first item-card in add-section-modal when both modal is shown and templates are loaded
   */
  focusFirstAddSectionCard() {
    if (this.addSectionModalShown && this.addSectionTemplatesLoaded) {
      const addSectionModal = document.getElementById("add-section-modal");
      if (addSectionModal) {
        const firstCard = addSectionModal.querySelector(".item-card");
        if (firstCard) {
          firstCard.focus();
        }
      }
    }
  }

  /**
   * Add sections from a template card click
   */
  async addSectionFromTemplateCard(templateId) {
    const position =
      this.pendingAddPosition !== undefined ? this.pendingAddPosition : 0;

    await this.closeAddSectionModal();
    Loading.show("Adding sections from template...");

    try {
      await this.addSectionsFromTemplate(templateId, position);
    } catch (error) {
      console.error("Add section from template error:", error);
      Toast.error("Failed to add sections from template");
    } finally {
      Loading.hide();
    }
  }

  /**
   * Add sections from another template
   */
  async addSectionsFromTemplate(templateId, position) {
    try {
      // First, get the template structure
      const result = await Ajax.post("get_template", {
        id: templateId,
      });

      if (!result.success || !result.data) {
        Toast.error("Failed to load template");
        return;
      }

      const templateData = result.data;
      let templateStructure;

      try {
        templateStructure =
          typeof templateData.structure === "string"
            ? JSON.parse(templateData.structure)
            : templateData.structure;
      } catch (e) {
        Toast.error("Invalid template structure");
        return;
      }

      if (
        !templateStructure.sections ||
        templateStructure.sections.length === 0
      ) {
        Toast.error("Template has no sections");
        return;
      }

      if (this.mode === "template") {
        // In template mode, directly modify the structure
        const structure = JSON.parse(this.currentDashboard.structure);

        // Store the first new section ID for focus
        let firstNewSectionId = null;

        // Add each section from the template with new IDs
        templateStructure.sections.forEach((section, index) => {
          const newSection = this.cloneSectionWithNewIds(section);
          if (index === 0) {
            firstNewSectionId = newSection.sid;
          }
          structure.sections.splice(position + index, 0, newSection);
        });

        // Update current dashboard structure
        this.currentDashboard.structure = JSON.stringify(structure);

        // Re-render
        this.renderDashboard();

        // Auto-save
        await this.saveDashboard(false);

        const sectionCount = templateStructure.sections.length;
        Toast.success(
          `Added ${sectionCount} section${sectionCount > 1 ? "s" : ""} from template`,
        );

        // Focus on first area of newly added section
        this.focusOnNewlyAddedSection(firstNewSectionId, null);
      } else {
        // In dashboard mode, use API call
        const apiResult = await Ajax.post("add_section_from_template", {
          dashboard_id: this.dashboardId,
          template_id: templateId,
          position: position === 0 ? "top" : "bottom",
        });

        if (apiResult.success) {
          await this.loadDashboard();
          Toast.success("Sections added from template");

          // Focus on first area of newly added section
          this.focusOnNewlyAddedSection(null, position);
        } else {
          Toast.error(apiResult.message);
        }
      }
    } catch (error) {
      console.error("Add sections from template error:", error);
      Toast.error("Failed to add sections from template");
    }
  }

  /**
   * Focus on the first area of a section after adding (template or empty section)
   * Used for keyboard navigation continuity
   * @param {string|null} sectionId - Direct section ID (for template mode)
   * @param {number|null} position - Position index to find section in DOM (for dashboard mode)
   */
  focusOnNewlyAddedSection(sectionId = null, position = null) {
    if (!window.keyboardNavigation?.isNavigationEnabled()) {
      return;
    }

    // Use setTimeout to ensure DOM is fully ready after modal close and render
    setTimeout(() => {
      let targetSectionId = sectionId;

      // If no direct sectionId, find by position in DOM
      if (!targetSectionId && position !== null) {
        const sections = this.container.querySelectorAll(".dashboard-section");
        const targetSection = sections[position];
        if (targetSection) {
          targetSectionId = targetSection.dataset.sectionId;
        }
      }

      if (targetSectionId) {
        window.keyboardNavigation.restoreFocusToArea(targetSectionId, 0, null);
      }
    }, 150);
  }

  /**
   * Clone a section with new unique IDs
   */
  cloneSectionWithNewIds(section) {
    const newSection = {
      sid: IdGenerator.sectionId(),
      gridTemplate: section.gridTemplate,
      areas: [],
    };

    if (section.areas) {
      section.areas.forEach((area) => {
        const newArea = {
          aid: IdGenerator.areaId(),
          colSpan: area.colSpan,
          colSpanFr: area.colSpanFr,
          content: area.content ? { ...area.content } : { type: "empty" },
          emptyState: area.emptyState
            ? { ...area.emptyState }
            : { icon: "fa-plus", message: "Add content" },
        };

        // Handle sub-rows if present
        if (area.hasSubRows && area.subRows) {
          newArea.hasSubRows = true;
          newArea.subRows = area.subRows.map((subRow) => ({
            rowId: IdGenerator.rowId(),
            height: subRow.height || "1fr",
            content: subRow.content ? { ...subRow.content } : { type: "empty" },
            emptyState: subRow.emptyState
              ? { ...subRow.emptyState }
              : { icon: "fa-plus", message: "Add content" },
          }));
        }

        newSection.areas.push(newArea);
      });
    }

    return newSection;
  }

  async removeSection(sectionId) {
    // Store focused element for restoration if cancelled
    const focusedElement = document.activeElement;

    const confirmed = await ConfirmDialog.delete(
      "Remove this section?",
      "Confirm Delete",
    );
    if (!confirmed) {
      // Restore focus if navigation mode is on
      if (
        window.keyboardNavigation?.isNavigationEnabled() &&
        focusedElement &&
        document.contains(focusedElement)
      ) {
        focusedElement.focus();
      }
      return;
    }

    Loading.show("Removing section...");

    try {
      // Use appropriate endpoint based on mode
      const action =
        this.mode === "template" ? "remove_template_section" : "remove_section";
      const idParam =
        this.mode === "template"
          ? { template_id: this.templateId }
          : { dashboard_id: this.dashboardId };

      const result = await Ajax.post(action, {
        ...idParam,
        section_id: sectionId,
      });

      if (result.success) {
        // Reload based on mode
        if (this.mode === "template") {
          await this.loadTemplate();
        } else {
          await this.loadDashboard();
        }
        Toast.success("Section removed");
      } else {
        Toast.error(result.message);
      }
    } catch (error) {
      Toast.error("Failed to remove section");
    } finally {
      Loading.hide();
    }
  }
}

// Expose to window
window.DashboardBuilder = DashboardBuilder;
window.TemplateManager = TemplateManager;

// Initialize template management globally (event delegation handles all pages)
TemplateManager.initTemplateList();
