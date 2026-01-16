import Sortable from "sortablejs";

/**
 * Template Manager
 * Handles template CRUD operations
 */

export class TemplateManager {
  static initialized = false;
  static categorySortable = null;
  static templateSortables = [];

  /**
   * Initialize template list page handlers using event delegation
   */
  static initTemplateList() {
    // Prevent multiple initializations
    if (TemplateManager.initialized) {
      return;
    }
    TemplateManager.initialized = true;

    // Initialize sortables for template list page
    TemplateManager.initSortables();

    // Initialize collapse/expand functionality
    TemplateManager.initCollapseToggle();

    // Use event delegation on document body for dynamic elements
    document.body.addEventListener("click", (e) => {
      // Check if delete button was clicked
      if (e.target.closest(".delete-template-btn")) {
        const btn = e.target.closest(".delete-template-btn");
        const templateId = btn.dataset.templateId;
        TemplateManager.deleteTemplate(templateId);
      }

      // Check if duplicate button was clicked
      if (e.target.closest(".duplicate-template-btn")) {
        const btn = e.target.closest(".duplicate-template-btn");
        const templateId = btn.dataset.templateId;
        TemplateManager.duplicateTemplate(templateId);
      }

      // Check if delete category button was clicked
      if (e.target.closest(".delete-category-btn")) {
        const btn = e.target.closest(".delete-category-btn");
        const categoryId = btn.dataset.categoryId;
        const categoryName = btn.dataset.categoryName;
        TemplateManager.deleteCategory(categoryId, categoryName);
      }

      // Check if category collapse toggle was clicked
      if (e.target.closest(".category-collapse-toggle")) {
        const btn = e.target.closest(".category-collapse-toggle");
        const section = btn.closest(".template-category-section");
        if (section) {
          TemplateManager.toggleCategory(section);
        }
      }

      // Check if toggle all categories button was clicked
      if (e.target.closest("#toggle-all-categories")) {
        TemplateManager.toggleAllCategories();
      }
    });
  }

  /**
   * Initialize collapse/expand toggle functionality
   */
  static initCollapseToggle() {
    // Restore collapsed state from localStorage
    const collapsedCategories = JSON.parse(localStorage.getItem("collapsedTemplateCategories") || "[]");
    collapsedCategories.forEach(categoryId => {
      const section = document.querySelector(`.template-category-section[data-category-id="${categoryId}"]`);
      if (section) {
        section.classList.add("collapsed");
      }
    });

    // Update toggle all button text
    TemplateManager.updateToggleAllButton();
  }

  /**
   * Toggle a single category's collapsed state
   * @param {HTMLElement} section - The category section element
   */
  static toggleCategory(section) {
    section.classList.toggle("collapsed");
    TemplateManager.saveCollapsedState();
    TemplateManager.updateToggleAllButton();
  }

  /**
   * Toggle all categories collapsed/expanded
   */
  static toggleAllCategories() {
    const sections = document.querySelectorAll(".template-category-section");
    const allCollapsed = Array.from(sections).every(s => s.classList.contains("collapsed"));

    sections.forEach(section => {
      if (allCollapsed) {
        section.classList.remove("collapsed");
      } else {
        section.classList.add("collapsed");
      }
    });

    TemplateManager.saveCollapsedState();
    TemplateManager.updateToggleAllButton();
  }

  /**
   * Save collapsed state to localStorage
   */
  static saveCollapsedState() {
    const collapsedCategories = [];
    document.querySelectorAll(".template-category-section.collapsed").forEach(section => {
      const categoryId = section.dataset.categoryId;
      if (categoryId) {
        collapsedCategories.push(categoryId);
      }
    });
    localStorage.setItem("collapsedTemplateCategories", JSON.stringify(collapsedCategories));
  }

  /**
   * Update the toggle all button text based on current state
   */
  static updateToggleAllButton() {
    const btn = document.getElementById("toggle-all-categories");
    if (!btn) return;

    const sections = document.querySelectorAll(".template-category-section");
    const allCollapsed = Array.from(sections).every(s => s.classList.contains("collapsed"));

    const icon = btn.querySelector("i");
    const text = btn.querySelector("span");

    if (allCollapsed) {
      icon.className = "fas fa-expand-alt";
      text.textContent = "Expand All";
      btn.title = "Expand All";
    } else {
      icon.className = "fas fa-compress-alt";
      text.textContent = "Collapse All";
      btn.title = "Collapse All";
    }
  }

