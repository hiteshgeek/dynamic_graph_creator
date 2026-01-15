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
    const modal = document.getElementById("template-modal");
    if (!modal) return;

    modal.style.display = "flex";

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
    const modal = document.getElementById("template-modal");
    const body = modal.querySelector(".modal-body");

    const categories = {
      columns: "Column Dashboards",
      rows: "Row Dashboards",
      mixed: "Mixed Dashboards",
      advanced: "Advanced Dashboards",
    };

    let html = "";

    for (const [category, label] of Object.entries(categories)) {
      if (templates[category] && templates[category].length > 0) {
        html += `<div class="template-category">
                    <h3>${label}</h3>
                    <div class="template-grid">`;

        templates[category].forEach((template) => {
          html += `<div class="template-card" data-template-id="${
            template.dtid
          }">
                        <div class="template-preview">
                            ${this.renderTemplatePreview(template)}
                        </div>
                        <div class="template-info">
                            <h4>${template.name}</h4>
                            <p>${template.description || ""}</p>
                        </div>
                    </div>`;
        });

        html += `</div></div>`;
      }
    }

    body.innerHTML = html;

    // Add click handlers
    body.querySelectorAll(".template-card").forEach((card) => {
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
    Loading.show("Creating dashboard...");

    try {
      const result = await Ajax.post("create_from_template", {
        template_id: templateId,
        name: "New Dashboard",
      });

      if (result.success) {
        this.dashboardId = result.data.id;

        // Update URL to include dashboard ID (for refresh persistence)
        const newUrl = `?urlq=dashboard/builder/${this.dashboardId}`;
        window.history.pushState({ dashboardId: this.dashboardId }, "", newUrl);

        // Update container data attribute
        this.container.dataset.dashboardId = this.dashboardId;

        // Update page title
        document.title = "Edit Dashboard - Dynamic Graph Creator";

        // Update header title
        const headerTitle = document.querySelector(".page-header-left h1");
        if (headerTitle) {
          headerTitle.textContent = "Edit Dashboard";
        }

        // Show save indicator and View Dashboard button
        this.updateHeaderAfterCreation();

        // Update sidebar to show "Add Section" button instead of "Choose Template"
        this.updateSidebarAfterCreation();

        // Load the dashboard
        await this.loadDashboard();

        // Close modal
        document.getElementById("template-modal").style.display = "none";

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

        // Close modal
        document.getElementById("template-modal").style.display = "none";

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
      // Show empty state when no sections exist
      html = `
                <div class="dashboard-empty-sections">
                    <div class="empty-sections-content">
                        <i class="fas fa-th-large"></i>
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
    let areasHtml = "";

    section.areas.forEach((area) => {
      // Check if this area has sub-rows (nested structure)
      if (area.hasSubRows && area.subRows && area.subRows.length > 0) {
        areasHtml += this.renderAreaWithSubRows(area);
      } else {
        // Regular single area
        areasHtml += `<div class="dashboard-area" data-area-id="${area.aid}">
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
                <span>Add Section</span>
            </button>
        `;

    // Add section button on bottom border
    const bottomBorderButton = `
            <button class="add-section-border-btn add-section-bottom-btn" data-position="${
              index + 1
            }" title="Add section below">
                <i class="fas fa-plus"></i>
                <span>Add Section</span>
            </button>
        `;

    return `<div class="dashboard-section-wrapper">
            ${topBorderButton}
            ${topBorderControls}
            <div class="dashboard-section" data-section-id="${section.sid}" style="grid-template-columns: ${section.gridTemplate};">
                ${areasHtml}
            </div>
            ${bottomBorderButton}
        </div>`;
  }

  renderAreaWithSubRows(area) {
    // Build grid-template-rows from sub-row heights
    const rowHeights = area.subRows.map((row) => row.height || "1fr").join(" ");

    let subRowsHtml = "";
    area.subRows.forEach((subRow) => {
      subRowsHtml += `<div class="dashboard-sub-row" data-row-id="${subRow.rowId}">
                ${
                  subRow.content && subRow.content.type === "empty"
                    ? this.renderEmptyState(subRow.emptyState)
                    : this.renderContent(subRow.content)
                }
            </div>`;
    });

    return `<div class="dashboard-area dashboard-area-nested" data-area-id="${area.aid}" style="grid-template-rows: ${rowHeights};">
            ${subRowsHtml}
        </div>`;
  }

  renderEmptyState(emptyState) {
    return `<div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas ${emptyState?.icon || "fa-plus-circle"}"></i>
            </div>
            <div class="empty-state-message">
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

    if (this.sortableInstance) {
      this.sortableInstance.destroy();
    }

    this.sortableInstance = Sortable.create(sectionsContainer, {
      animation: 150,
      handle: ".drag-handle",
      draggable: ".dashboard-section-wrapper",
      ghostClass: "section-ghost",
      onEnd: () => this.onSectionsReorder(),
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

      console.log("Save result:", result);

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

    // Template modal close
    const modalClose = document.querySelector(
      ".template-modal .modal-close"
    );
    if (modalClose) {
      modalClose.addEventListener("click", () => {
        document.getElementById("template-modal").style.display = "none";
      });
    }

    // Remove section handlers - use event delegation on container instead of document
    this.container.addEventListener("click", (e) => {
      if (e.target.closest(".remove-btn")) {
        const btn = e.target.closest(".remove-btn");
        const sectionId = btn.dataset.sectionId;
        this.removeSection(sectionId);
      }
    });
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
        const newSectionId = "s" + Date.now();

        // Create column template
        const colWidths = Array(parseInt(columns)).fill("1fr").join(" ");

        // Create areas for the new section
        const areas = [];
        for (let i = 0; i < parseInt(columns); i++) {
          areas.push({
            aid: `${newSectionId}_a${i}`,
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
      const result = await Ajax.post("remove_section", {
        dashboard_id: this.dashboardId,
        section_id: sectionId,
      });

      if (result.success) {
        await this.loadDashboard();
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
