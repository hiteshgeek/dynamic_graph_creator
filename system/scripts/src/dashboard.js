/**
 * Dashboard Module Entry Point
 * Simplified version for initial implementation
 */

import Sortable from "sortablejs";
import { TemplateManager } from "./dashboard/TemplateManager.js";

// Use globals from common.js (Toast, Loading, Ajax, ConfirmDialog)
const Toast = window.Toast;
const Loading = window.Loading;
const Ajax = window.Ajax;
const ConfirmDialog = window.ConfirmDialog;

// Grid configuration constants
// Max total fr for a section is MAX_COLUMNS (4fr), not numColumns * MAX_FR_UNITS
const GRID_CONFIG = {
  MAX_FR_UNITS: 4, // Maximum fr units for any single column/row (4fr max)
  MIN_FR_UNITS: 1, // Minimum fr units for any column/row
  MAX_COLUMNS: 4, // Maximum columns per section AND max total fr
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
  }

  init() {
    if (this.mode === "template" && this.templateId) {
      this.loadTemplate();
    } else if (this.dashboardId) {
      this.loadDashboard();
    } else {
      this.showTemplateSelector();
    }
    this.initEventHandlers();
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

    // Clear dashboard name input and validation state
    const nameInput = document.getElementById("new-dashboard-name");
    if (nameInput) {
      nameInput.value = "";
      nameInput.classList.remove("is-invalid");
    }

    // Get or create Bootstrap modal instance
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) {
      modal = new bootstrap.Modal(modalElement);
    }
    modal.show();

    try {
      const result = await Ajax.post("get_templates", {});

      if (result.success) {
        this.renderTemplates(result.data);
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
                        ${categoryDescription ? `<p>${categoryDescription}</p>` : ''}
                    </div>
                    <div class="template-grid">`;

        categoryData.templates.forEach((template) => {
          const systemBadge = template.is_system == 1
            ? '<span class="badge badge-system"><i class="fas fa-lock"></i> System</span>'
            : '';
          html += `<div class="template-card" data-template-id="${
            template.dtid
          }">
                        <div class="template-preview">
                            ${this.renderTemplatePreview(template)}
                        </div>
                        <div class="template-info">
                            <h4>${template.name}</h4>
                            <p>${template.description || ""}</p>
                            ${systemBadge ? `<div class="template-meta">${systemBadge}</div>` : ''}
                        </div>
                    </div>`;
        });

        html += `</div></div>`;
      }
    }

    templateList.innerHTML = html;

    // Add click handlers
    templateList.querySelectorAll(".template-card").forEach((card) => {
      card.addEventListener("click", () => {
        const templateId = parseInt(card.dataset.templateId);
        if (this.templateSelectorMode === "add-section") {
          this.addSectionFromTemplate(templateId);
        } else {
          this.createFromTemplate(templateId);
        }
      });
    });
  }

  async createFromTemplate(templateId) {
    // Validate dashboard name
    const nameInput = document.getElementById("new-dashboard-name");
    const dashboardName = nameInput ? nameInput.value.trim() : "";

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

    try {
      const result = await Ajax.post("add_section_from_template", {
        dashboard_id: this.dashboardId,
        template_id: templateId,
        position: this.pendingAddPosition,
      });

      if (result.success) {
        await this.loadDashboard();

        // Close modal using Bootstrap API
        const modalElement = document.getElementById("template-modal");
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }

        // Reset mode
        this.templateSelectorMode = "create-dashboard";

        Toast.success("Section added successfully");
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

    // Add View Dashboard button if it doesn't exist
    const headerRight = document.querySelector(".page-header-right");
    if (headerRight && this.dashboardId) {
      const existingViewBtn = headerRight.querySelector(
        'a[href*="dashboard/preview"]'
      );
      if (!existingViewBtn) {
        const viewBtn = document.createElement("a");
        viewBtn.href = `?urlq=dashboard/preview/${this.dashboardId}`;
        viewBtn.className = "btn btn-primary";
        viewBtn.title = "View Dashboard";
        viewBtn.innerHTML = '<i class="fas fa-eye"></i> View Dashboard';
        headerRight.appendChild(viewBtn);
      }
    }
  }

  updateSidebarAfterCreation() {
    // Replace the choose-template-card with the dashboard sections container
    const gridEditor = document.querySelector(".grid-editor");
    if (!gridEditor) return;

    gridEditor.innerHTML = `
            <div class="dashboard-sections">
                <div class="loading-message">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading dashboard...</p>
                </div>
            </div>
        `;
  }

  renderDashboard() {
    const sectionsContainer = this.container.querySelector(".dashboard-sections");
    if (!sectionsContainer) return;

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
                        <button class="btn btn-primary add-first-section-btn">
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
        ".add-first-section-btn"
      );
      if (addFirstBtn) {
        addFirstBtn.addEventListener("click", async () => {
          this.pendingAddPosition = 0;

          if (this.mode === "template") {
            // In template mode, show simple add section modal
            const modal = new bootstrap.Modal(
              document.getElementById("add-section-modal")
            );
            modal.show();
          } else {
            // In dashboard mode, show template selector
            this.templateSelectorMode = "add-section";
            await this.showTemplateSelector();
          }
        });
      }
    }
  }

  initAddSectionBorderButtons() {
    const borderBtns = document.querySelectorAll(".add-section-border-btn");
    if (borderBtns.length > 0) {
      borderBtns.forEach((btn) => {
        btn.addEventListener("click", async () => {
          const position = parseInt(btn.dataset.position);
          // Store the position for the add section handler
          this.pendingAddPosition = position;

          if (this.mode === "template") {
            // In template mode, show simple add section modal
            const modal = new bootstrap.Modal(
              document.getElementById("add-section-modal")
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
      const canExpandCol = widths[areaIndex] < GRID_CONFIG.MAX_FR_UNITS && hasRoomToGrowResize;
      const hasResizeOptions = canShrinkCol || canExpandCol;

      // Calculate add column options
      // Can add if: under max columns AND (there's room to grow OR ANY column can give 1fr)
      // Max total is always MAX_COLUMNS (4fr) regardless of column count
      const hasRoomToGrow = totalFr < maxTotalFr;
      const anyColumnCanGive = widths.some(w => w > GRID_CONFIG.MIN_FR_UNITS);
      const canAddCol = canAddColumn && (hasRoomToGrow || anyColumnCanGive);

      // Both left and right add column buttons use the same logic
      const canAddColLeft = canAddCol;
      const canAddColRight = canAddCol;

      // Column drag handle - only show when more than one column
      const columnDragHandle = numColumns > 1
        ? `<button class="column-drag-handle" title="Drag to reorder column">
                    <i class="fas fa-grip-vertical"></i>
                </button>`
        : '';

      // Area controls - edge buttons and center resize/delete
      const areaControls = `<div class="area-controls-overlay">
                ${columnDragHandle}
                <!-- Top: Add Row Above (splits column into rows) -->
                <button class="edge-btn edge-top add-row-top-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" title="Add row above">
                    Add Row Above
                </button>
                <!-- Bottom: Add Row Below (splits column into rows) -->
                <button class="edge-btn edge-bottom add-row-bottom-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" title="Add row below">
                    Add Row Below
                </button>
                <!-- Left: Add Column Left -->
                <button class="edge-btn edge-left add-col-left-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" title="Add column to left" ${!canAddColLeft ? 'disabled' : ''}>
                    Add Column Left
                </button>
                <!-- Right: Add Column Right -->
                <button class="edge-btn edge-right add-col-right-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" title="Add column to right" ${!canAddColRight ? 'disabled' : ''}>
                    Add Column Right
                </button>
                <!-- Center: Resize buttons + Delete -->
                <div class="center-controls">
                    <div class="center-row">
                        <button class="center-btn resize-col-left-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" title="Shrink column" ${!canShrinkCol ? 'disabled' : ''}>
                            <i class="fas fa-caret-left"></i>
                        </button>
                        <button class="center-btn delete-btn remove-col-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" title="Remove column" ${!canRemoveColumn ? 'disabled' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="center-btn resize-col-right-btn" data-section-id="${section.sid}" data-area-index="${areaIndex}" title="Expand column" ${!canExpandCol ? 'disabled' : ''}>
                            <i class="fas fa-caret-right"></i>
                        </button>
                    </div>
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
          numColumns
        );
      } else {
        // Regular single area with controls inside
        areasHtml += `<div class="dashboard-area" data-area-id="${area.aid}" data-area-index="${areaIndex}">
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
            <button class="section-control-btn drag-handle" title="Drag to reorder">
                <i class="fas fa-grip-vertical"></i>
            </button>
        `
        : "";

    const topBorderControls = `
            <div class="section-top-border-controls">
                ${dragHandleHtml}
                <button class="section-control-btn remove-btn" data-section-id="${section.sid}" title="Remove section">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

    // Add section button on top border
    const topBorderButton = `
            <button class="add-section-border-btn add-section-top-btn" data-position="${index}" title="Add section above">
                <i class="fas fa-plus"></i>
                <span>Add Section Above</span>
            </button>
        `;

    // Add section button on bottom border
    const bottomBorderButton = `
            <button class="add-section-border-btn add-section-bottom-btn" data-position="${
              index + 1
            }" title="Add section below">
                <i class="fas fa-plus"></i>
                <span>Add Section Below</span>
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
        const rowHeightParts = area.subRows.map((r) => {
          const height = r.height || '1fr';
          return `<span class="grid-size-row" data-row-id="${r.rowId}">${height}</span>`;
        }).join('<span class="grid-size-row-sep">/</span>');
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
    numColumns = 1
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
    const columnDragHandle = numColumns > 1
      ? `<button class="column-drag-handle" title="Drag to reorder column">
                <i class="fas fa-grip-vertical"></i>
            </button>`
      : '';

    let subRowsHtml = "";

    area.subRows.forEach((subRow, rowIndex) => {
      const isFirstRow = rowIndex === 0;

      // Row resize conditions
      // Expand (plus on top): can increase if current size < MAX AND total has room
      // Shrink (minus on bottom): can decrease if current size > MIN
      const canExpandRow = heights[rowIndex] < GRID_CONFIG.MAX_FR_UNITS && hasRoomToGrowRows;
      const canShrinkRow = heights[rowIndex] > GRID_CONFIG.MIN_FR_UNITS;

      // Row drag handle - only show when more than one row
      const rowDragHandle = numRows > 1
        ? `<button class="row-drag-handle" title="Drag to reorder row">
                    <i class="fas fa-grip-horizontal"></i>
                </button>`
        : '';

      // All controls inside each sub-row - both column and row actions
      // Each row shows Add Row Above (inserts above this row) and Add Row Below (inserts below this row)
      // Column drag handle only shown in first row
      const controls = `<div class="area-controls-overlay">
                ${isFirstRow ? columnDragHandle : ''}
                ${rowDragHandle}
                <!-- Column actions: Add Column Left/Right -->
                <button class="edge-btn edge-left add-col-left-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" title="Add column to left" ${!canAddColLeft ? 'disabled' : ''}>
                    Add Column Left
                </button>
                <button class="edge-btn edge-right add-col-right-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" title="Add column to right" ${!canAddColRight ? 'disabled' : ''}>
                    Add Column Right
                </button>
                <!-- Row actions: Add Row Above/Below - each row can add above or below itself -->
                <button class="edge-btn edge-top add-row-top-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" title="Add row above" ${!canAddRow ? 'disabled' : ''}>
                    Add Row Above
                </button>
                <button class="edge-btn edge-bottom add-row-bottom-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" title="Add row below" ${!canAddRow ? 'disabled' : ''}>
                    Add Row Below
                </button>
                <!-- Center: All resize buttons + Delete buttons -->
                <div class="center-controls">
                    <button class="center-btn resize-row-up-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" title="Expand row" ${!canExpandRow ? 'disabled' : ''}>
                        <i class="fas fa-caret-up"></i>
                    </button>
                    <div class="center-row">
                        <button class="center-btn resize-col-left-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" title="Shrink column" ${!canShrinkCol ? 'disabled' : ''}>
                            <i class="fas fa-caret-left"></i>
                        </button>
                        <button class="center-btn delete-btn remove-row-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" title="Remove row" ${!canRemoveRow ? 'disabled' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="center-btn resize-col-right-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" title="Expand column" ${!canExpandCol ? 'disabled' : ''}>
                            <i class="fas fa-caret-right"></i>
                        </button>
                    </div>
                    <button class="center-btn resize-row-down-btn" data-section-id="${sectionId}" data-area-index="${areaIndex}" data-row-index="${rowIndex}" title="Shrink row" ${!canShrinkRow ? 'disabled' : ''}>
                        <i class="fas fa-caret-down"></i>
                    </button>
                </div>
            </div>`;

      // Calculate min-height based on fr value (150px per fr unit, matching .dashboard-area min-height)
      const rowFr = parseInt(subRow.height) || 1;
      const minRowHeight = rowFr * 150;

      subRowsHtml += `<div class="dashboard-sub-row" data-row-id="${subRow.rowId}" data-row-index="${rowIndex}" style="min-height: ${minRowHeight}px;">
                ${controls}
                ${
                  subRow.content && subRow.content.type === "empty"
                    ? this.renderEmptyState(subRow.emptyState)
                    : this.renderContent(subRow.content)
                }
            </div>`;
    });

    // Calculate min-height for the nested container based on total fr (150px per fr unit)
    // This gives the grid actual space to distribute among rows
    const totalFrForHeight = heights.reduce((sum, h) => sum + h, 0);
    const nestedMinHeight = totalFrForHeight * 150;

    return `<div class="dashboard-area dashboard-area-nested" data-area-id="${area.aid}" data-area-index="${areaIndex}" style="grid-template-rows: ${rowHeights}; min-height: ${nestedMinHeight}px;">
            ${subRowsHtml}
        </div>`;
  }

  renderEmptyState(emptyState) {
    return `<div class="dashboard-cell-empty">
            <div class="cell-empty-icon">
                <i class="fas ${emptyState?.icon || "fa-plus-circle"}"></i>
            </div>
            <div class="cell-empty-message">
                ${emptyState?.message || "Add content here"}
            </div>
        </div>`;
  }

  renderContent(content) {
    return `<div class="area-content">
            <p>Widget: ${content?.widgetType || "Unknown"}</p>
        </div>`;
  }

  initDragDrop() {
    const sectionsContainer = this.container.querySelector(".dashboard-sections");

    // Destroy existing sortable instances
    if (this.sortableInstance) {
      this.sortableInstance.destroy();
    }
    if (this.columnSortables) {
      this.columnSortables.forEach(s => s.destroy());
    }
    if (this.rowSortables) {
      this.rowSortables.forEach(s => s.destroy());
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
      const columnCount = section.querySelectorAll(":scope > .dashboard-area").length;

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
    const nestedAreas = this.container.querySelectorAll(".dashboard-area-nested");
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
      (section) => section.dataset.sectionId
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
      `.dashboard-section[data-section-id="${sectionId}"]`
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
      `.dashboard-area-nested[data-area-id="${areaId}"]`
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
      const newRows = newOrder
        .map((rowId) => rowMap[rowId])
        .filter((r) => r);

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
              : "Dashboard saved successfully"
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
          : "Failed to save dashboard"
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

    // Choose template button (when no dashboard exists)
    const chooseTemplateBtn = document.querySelector(".choose-template-btn");
    if (chooseTemplateBtn) {
      chooseTemplateBtn.addEventListener("click", () => {
        this.showTemplateSelector();
      });
    }

    // Confirm add section
    const confirmBtn = document.getElementById("confirm-add-section");
    if (confirmBtn) {
      confirmBtn.addEventListener("click", () => this.handleAddSection());
    }

    // Remove section handlers - use event delegation on container instead of document
    this.container.addEventListener("click", (e) => {
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
      if (e.target.closest(".remove-column-btn") || e.target.closest(".remove-col-btn")) {
        const btn = e.target.closest(".remove-column-btn") || e.target.closest(".remove-col-btn");
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        this.removeColumn(sectionId, areaIndex);
      }

      // Add row above (inserts row above the current row, or splits column if not nested)
      if (e.target.closest(".add-row-top-btn")) {
        const btn = e.target.closest(".add-row-top-btn");
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        const rowIndex = btn.dataset.rowIndex !== undefined ? parseInt(btn.dataset.rowIndex) : 0;
        this.addRowAt(sectionId, areaIndex, rowIndex); // Insert at this position (pushes current down)
      }

      // Add row below (inserts row below the current row, or splits column if not nested)
      if (e.target.closest(".add-row-bottom-btn")) {
        const btn = e.target.closest(".add-row-bottom-btn");
        const sectionId = btn.dataset.sectionId;
        const areaIndex = parseInt(btn.dataset.areaIndex);
        const rowIndex = btn.dataset.rowIndex !== undefined ? parseInt(btn.dataset.rowIndex) : -1;
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
    });

    // Hover event delegation - highlight grid-size-indicator part when hovering over area
    // Helper function to highlight indicator part (for regular areas)
    const highlightIndicatorPart = (areaId, sectionWrapper, add) => {
      if (sectionWrapper && areaId) {
        const indicator = sectionWrapper.querySelector(".grid-size-indicator");
        if (indicator) {
          const part = indicator.querySelector(`.grid-size-part[data-area-id="${areaId}"]`);
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
          const rowPart = indicator.querySelector(`.grid-size-row[data-row-id="${rowId}"]`);
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
        highlightIndicatorPart(area.dataset.areaId, area.closest(".dashboard-section-wrapper"), true);
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
        highlightIndicatorPart(area.dataset.areaId, area.closest(".dashboard-section-wrapper"), false);
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
      if (widths[areaIndex] < GRID_CONFIG.MAX_FR_UNITS && totalFr < maxTotalFr) {
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
      if (heights[rowIndex] < GRID_CONFIG.MAX_FR_UNITS && totalFr < maxTotalFr) {
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
      emptyState: { icon: "fa-plus-circle", message: "Add content" },
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

  async removeColumn(sectionId, areaIndex) {
    if (this.isReadOnly) return;

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

        // Add removed width to recipient, but cap at MAX_FR_UNITS
        const newWidth = widths[recipientIndex] + removedWidth;
        widths[recipientIndex] = Math.min(newWidth, GRID_CONFIG.MAX_FR_UNITS);
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
        icon: "fa-plus-circle",
        message: "Add content",
      };

      if (position === 0) {
        // Add at top: new empty row first, existing content second
        area.subRows = [
          {
            rowId: IdGenerator.rowId(),
            height: `${GRID_CONFIG.DEFAULT_NEW_ROW_FR}fr`,
            content: { type: "empty" },
            emptyState: { icon: "fa-plus-circle", message: "Add content" },
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
            emptyState: { icon: "fa-plus-circle", message: "Add content" },
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
      emptyState: { icon: "fa-plus-circle", message: "Add content" },
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

  async removeRow(sectionId, areaIndex, rowIndex) {
    if (this.isReadOnly) return;

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

  async handleAddSection() {
    const columns = document.getElementById("section-columns").value;
    // Use the stored position from the button click
    const position =
      this.pendingAddPosition !== undefined ? this.pendingAddPosition : 0;
    const modalElement = document.getElementById("add-section-modal");
    const modalInstance = bootstrap.Modal.getInstance(modalElement);

    // Close modal first
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

    Loading.show("Adding section...");

    try {
      if (this.mode === "template") {
        // In template mode, directly modify the structure
        const structure = JSON.parse(this.currentDashboard.structure);

        // Generate new section ID
        const newSectionId = IdGenerator.sectionId();

        // Create column template
        const colWidths = Array(parseInt(columns)).fill("1fr").join(" ");

        // Create areas for the new section
        const areas = [];
        for (let i = 0; i < parseInt(columns); i++) {
          areas.push({
            aid: IdGenerator.areaId(),
            colSpanFr: "1fr",
            content: { type: "empty" },
            emptyState: {
              icon: "fa-plus-circle",
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

  async removeSection(sectionId) {
    const confirmed = await ConfirmDialog.delete(
      "Remove this section?",
      "Confirm Delete"
    );
    if (!confirmed) return;

    Loading.show("Removing section...");

    try {
      // Use appropriate endpoint based on mode
      const action = this.mode === "template" ? "remove_template_section" : "remove_section";
      const idParam = this.mode === "template"
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