  /**
   * Delete template with confirmation
   * @param {number} templateId - Template ID to delete
   */
  static async deleteTemplate(templateId) {
    const Toast = window.Toast;
    const Loading = window.Loading;
    const Ajax = window.Ajax;
    const ConfirmDialog = window.ConfirmDialog;

    if (!templateId) {
      Toast.error("Invalid template ID");
      return;
    }

    // Confirm deletion
    const confirmed = await ConfirmDialog.delete(
      "Are you sure you want to delete this template? This action cannot be undone.",
      "Delete Template"
    );

    if (!confirmed) return;

    // Show loading state
    Loading.show("Deleting template...");

    try {
      const result = await Ajax.post("delete_template", { id: templateId });

      if (result.success) {
        Toast.success(result.message || "Template deleted successfully");

        // Remove template card from UI
        const templateCard = document.querySelector(
          `.template-card[data-template-id="${templateId}"]`
        );
        if (templateCard) {
          templateCard.style.opacity = "0";
          templateCard.style.transform = "scale(0.95)";
          setTimeout(() => {
            templateCard.remove();

            // Check if category section is now empty
            const categorySection = templateCard.closest(
              ".template-category-section"
            );
            if (categorySection) {
              const remainingCards =
                categorySection.querySelectorAll(".template-card");
              if (remainingCards.length === 0) {
                categorySection.remove();
              }
            }

            // Check if page is now empty
            const allCards = document.querySelectorAll(".template-card");
            if (allCards.length === 0) {
              // Reload page to show empty state
              window.location.reload();
            }
          }, 300);
        } else {
          // Not on template list page (e.g., preview page), redirect to template list
          setTimeout(() => {
            window.location.href = "?urlq=dashboard/templates";
          }, 500);
        }
      } else {
        Toast.error(result.message || "Failed to delete template");
      }
    } catch (error) {
      console.error("Delete template error:", error);
      Toast.error("Failed to delete template");
    } finally {
      Loading.hide();
    }
  }

  /**
   * Duplicate template
   * @param {number} templateId - Template ID to duplicate
   */
  static async duplicateTemplate(templateId) {
    const Toast = window.Toast;
    const Loading = window.Loading;
    const Ajax = window.Ajax;
    const ConfirmDialog = window.ConfirmDialog;

    if (!templateId) {
      Toast.error("Invalid template ID");
      return;
    }

    // Confirm duplication
    const confirmed = await ConfirmDialog.show({
      message: "This will create a copy of this template. Do you want to continue?",
      title: "Duplicate Template",
      confirmText: "Duplicate",
      confirmClass: "btn-success"
    });

    if (!confirmed) return;

    // Show loading state
    Loading.show("Duplicating template...");

    try {
      const result = await Ajax.post("duplicate_template", { id: templateId });

      if (result.success) {
        Toast.success(result.message || "Template duplicated successfully");

        // Redirect to template builder if redirect URL provided
        if (result.data && result.data.redirect) {
          setTimeout(() => {
            window.location.href = result.data.redirect;
          }, 500);
        } else {
          // Reload page to show new template
          setTimeout(() => {
            window.location.reload();
          }, 500);
        }
      } else {
        Toast.error(result.message || "Failed to duplicate template");
        Loading.hide();
      }
    } catch (error) {
      console.error("Duplicate template error:", error);
      Toast.error("Failed to duplicate template");
      Loading.hide();
    }
  }

  /**
   * Delete category with confirmation
   * @param {number} categoryId - Category ID to delete
   * @param {string} categoryName - Category name for confirmation message
   */
  static async deleteCategory(categoryId, categoryName) {
    const Toast = window.Toast;
    const Loading = window.Loading;
    const Ajax = window.Ajax;
    const ConfirmDialog = window.ConfirmDialog;

    if (!categoryId) {
      Toast.error("Invalid category ID");
      return;
    }

    // Confirm deletion
    const confirmed = await ConfirmDialog.delete(
      `Are you sure you want to delete the category "${categoryName}"?`,
      "Delete Category"
    );

    if (!confirmed) return;

    // Show loading state
    Loading.show("Deleting category...");

    try {
      const result = await Ajax.post("delete_category", { id: categoryId });

      if (result.success) {
        Toast.success(result.message || "Category deleted successfully");

        // Remove category section from UI
        const categoryCard = document.querySelector(
          `.template-card-empty[data-category-id="${categoryId}"]`
        );
        if (categoryCard) {
          const categorySection = categoryCard.closest(".template-category-section");
          if (categorySection) {
            categorySection.style.opacity = "0";
            categorySection.style.transform = "translateY(-10px)";
            categorySection.style.transition = "all 0.3s ease";
            setTimeout(() => {
              categorySection.remove();

              // Check if page is now empty
              const allSections = document.querySelectorAll(".template-category-section");
              if (allSections.length === 0) {
                // Reload page to show empty state
                window.location.reload();
              }
            }, 300);
          }
        }
      } else {
        Toast.error(result.message || "Failed to delete category");
      }
    } catch (error) {
      console.error("Delete category error:", error);
      Toast.error("Failed to delete category");
    } finally {
      Loading.hide();
    }
  }

