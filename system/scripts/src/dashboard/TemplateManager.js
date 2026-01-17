// Use Sortable from CDN (global)
const Sortable = window.Sortable;

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

    // Initialize sortables only if ordering is allowed
    if (window.__allowTemplateOrdering) {
      TemplateManager.initSortables();
    }

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

      // Check if category header was clicked (but not action buttons)
      if (e.target.closest(".category-header") &&
          !e.target.closest(".category-actions") &&
          !e.target.closest(".category-drag-handle")) {
        const header = e.target.closest(".category-header");
        const section = header.closest(".template-category-section");
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
    // Collapsed state is now applied inline in PHP template to prevent flash
    // Just clean up the global variable and update the toggle all button
    if (window.__collapsedCategories) {
      delete window.__collapsedCategories;
    }

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
          `.item-card[data-template-id="${templateId}"]`
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
                categorySection.querySelectorAll(".item-card");
              if (remainingCards.length === 0) {
                categorySection.remove();
              }
            }

            // Check if page is now empty
            const allCards = document.querySelectorAll(".item-card");
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
          `.item-card-empty[data-category-id="${categoryId}"]`
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

    // Track source category for cross-category moves
    let sourceCategoryId = null;
    let sourceCategoryName = null;
    let sourceGrid = null;
    let draggedTemplateName = null;
    let originalIndex = null;
    let draggedItem = null;
    let collapsedDropHandler = null;

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
    // Using group to allow cross-list dragging
    const templateGrids = document.querySelectorAll(".item-card-grid");
    templateGrids.forEach(grid => {
      const sortable = Sortable.create(grid, {
        group: "templates", // Allow dragging between grids
        animation: 150,
        handle: ".template-drag-handle",
        draggable: ".item-card:not(.item-card-empty)",
        ghostClass: "sortable-ghost",
        chosenClass: "sortable-chosen",
        dragClass: "sortable-drag",
        forceFallback: true,
        fallbackClass: "sortable-fallback",
        fallbackOnBody: true,
        fallbackTolerance: 0,
        scroll: true,
        scrollFn: scrollFn,
        scrollSensitivity: 150,
        bubbleScroll: true,

        onStart: (evt) => {
          // Store source category info
          const section = evt.from.closest(".template-category-section");
          sourceCategoryId = section ? section.dataset.categoryId : null;
          sourceCategoryName = section ? section.querySelector(".category-header h2")?.textContent : null;
          sourceGrid = evt.from;
          draggedTemplateName = evt.item.querySelector(".template-info h4")?.textContent || "Template";
          originalIndex = evt.oldIndex;
          draggedItem = evt.item;

          // Add class to body for cross-category drag styling
          document.body.classList.add("template-dragging");

          // Add drop zones for collapsed categories
          document.querySelectorAll(".template-category-section.collapsed").forEach(collapsedSection => {
            const header = collapsedSection.querySelector(".category-header");
            if (header && collapsedSection.dataset.categoryId !== sourceCategoryId) {
              header.classList.add("collapsed-drop-zone");
              // Add drop indicator element
              const dropIndicator = document.createElement("div");
              dropIndicator.className = "collapsed-drop-indicator";
              dropIndicator.innerHTML = '<i class="fas fa-plus-circle"></i> Drop here to add';
              header.appendChild(dropIndicator);
            }
          });

          // Track mouse position for collapsed category hover detection
          collapsedDropHandler = (e) => {
            const fallbackEl = document.querySelector(".sortable-fallback");
            let hoveredCollapsedSection = null;

            // Check if mouse is over any collapsed category header
            document.querySelectorAll(".template-category-section.collapsed").forEach(collapsedSection => {
              if (collapsedSection.dataset.categoryId === sourceCategoryId) return;

              const header = collapsedSection.querySelector(".category-header");
              if (!header) return;

              const rect = header.getBoundingClientRect();
              if (e.clientX >= rect.left && e.clientX <= rect.right &&
                  e.clientY >= rect.top && e.clientY <= rect.bottom) {
                hoveredCollapsedSection = collapsedSection;
              }
            });

            // Update visual feedback
            document.querySelectorAll(".template-category-section.collapsed").forEach(s => {
              s.classList.remove("drag-target-different-category");
            });

            if (hoveredCollapsedSection) {
              hoveredCollapsedSection.classList.add("drag-target-different-category");
              if (fallbackEl) {
                fallbackEl.classList.add("cross-category-drag");
              }
            }
          };

          document.addEventListener("mousemove", collapsedDropHandler);
        },

        onMove: (evt) => {
          // Add visual feedback when hovering over different category
          const targetSection = evt.to.closest(".template-category-section");
          const targetCategoryId = targetSection ? targetSection.dataset.categoryId : null;

          // Remove previous hover states
          document.querySelectorAll(".template-category-section").forEach(s => {
            s.classList.remove("drag-target-different-category");
          });

          // Get the fallback element (the clone following cursor)
          const fallbackEl = document.querySelector(".sortable-fallback");

          // Add hover state if different category
          if (targetCategoryId && targetCategoryId !== sourceCategoryId) {
            targetSection.classList.add("drag-target-different-category");
            // Highlight the dragged item to indicate cross-category move
            if (fallbackEl) {
              fallbackEl.classList.add("cross-category-drag");
            }
          } else {
            // Remove cross-category highlight
            if (fallbackEl) {
              fallbackEl.classList.remove("cross-category-drag");
            }
          }

          return true; // Allow the move
        },

        onEnd: async (evt) => {
          // FIRST: Check if dropped on a collapsed category header BEFORE cleanup
          const collapsedTarget = document.querySelector(".template-category-section.collapsed.drag-target-different-category");

          // Clean up drag states
          document.body.classList.remove("template-dragging");
          document.querySelectorAll(".template-category-section").forEach(s => {
            s.classList.remove("drag-target-different-category");
          });
          document.querySelectorAll(".category-header.collapsed-drop-zone").forEach(h => {
            h.classList.remove("collapsed-drop-zone");
            // Remove drop indicator elements
            const indicator = h.querySelector(".collapsed-drop-indicator");
            if (indicator) indicator.remove();
          });

          // Remove mousemove listener
          if (collapsedDropHandler) {
            document.removeEventListener("mousemove", collapsedDropHandler);
            collapsedDropHandler = null;
          }
          let targetSection = evt.to.closest(".template-category-section");
          let targetCategoryId = targetSection ? targetSection.dataset.categoryId : null;
          let droppedOnCollapsed = false;

          if (collapsedTarget && collapsedTarget.dataset.categoryId !== sourceCategoryId) {
            // Override target to be the collapsed category
            targetSection = collapsedTarget;
            targetCategoryId = collapsedTarget.dataset.categoryId;
            droppedOnCollapsed = true;
          }

          const templateId = evt.item.dataset.templateId;

          // Check if moved to different category (either via drag to expanded grid or drop on collapsed header)
          if ((evt.from !== evt.to || droppedOnCollapsed) && sourceCategoryId !== targetCategoryId) {
            // Cross-category move - show confirmation
            const targetCategoryName = targetSection.querySelector(".category-header h2")?.textContent || "this category";

            const ConfirmDialog = window.ConfirmDialog;
            const confirmed = await ConfirmDialog.show({
              message: `Move "<strong>${draggedTemplateName}</strong>" from "<strong>${sourceCategoryName}</strong>" to "<strong>${targetCategoryName}</strong>"?`,
              title: "Move Template to Different Category",
              confirmText: "Move",
              confirmClass: "btn-primary"
            });

            if (!confirmed) {
              // Revert: move template back to source at original position
              // Remove the item from its current location first
              evt.item.remove();

              // Get fresh reference to children after removal
              const children = Array.from(sourceGrid.children);

              // Insert at original position
              if (originalIndex < children.length) {
                sourceGrid.insertBefore(evt.item, children[originalIndex]);
              } else {
                sourceGrid.appendChild(evt.item);
              }

              sourceCategoryId = null;
              sourceGrid = null;
              originalIndex = null;
              return;
            }

            // Perform the category move via API
            try {
              const result = await Ajax.post("move_template_category", {
                template_id: templateId,
                category_id: targetCategoryId
              });

              if (result.success) {
                Toast.success(result.message || "Template moved successfully");

                // For collapsed category drops, move item to target grid (at end)
                if (droppedOnCollapsed) {
                  const targetGrid = targetSection.querySelector(".item-card-grid");
                  if (targetGrid) {
                    targetGrid.appendChild(evt.item);
                  }
                }

                // Also reorder within new category
                const targetGrid = droppedOnCollapsed
                  ? targetSection.querySelector(".item-card-grid")
                  : evt.to;
                if (targetGrid) {
                  const templates = targetGrid.querySelectorAll(".item-card:not(.item-card-empty)");
                  const order = Array.from(templates).map(card => card.dataset.templateId);
                  await Ajax.post("reorder_templates", { order: order });
                }

                // Check if source category is now empty (no templates left)
                const sourceSection = document.querySelector(`.template-category-section[data-category-id="${sourceCategoryId}"]`);
                if (sourceSection) {
                  const remainingTemplates = sourceSection.querySelectorAll(".item-card:not(.item-card-empty)");
                  if (remainingTemplates.length === 0) {
                    // Reload to show empty state for category
                    window.location.reload();
                  }
                }
              } else {
                Toast.error(result.message || "Failed to move template");
                // Revert on error to original position
                const children = sourceGrid.children;
                if (originalIndex < children.length) {
                  sourceGrid.insertBefore(evt.item, children[originalIndex]);
                } else {
                  sourceGrid.appendChild(evt.item);
                }
              }
            } catch (error) {
              console.error("Move template error:", error);
              Toast.error("Failed to move template");
              // Revert on error to original position
              const children = sourceGrid.children;
              if (originalIndex < children.length) {
                sourceGrid.insertBefore(evt.item, children[originalIndex]);
              } else {
                sourceGrid.appendChild(evt.item);
              }
            }
          } else if (evt.oldIndex !== evt.newIndex || evt.from !== evt.to) {
            // Same category reorder
            const templates = evt.to.querySelectorAll(".item-card:not(.item-card-empty)");
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

          // Reset tracking
          sourceCategoryId = null;
          sourceCategoryName = null;
          sourceGrid = null;
          draggedTemplateName = null;
          originalIndex = null;
          draggedItem = null;
        }
      });

      TemplateManager.templateSortables.push(sortable);
    });
  }
}
