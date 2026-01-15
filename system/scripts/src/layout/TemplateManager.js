/**
 * Template Manager
 * Handles template CRUD operations
 */

export class TemplateManager {
  static initialized = false;

  /**
   * Initialize template list page handlers using event delegation
   */
  static initTemplateList() {
    // Prevent multiple initializations
    if (TemplateManager.initialized) {
      return;
    }
    TemplateManager.initialized = true;

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
    });
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
}
