/**
 * Dashboard Keyboard Navigation Module
 * Handles arrow key navigation between dashboard areas and section navigation
 */

/**
 * KeyboardNavigation class for dashboard grid navigation
 */
export class KeyboardNavigation {
  constructor() {
    this.initialized = false;
    this.lastFocusedCell = null;
  }

  /**
   * Initialize keyboard navigation
   */
  init() {
    if (this.initialized) return;

    document.addEventListener("keydown", this.handleKeyDown.bind(this));
    this.initialized = true;
  }

  /**
   * Main keydown handler
   * @param {KeyboardEvent} e
   */
  handleKeyDown(e) {
    // Check if we're on a dashboard/template page
    const container = this.getContainer();
    if (!container) return;

    // Don't interfere with inputs, textareas, or contenteditable
    if (this.isInputFocused()) return;

    // Check if tweak mode is enabled (only for builder pages)
    const isTweakMode = this.isTweakMode();

    // Handle Alt+PageUp/PageDown for section navigation (only in non-tweak mode)
    if (!isTweakMode && e.altKey && (e.key === "PageUp" || e.key === "PageDown")) {
      this.handleSectionNavigation(e.key === "PageDown" ? "next" : "prev");
      e.preventDefault();
      return;
    }

    // Only handle arrow keys (without modifiers for navigation)
    if (!["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"].includes(e.key)) {
      return;
    }

    // Don't navigate if any modifier is pressed
    if (e.altKey || e.ctrlKey || e.metaKey) return;

    this.handleArrowNavigation(e);
  }

  /**
   * Get the dashboard container
   * @returns {HTMLElement|null}
   */
  getContainer() {
    return document.querySelector(".dashboard-builder, .dashboard-sections");
  }

  /**
   * Check if tweak mode is enabled
   * @returns {boolean}
   */
  isTweakMode() {
    const builder = document.querySelector(".dashboard-builder");
    return builder && !builder.classList.contains("layout-edit-disabled");
  }

  /**
   * Check if an input element is focused
   * @returns {boolean}
   */
  isInputFocused() {
    const active = document.activeElement;
    if (!active) return false;

    const tagName = active.tagName.toLowerCase();
    if (tagName === "input" || tagName === "textarea" || tagName === "select") {
      return true;
    }

    if (active.isContentEditable) return true;

    return false;
  }

  /**
   * Get all navigable dashboard areas
   * @returns {HTMLElement[]}
   */
  getNavigableAreas() {
    const container = this.getContainer();
    if (!container) return [];

    // Get all dashboard areas (top-level and nested sub-rows)
    const areas = Array.from(
      container.querySelectorAll(".dashboard-area, .dashboard-sub-row")
    );

    // Filter to only visible areas
    return areas.filter((area) => {
      const rect = area.getBoundingClientRect();
      return rect.width > 0 && rect.height > 0;
    });
  }

  /**
   * Get the focusable element for an area
   * Always returns the area itself - focus is on dashboard-area/dashboard-sub-row
   * @param {HTMLElement} area - The dashboard area or sub-row
   * @returns {HTMLElement}
   */
  getFocusableElement(area) {
    // Always focus the area itself
    // Add tabindex if not present
    if (!area.hasAttribute("tabindex")) {
      area.setAttribute("tabindex", "0");
    }
    return area;
  }

  /**
   * Get the currently focused area
   * @returns {HTMLElement|null}
   */
  getCurrentArea() {
    const active = document.activeElement;
    if (!active) return null;

    // Check if focused element is inside a dashboard area
    return active.closest(".dashboard-sub-row") || active.closest(".dashboard-area");
  }

  /**
   * Handle arrow key navigation
   * @param {KeyboardEvent} e
   */
  handleArrowNavigation(e) {
    const areas = this.getNavigableAreas();
    if (areas.length === 0) return;

    const currentArea = this.getCurrentArea();

    // If no area is focused, focus the first one
    if (!currentArea) {
      const firstFocusable = this.getFocusableElement(areas[0]);
      if (firstFocusable) {
        firstFocusable.focus();
        e.preventDefault();
      }
      return;
    }

    const currentIndex = areas.indexOf(currentArea);
    if (currentIndex === -1) return;

    const currentRect = currentArea.getBoundingClientRect();
    let targetArea = null;

    switch (e.key) {
      case "ArrowRight":
        targetArea = this.findAreaInDirection(areas, currentArea, currentRect, "right");
        break;

      case "ArrowLeft":
        targetArea = this.findAreaInDirection(areas, currentArea, currentRect, "left");
        break;

      case "ArrowDown":
        targetArea = this.findAreaInDirection(areas, currentArea, currentRect, "down");
        break;

      case "ArrowUp":
        targetArea = this.findAreaInDirection(areas, currentArea, currentRect, "up");
        break;
    }

    if (targetArea) {
      const focusable = this.getFocusableElement(targetArea);
      if (focusable) {
        focusable.focus();
        e.preventDefault();
      }
    }
  }

