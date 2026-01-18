/**
 * Template Modal Keyboard Navigation Module
 * Handles arrow key navigation within template/section selection modals
 * - Up/Down: Move between categories
 * - Left/Right: Move between templates within a category
 *
 * Supports both:
 * - template-modal (dashboard builder - create dashboard/add section from template)
 * - add-section-modal (template builder - add empty section or section from template)
 */

/**
 * TemplateModalNavigation class for template modal grid navigation
 */
export class TemplateModalNavigation {
  constructor() {
    this.initialized = false;
    this.modals = [];
  }

  /**
   * Initialize template modal navigation for all supported modals
   */
  init() {
    if (this.initialized) return;

    // Support both template-modal and add-section-modal
    const modalIds = ["template-modal", "add-section-modal"];

    modalIds.forEach((id) => {
      const modal = document.getElementById(id);
      if (modal) {
        modal.addEventListener("keydown", this.handleKeyDown.bind(this));
        this.modals.push(modal);
      }
    });

    this.initialized = true;
  }

  /**
   * Handle keydown events for arrow navigation
   */
  handleKeyDown(e) {
    // Only handle arrow keys
    if (!["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"].includes(e.key)) {
      return;
    }

    // Check if focus is on an item-card
    const currentCard = document.activeElement?.closest(".item-card");
    if (!currentCard) return;

    // Find which modal contains the card
    const modal = currentCard.closest(".modal");
    if (!modal || !this.modals.includes(modal)) return;

    // Make sure we're in a template list or item card grid
    const cardContainer =
      currentCard.closest("#template-list") ||
      currentCard.closest(".item-card-grid");
    if (!cardContainer) return;

    e.preventDefault();
    this.navigateTemplateCards(currentCard, e.key, modal);
  }

  /**
   * Navigate between template cards using arrow keys
   * @param {HTMLElement} currentCard - Currently focused card
   * @param {string} key - Arrow key pressed
   * @param {HTMLElement} modal - The modal containing the cards
   */
  navigateTemplateCards(currentCard, key, modal) {
    // Get all categories in this modal (template-category or item-card-grid containers)
    const categories = Array.from(
      modal.querySelectorAll(".template-category, .modal-body > .item-card-grid")
    );

    // If no categories found, try to navigate within a flat grid
    if (categories.length === 0) {
      this.navigateFlatGrid(currentCard, key, modal);
      return;
    }

    const currentCategory = currentCard.closest(".template-category") ||
      currentCard.closest(".item-card-grid");
    if (!currentCategory) return;

    const currentCategoryIndex = categories.indexOf(currentCategory);
    const cardsInCategory = Array.from(
      currentCategory.querySelectorAll(".item-card")
    );
    const currentCardIndex = cardsInCategory.indexOf(currentCard);

    let targetCard = null;

    switch (key) {
      case "ArrowLeft":
        // Move to previous card in same category
        if (currentCardIndex > 0) {
          targetCard = cardsInCategory[currentCardIndex - 1];
        }
        break;

      case "ArrowRight":
        // Move to next card in same category
        if (currentCardIndex < cardsInCategory.length - 1) {
          targetCard = cardsInCategory[currentCardIndex + 1];
        }
        break;

      case "ArrowUp":
        // Move to same position in previous category
        if (currentCategoryIndex > 0) {
          const prevCategory = categories[currentCategoryIndex - 1];
          const prevCategoryCards = Array.from(
            prevCategory.querySelectorAll(".item-card")
          );
          // Try same index, or last card if previous category has fewer cards
          const targetIndex = Math.min(
            currentCardIndex,
            prevCategoryCards.length - 1
          );
          targetCard = prevCategoryCards[targetIndex];
        }
        break;

      case "ArrowDown":
        // Move to same position in next category
        if (currentCategoryIndex < categories.length - 1) {
          const nextCategory = categories[currentCategoryIndex + 1];
          const nextCategoryCards = Array.from(
            nextCategory.querySelectorAll(".item-card")
          );
          // Try same index, or last card if next category has fewer cards
          const targetIndex = Math.min(
            currentCardIndex,
            nextCategoryCards.length - 1
          );
          targetCard = nextCategoryCards[targetIndex];
        }
        break;
    }

    if (targetCard) {
      targetCard.focus();
      // Scroll into view if needed
      targetCard.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
  }

  /**
   * Navigate within a flat grid (no categories)
   * @param {HTMLElement} currentCard - Currently focused card
   * @param {string} key - Arrow key pressed
   * @param {HTMLElement} modal - The modal containing the cards
   */
  navigateFlatGrid(currentCard, key, modal) {
    const allCards = Array.from(modal.querySelectorAll(".item-card"));
    const currentIndex = allCards.indexOf(currentCard);
    if (currentIndex === -1) return;

    let targetCard = null;

    switch (key) {
      case "ArrowLeft":
        if (currentIndex > 0) {
          targetCard = allCards[currentIndex - 1];
        }
        break;

      case "ArrowRight":
        if (currentIndex < allCards.length - 1) {
          targetCard = allCards[currentIndex + 1];
        }
        break;

      case "ArrowUp":
      case "ArrowDown":
        // For flat grids, estimate columns based on container width
        // and navigate up/down by that many items
        const grid = currentCard.closest(".item-card-grid");
        if (grid) {
          const gridStyle = window.getComputedStyle(grid);
          const columns = gridStyle.gridTemplateColumns.split(" ").length || 3;
          const offset = key === "ArrowUp" ? -columns : columns;
          const targetIndex = currentIndex + offset;
          if (targetIndex >= 0 && targetIndex < allCards.length) {
            targetCard = allCards[targetIndex];
          }
        }
        break;
    }

    if (targetCard) {
      targetCard.focus();
      targetCard.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
  }
}

// Create singleton instance
const templateModalNavigation = new TemplateModalNavigation();

// Export singleton
export default templateModalNavigation;