  /**
   * Create scroll function with gradual acceleration
   * Speed increases as cursor gets closer to edge
   */
  static createScrollFn() {
    const sensitivity = 150; // Distance from edge where scrolling starts
    const minSpeed = 5;      // Minimum scroll speed
    const maxSpeed = 40;     // Maximum scroll speed at very edge

    return (offsetX, offsetY, originalEvent) => {
      const scrollEl = document.documentElement;
      const viewportHeight = window.innerHeight;
      const mouseY = originalEvent.clientY;

      // Calculate distance from edges
      const distanceFromTop = mouseY;
      const distanceFromBottom = viewportHeight - mouseY;

      let scrollAmount = 0;

      if (distanceFromTop < sensitivity) {
        // Scroll up - speed increases as we get closer to top
        const ratio = 1 - (distanceFromTop / sensitivity);
        scrollAmount = -(minSpeed + (maxSpeed - minSpeed) * Math.pow(ratio, 2));
      } else if (distanceFromBottom < sensitivity) {
        // Scroll down - speed increases as we get closer to bottom
        const ratio = 1 - (distanceFromBottom / sensitivity);
        scrollAmount = minSpeed + (maxSpeed - minSpeed) * Math.pow(ratio, 2);
      }

      if (scrollAmount !== 0) {
        scrollEl.scrollTop += scrollAmount;
      }
    };
  }

  /**
   * Initialize sortables for category and template reordering
   */
  static initSortables() {
    const categoryList = document.getElementById("category-list");
    if (!categoryList) return; // Not on template list page

    const Toast = window.Toast;
    const Ajax = window.Ajax;
    const scrollFn = TemplateManager.createScrollFn();

    // Category sortable - drag entire category sections
    TemplateManager.categorySortable = Sortable.create(categoryList, {
      animation: 150,
      handle: ".category-drag-handle",
      draggable: ".template-category-section",
      ghostClass: "sortable-ghost",
      chosenClass: "sortable-chosen",
      forceFallback: true,
      scroll: true,
      scrollFn: scrollFn,
      scrollSensitivity: 150,
      bubbleScroll: true,
      onEnd: async (evt) => {
        if (evt.oldIndex === evt.newIndex) return;

        const categories = categoryList.querySelectorAll(".template-category-section");
        const order = Array.from(categories).map(cat => cat.dataset.categoryId);

        try {
          const result = await Ajax.post("reorder_categories", { order: order });
          if (result.success) {
            Toast.success("Categories reordered");
          } else {
            Toast.error(result.message || "Failed to reorder categories");
            window.location.reload();
          }
        } catch (error) {
          console.error("Reorder categories error:", error);
          Toast.error("Failed to reorder categories");
          window.location.reload();
        }
      }
    });

    // Template sortables - one for each category's template grid
    const templateGrids = document.querySelectorAll(".template-grid");
    templateGrids.forEach(grid => {
      // Skip grids that only contain empty state cards
      const hasTemplates = grid.querySelector(".template-card:not(.template-card-empty)");
      if (!hasTemplates) return;

      const sortable = Sortable.create(grid, {
        animation: 150,
        handle: ".template-drag-handle",
        draggable: ".template-card:not(.template-card-empty)",
        ghostClass: "sortable-ghost",
        chosenClass: "sortable-chosen",
        forceFallback: true,
        scroll: true,
        scrollFn: scrollFn,
        scrollSensitivity: 150,
        bubbleScroll: true,
        onEnd: async (evt) => {
          if (evt.oldIndex === evt.newIndex) return;

          const templates = grid.querySelectorAll(".template-card:not(.template-card-empty)");
          const order = Array.from(templates).map(card => card.dataset.templateId);

          try {
            const result = await Ajax.post("reorder_templates", { order: order });
            if (result.success) {
              Toast.success("Templates reordered");
            } else {
              Toast.error(result.message || "Failed to reorder templates");
              window.location.reload();
            }
          } catch (error) {
            console.error("Reorder templates error:", error);
            Toast.error("Failed to reorder templates");
            window.location.reload();
          }
        }
      });

      TemplateManager.templateSortables.push(sortable);
    });
  }
}