  /**
   * Find the nearest area in a given direction
   * For horizontal (left/right): prioritize same section, then same row
   * For vertical (up/down): allow cross-section movement
   * @param {HTMLElement[]} areas - Array of area elements
   * @param {HTMLElement} currentArea - Current focused area
   * @param {DOMRect} currentRect - Bounding rect of current area
   * @param {string} direction - "up", "down", "left", or "right"
   * @returns {HTMLElement|null}
   */
  findAreaInDirection(areas, currentArea, currentRect, direction) {
    const currentCenterX = currentRect.left + currentRect.width / 2;
    const currentCenterY = currentRect.top + currentRect.height / 2;
    const currentSection = currentArea.closest(".dashboard-section");
    const isHorizontal = direction === "left" || direction === "right";

    let bestArea = null;
    let bestScore = Infinity;

    // Threshold for considering elements in the same row/column
    const threshold = 10;
    // Row threshold for horizontal navigation - must be roughly same vertical position
    const rowThreshold = currentRect.height * 0.5;

    areas.forEach((area) => {
      if (area === currentArea) return;

      const rect = area.getBoundingClientRect();
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 2;
      const areaSection = area.closest(".dashboard-section");

      let isInDirection = false;
      let primaryDist = 0;
      let secondaryDist = 0;

      switch (direction) {
        case "right":
          isInDirection = centerX > currentCenterX + threshold;
          primaryDist = centerX - currentCenterX;
          secondaryDist = Math.abs(centerY - currentCenterY);
          break;

        case "left":
          isInDirection = centerX < currentCenterX - threshold;
          primaryDist = currentCenterX - centerX;
          secondaryDist = Math.abs(centerY - currentCenterY);
          break;

        case "down":
          isInDirection = centerY > currentCenterY + threshold;
          primaryDist = centerY - currentCenterY;
          secondaryDist = Math.abs(centerX - currentCenterX);
          break;

        case "up":
          isInDirection = centerY < currentCenterY - threshold;
          primaryDist = currentCenterY - centerY;
          secondaryDist = Math.abs(centerX - currentCenterX);
          break;
      }

      if (!isInDirection) return;

      // For horizontal navigation, only consider areas in the same row (similar Y position)
      if (isHorizontal && secondaryDist > rowThreshold) {
        return;
      }

      // Calculate score
      let score = primaryDist + secondaryDist * 0.3;

      // For horizontal navigation, heavily penalize areas in different sections
      if (isHorizontal && areaSection !== currentSection) {
        score += 10000; // Large penalty to ensure same-section areas are preferred
      }

      if (score < bestScore) {
        bestScore = score;
        bestArea = area;
      }
    });

    return bestArea;
  }

  /**
   * Handle Alt+PageUp/PageDown to click Add Section buttons
   * @param {string} direction - "next" (bottom) or "prev" (top)
   */
  handleSectionNavigation(direction) {
    // Must be focused on something inside a section
    const currentArea = this.getCurrentArea();
    if (!currentArea) return;

    // Find the parent section wrapper
    const sectionWrapper = currentArea.closest(".dashboard-section-wrapper");
    if (!sectionWrapper) return;

    // Find the appropriate add section button
    const buttonClass =
      direction === "next" ? ".add-section-bottom-btn" : ".add-section-top-btn";
    const addSectionBtn = sectionWrapper.querySelector(buttonClass);

    if (addSectionBtn) {
      // Store the current focused element for focus restoration
      this.lastFocusedCell = document.activeElement;

      // Scroll the button into view and click it
      addSectionBtn.scrollIntoView({ behavior: "smooth", block: "center" });
      setTimeout(() => {
        addSectionBtn.click();
      }, 100);
    }
  }

  /**
   * Store the last focused cell (called before opening modals)
   * @param {HTMLElement} element
   */
  setLastFocusedCell(element) {
    this.lastFocusedCell = element;
  }

  /**
   * Get the last focused cell
   * @returns {HTMLElement|null}
   */
  getLastFocusedCell() {
    return this.lastFocusedCell;
  }

  /**
   * Restore focus to the last focused cell
   */
  restoreFocus() {
    if (this.lastFocusedCell && document.contains(this.lastFocusedCell)) {
      this.lastFocusedCell.focus();
    }
    this.lastFocusedCell = null;
  }
}

// Create singleton instance
export const keyboardNavigation = new KeyboardNavigation();
