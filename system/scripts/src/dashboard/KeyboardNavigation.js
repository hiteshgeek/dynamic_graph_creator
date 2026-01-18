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
    this.navigationEnabled = false;
    this.indicatorElement = null;
  }

  /**
   * Initialize keyboard navigation
   */
  init() {
    if (this.initialized) return;

    document.addEventListener("keydown", this.handleKeyDown.bind(this));
    this.initialized = true;

    // Restore navigation state from localStorage
    this.restoreNavigationState();
  }

  /**
   * Restore navigation state from localStorage
   */
  restoreNavigationState() {
    try {
      const saved = localStorage.getItem("dgc-keyboard-nav-enabled");
      if (saved === "true") {
        this.navigationEnabled = true;
        this.showIndicator();
      }
    } catch (e) {
      // localStorage not available, ignore
    }
  }

  /**
   * Save navigation state to localStorage
   */
  saveNavigationState() {
    try {
      localStorage.setItem("dgc-keyboard-nav-enabled", this.navigationEnabled.toString());
    } catch (e) {
      // localStorage not available, ignore
    }
  }

  /**
   * Main keydown handler
   * @param {KeyboardEvent} e
   */
  handleKeyDown(e) {
    // Check if we're on a dashboard/template page
    const container = this.getContainer();
    if (!container) return;

    // Handle Alt+N to toggle navigation mode (works even when input is focused)
    if (e.altKey && (e.key === "n" || e.key === "N")) {
      this.toggleNavigation();
      e.preventDefault();
      return;
    }

    // Don't interfere with inputs, textareas, or contenteditable
    if (this.isInputFocused()) return;

    // Only process navigation when enabled
    if (!this.navigationEnabled) return;

    // Check if tweak mode is enabled (only for builder pages)
    const isTweakMode = this.isTweakMode();

    // Handle Alt+PageUp/PageDown for section navigation (only in non-tweak mode)
    if (!isTweakMode && e.altKey && (e.key === "PageUp" || e.key === "PageDown")) {
      this.handleSectionNavigation(e.key === "PageDown" ? "next" : "prev");
      e.preventDefault();
      return;
    }

    // Tweak mode keyboard shortcuts
    if (isTweakMode && e.altKey && !e.shiftKey) {
      // Alt+Home/End - Add row above/below
      if (e.key === "Home" || e.key === "End") {
        this.handleTweakAddRow(e.key === "Home" ? "above" : "below");
        e.preventDefault();
        return;
      }

      // Alt+Insert/Delete - Add column left/right
      if (e.key === "Insert" || e.key === "Delete") {
        this.handleTweakAddColumn(e.key === "Insert" ? "left" : "right");
        e.preventDefault();
        return;
      }

      // Alt+Backspace - Delete selected column or row
      if (e.key === "Backspace" && !e.ctrlKey) {
        this.handleTweakDelete();
        e.preventDefault();
        return;
      }

      // Ctrl+Alt+Backspace - Delete section
      if (e.key === "Backspace" && e.ctrlKey) {
        this.handleDeleteSection();
        e.preventDefault();
        return;
      }

      // Alt+Arrows (without Shift) - Expand/shrink row or column
      if (["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"].includes(e.key)) {
        this.handleTweakResize(e.key.replace("Arrow", "").toLowerCase());
        e.preventDefault();
        return;
      }
    }

    // Dragging/moving shortcuts (require navigation mode and tweak mode)
    if (isTweakMode && ["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"].includes(e.key)) {
      const direction = e.key.replace("Arrow", "").toLowerCase();

      // Shift+Arrow - Move sections up/down
      if (e.shiftKey && !e.ctrlKey && !e.altKey) {
        if (direction === "up" || direction === "down") {
          this.handleMoveSection(direction);
          e.preventDefault();
          return;
        }
      }

      // Shift+Ctrl+Arrow - Move columns left/right
      if (e.shiftKey && e.ctrlKey && !e.altKey) {
        if (direction === "left" || direction === "right") {
          this.handleMoveColumn(direction);
          e.preventDefault();
          return;
        }
      }

      // Shift+Alt+Arrow - Move rows up/down
      if (e.shiftKey && e.altKey && !e.ctrlKey) {
        if (direction === "up" || direction === "down") {
          this.handleMoveRow(direction);
          e.preventDefault();
          return;
        }
      }
    }

    // Only handle arrow keys (without modifiers for navigation)
    if (!["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"].includes(e.key)) {
      return;
    }

    // Don't navigate if any modifier is pressed
    if (e.altKey || e.ctrlKey || e.metaKey || e.shiftKey) return;

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

    // Get non-nested dashboard areas and sub-rows (for nested layouts)
    // Exclude .dashboard-area-nested as those are containers, not focusable cells
    const areas = Array.from(
      container.querySelectorAll(
        ".dashboard-area:not(.dashboard-area-nested), .dashboard-sub-row"
      )
    );

    // Filter to only visible areas
    return areas.filter((area) => {
      const rect = area.getBoundingClientRect();
      return rect.width > 0 && rect.height > 0;
    });
  }

  /**
   * Get all dashboard areas including nested containers (for column-level navigation)
   * @returns {HTMLElement[]}
   */
  getAllDashboardAreas() {
    const container = this.getContainer();
    if (!container) return [];

    // Get all dashboard areas (including nested containers)
    const areas = Array.from(container.querySelectorAll(".dashboard-area"));

    // Filter to only visible areas
    return areas.filter((area) => {
      const rect = area.getBoundingClientRect();
      return rect.width > 0 && rect.height > 0;
    });
  }

  /**
   * Get the focusable element for an area
   * For nested areas, returns the first sub-row; otherwise returns the area itself
   * @param {HTMLElement} area - The dashboard area or sub-row
   * @returns {HTMLElement}
   */
  getFocusableElement(area) {
    // If this is a nested area container, focus the first sub-row instead
    if (area.classList.contains("dashboard-area-nested")) {
      const firstSubRow = area.querySelector(".dashboard-sub-row");
      if (firstSubRow) {
        if (!firstSubRow.hasAttribute("tabindex")) {
          firstSubRow.setAttribute("tabindex", "0");
        }
        return firstSubRow;
      }
    }

    // Add tabindex if not present
    if (!area.hasAttribute("tabindex")) {
      area.setAttribute("tabindex", "0");
    }
    return area;
  }

  /**
   * Check if an area is a nested container with sub-rows
   * @param {HTMLElement} area - The dashboard area
   * @returns {boolean}
   */
  isNestedArea(area) {
    return area.classList.contains("dashboard-area-nested");
  }

  /**
   * Get the first sub-row from a nested area, or the area itself if not nested
   * @param {HTMLElement} area - The dashboard area
   * @param {string} direction - Navigation direction to determine which sub-row to focus
   * @returns {HTMLElement}
   */
  getTargetFocusElement(area, direction) {
    // If navigating to a nested area, focus the appropriate sub-row
    if (this.isNestedArea(area)) {
      const subRows = Array.from(area.querySelectorAll(".dashboard-sub-row"));
      if (subRows.length > 0) {
        // For vertical navigation (up), focus the last sub-row
        // For all other directions (left, right, down), focus the first sub-row
        const targetSubRow =
          direction === "up" ? subRows[subRows.length - 1] : subRows[0];
        if (!targetSubRow.hasAttribute("tabindex")) {
          targetSubRow.setAttribute("tabindex", "0");
        }
        return targetSubRow;
      }
    }

    // Otherwise focus the area itself
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

    const currentRect = currentArea.getBoundingClientRect();
    const direction = e.key.replace("Arrow", "").toLowerCase();
    const isHorizontal = direction === "left" || direction === "right";
    const isSubRow = currentArea.classList.contains("dashboard-sub-row");

    let targetArea = null;
    let focusElement = null;

    // For horizontal navigation from a sub-row, try column-level navigation first
    if (isHorizontal && isSubRow) {
      const columnTarget = this.findAdjacentColumn(currentArea, direction);
      if (columnTarget) {
        focusElement = this.getTargetFocusElement(columnTarget, direction);
      }
    }

    // For vertical navigation from a regular area (not sub-row), try cross-section navigation
    if (!isHorizontal && !isSubRow) {
      const sectionTarget = this.findAreaInAdjacentSection(currentArea, direction);
      if (sectionTarget) {
        focusElement = this.getTargetFocusElement(sectionTarget, direction);
      }
    }

    // If no column/section found, use regular area navigation
    if (!focusElement) {
      targetArea = this.findAreaInDirection(areas, currentArea, currentRect, direction);
      if (targetArea) {
        focusElement = this.getFocusableElement(targetArea);
      }
    }

    if (focusElement) {
      focusElement.focus();
      e.preventDefault();
    }
  }

  /**
   * Find the adjacent column (dashboard-area) when navigating horizontally from a sub-row
   * @param {HTMLElement} currentSubRow - The current sub-row element
   * @param {string} direction - "left" or "right"
   * @returns {HTMLElement|null} - The adjacent dashboard-area or null
   */
  findAdjacentColumn(currentSubRow, direction) {
    const currentNestedParent = currentSubRow.closest(".dashboard-area-nested");
    if (!currentNestedParent) return null;

    const currentSection = currentSubRow.closest(".dashboard-section");
    if (!currentSection) return null;

    // Get all dashboard areas in the same section (direct children of the section grid)
    const allAreas = this.getAllDashboardAreas();
    const sectionAreas = allAreas.filter(
      (area) => area.closest(".dashboard-section") === currentSection
    );

    const currentRect = currentNestedParent.getBoundingClientRect();
    const currentCenterX = currentRect.left + currentRect.width / 2;

    let bestArea = null;
    let bestDist = Infinity;

    sectionAreas.forEach((area) => {
      // Skip the current nested parent
      if (area === currentNestedParent) return;
      // Skip areas that are children of another nested area
      if (area.closest(".dashboard-area-nested") && area.closest(".dashboard-area-nested") !== area) return;

      const rect = area.getBoundingClientRect();
      const centerX = rect.left + rect.width / 2;

      const isInDirection =
        direction === "right" ? centerX > currentCenterX + 10 : centerX < currentCenterX - 10;

      if (!isInDirection) return;

      const dist = Math.abs(centerX - currentCenterX);
      if (dist < bestDist) {
        bestDist = dist;
        bestArea = area;
      }
    });

    return bestArea;
  }

  /**
   * Find the best area in an adjacent section when navigating vertically
   * Uses column index to find the corresponding area in the target section
   * @param {HTMLElement} currentArea - The current dashboard area
   * @param {string} direction - "up" or "down"
   * @returns {HTMLElement|null} - The best area in the adjacent section
   */
  findAreaInAdjacentSection(currentArea, direction) {
    const currentSection = currentArea.closest(".dashboard-section");
    if (!currentSection) return null;

    // Get current column index within the section
    const currentColumnIndex = this.getColumnIndex(currentArea, currentSection);

    // Find the adjacent section
    const adjacentSection = this.getAdjacentSection(currentSection, direction);
    if (!adjacentSection) return null;

    // Get areas in the adjacent section and find the one at the same column index
    const adjacentAreas = this.getSectionAreas(adjacentSection);
    if (adjacentAreas.length === 0) return null;

    // Try to get area at same column index, or the last area if index is out of bounds
    const targetIndex = Math.min(currentColumnIndex, adjacentAreas.length - 1);
    return adjacentAreas[targetIndex] || adjacentAreas[0];
  }

  /**
   * Get the column index of an area within its section
   * @param {HTMLElement} area - The dashboard area
   * @param {HTMLElement} section - The parent section
   * @returns {number} - The column index (0-based)
   */
  getColumnIndex(area, section) {
    const sectionAreas = this.getSectionAreas(section);
    return sectionAreas.indexOf(area);
  }

  /**
   * Get all top-level dashboard areas in a section (sorted by horizontal position)
   * @param {HTMLElement} section - The dashboard section
   * @returns {HTMLElement[]} - Array of areas sorted left to right
   */
  getSectionAreas(section) {
    const allAreas = Array.from(section.querySelectorAll(".dashboard-area"));

    // Filter to only top-level areas (not nested inside another dashboard-area)
    const topLevelAreas = allAreas.filter((area) => {
      const nestedParent = area.closest(".dashboard-area-nested");
      // Include if not nested, or if it IS the nested container itself
      return !nestedParent || nestedParent === area;
    });

    // Sort by horizontal position (left to right)
    return topLevelAreas.sort((a, b) => {
      const rectA = a.getBoundingClientRect();
      const rectB = b.getBoundingClientRect();
      return rectA.left - rectB.left;
    });
  }

  /**
   * Get the adjacent section in a given direction
   * @param {HTMLElement} currentSection - The current section
   * @param {string} direction - "up" or "down"
   * @returns {HTMLElement|null} - The adjacent section or null
   */
  getAdjacentSection(currentSection, direction) {
    const container = this.getContainer();
    if (!container) return null;

    const allSections = Array.from(container.querySelectorAll(".dashboard-section"));
    const currentIndex = allSections.indexOf(currentSection);

    if (currentIndex === -1) return null;

    if (direction === "down") {
      return allSections[currentIndex + 1] || null;
    } else {
      return allSections[currentIndex - 1] || null;
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
    const currentNestedParent = currentArea.closest(".dashboard-area-nested");
    const isHorizontal = direction === "left" || direction === "right";
    const isSubRow = currentArea.classList.contains("dashboard-sub-row");

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
      const areaNestedParent = area.closest(".dashboard-area-nested");

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

      // For horizontal navigation on sub-rows within same nested parent,
      // allow navigation to sibling sub-rows (vertically stacked)
      const isSiblingSubRow =
        isSubRow &&
        currentNestedParent &&
        areaNestedParent === currentNestedParent &&
        area.classList.contains("dashboard-sub-row");

      // Check if navigating from sub-row to adjacent dashboard-area (not nested)
      const isAdjacentArea =
        isSubRow &&
        !area.classList.contains("dashboard-sub-row") &&
        areaSection === currentSection;

      // For horizontal navigation, only consider areas in the same row (similar Y position)
      // Exceptions:
      // 1. Sibling sub-rows within the same nested parent
      // 2. Adjacent dashboard-areas in the same section (for cross-column navigation)
      if (isHorizontal && secondaryDist > rowThreshold && !isSiblingSubRow && !isAdjacentArea) {
        return;
      }

      // Calculate score
      let score = primaryDist + secondaryDist * 0.3;

      // For horizontal navigation, heavily penalize areas in different sections
      if (isHorizontal && areaSection !== currentSection) {
        score += 10000; // Large penalty to ensure same-section areas are preferred
      }

      // Prioritize sibling sub-rows for horizontal navigation
      if (isSiblingSubRow) {
        score -= 100; // Bonus for sibling sub-rows
      }

      // Give slight preference to adjacent areas in the same section
      if (isAdjacentArea) {
        score -= 50; // Bonus for adjacent areas
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

  /**
   * Get the focused area's context (sectionId, areaIndex, rowIndex)
   * @returns {Object|null} - Context object or null if no focused area
   */
  getFocusedAreaContext() {
    const currentArea = this.getCurrentArea();
    if (!currentArea) return null;

    const isSubRow = currentArea.classList.contains("dashboard-sub-row");

    // For sub-rows, get data from parent nested area
    if (isSubRow) {
      const nestedArea = currentArea.closest(".dashboard-area-nested");
      const section = currentArea.closest(".dashboard-section");
      if (!nestedArea || !section) return null;

      return {
        sectionId: section.dataset.sectionId,
        areaIndex: parseInt(nestedArea.dataset.areaIndex),
        rowIndex: parseInt(currentArea.dataset.rowIndex),
        isSubRow: true,
        element: currentArea,
      };
    }

    // For regular areas
    const section = currentArea.closest(".dashboard-section");
    if (!section) return null;

    return {
      sectionId: section.dataset.sectionId,
      areaIndex: parseInt(currentArea.dataset.areaIndex),
      rowIndex: null,
      isSubRow: false,
      element: currentArea,
    };
  }

  /**
   * Get the dashboard builder instance
   * @returns {Object|null}
   */
  getDashboardBuilder() {
    return window.dashboardBuilderInstance || null;
  }

  /**
   * Handle tweak mode add row (Alt+Home/End)
   * @param {string} position - "above" or "below"
   */
  async handleTweakAddRow(position) {
    const context = this.getFocusedAreaContext();
    if (!context) return;

    const builder = this.getDashboardBuilder();
    if (!builder) return;

    // Get current row count before operation
    const getRowCount = () => {
      const section = document.querySelector(
        `.dashboard-section[data-section-id="${context.sectionId}"]`
      );
      if (!section) return 0;
      const area = section.querySelector(
        `.dashboard-area[data-area-index="${context.areaIndex}"]`
      );
      if (!area) return 0;
      const subRows = area.querySelectorAll(".dashboard-sub-row");
      return subRows.length;
    };

    const rowCountBefore = getRowCount();
    const wasNestedBefore = rowCountBefore > 0;

    let newRowIndex;
    if (context.isSubRow) {
      // For sub-rows: add row above or below the current row
      const insertPosition =
        position === "above" ? context.rowIndex : context.rowIndex + 1;
      await builder.addRowAt(context.sectionId, context.areaIndex, insertPosition);

      // Check if row was actually added
      const rowCountAfter = getRowCount();
      const rowWasAdded = rowCountAfter > rowCountBefore;

      // Only adjust index if row was actually added above current position
      newRowIndex =
        rowWasAdded && position === "above"
          ? context.rowIndex + 1
          : context.rowIndex;
    } else {
      // For regular areas: add row above (0) or below (-1 for end)
      const insertPosition = position === "above" ? 0 : -1;
      await builder.addRowAt(context.sectionId, context.areaIndex, insertPosition);

      // Check if area became nested (row was added)
      const rowCountAfter = getRowCount();
      const becameNested = !wasNestedBefore && rowCountAfter > 0;

      // After adding a row, area becomes nested - focus on the original content's position
      newRowIndex = becameNested ? (position === "above" ? 1 : 0) : null;
    }

    // Restore focus to the area (now nested with sub-rows if row was added)
    this.restoreFocusToArea(context.sectionId, context.areaIndex, newRowIndex);
  }

  /**
   * Handle tweak mode add column (Alt+Insert/Delete)
   * @param {string} position - "left" or "right"
   */
  async handleTweakAddColumn(position) {
    const context = this.getFocusedAreaContext();
    if (!context) return;

    const builder = this.getDashboardBuilder();
    if (!builder) return;

    // Get current column count before operation
    const section = document.querySelector(
      `.dashboard-section[data-section-id="${context.sectionId}"]`
    );
    const columnCountBefore = section
      ? section.querySelectorAll(":scope > .dashboard-area").length
      : 0;

    // Add column at the specified position
    const insertPosition =
      position === "left" ? context.areaIndex : context.areaIndex + 1;
    await builder.addColumnAt(context.sectionId, insertPosition);

    // Check if column was actually added by comparing column counts
    const sectionAfter = document.querySelector(
      `.dashboard-section[data-section-id="${context.sectionId}"]`
    );
    const columnCountAfter = sectionAfter
      ? sectionAfter.querySelectorAll(":scope > .dashboard-area").length
      : 0;

    // Only adjust index if column was actually added
    const columnWasAdded = columnCountAfter > columnCountBefore;
    const newAreaIndex =
      columnWasAdded && position === "left"
        ? context.areaIndex + 1
        : context.areaIndex;

    this.restoreFocusToArea(
      context.sectionId,
      newAreaIndex,
      context.isSubRow ? context.rowIndex : null
    );
  }

  /**
   * Handle tweak mode resize (Alt+Arrows)
   * Alt+Up/Down: Expand/shrink row height
   * Alt+Left/Right: Shrink/expand column width
   * @param {string} direction - "up", "down", "left", or "right"
   */
  async handleTweakResize(direction) {
    const context = this.getFocusedAreaContext();
    if (!context) return;

    const builder = this.getDashboardBuilder();
    if (!builder) return;

    if (direction === "up" || direction === "down") {
      // Vertical: resize row height
      if (context.isSubRow) {
        // For sub-rows: expand (up) or shrink (down) the row
        const resizeDirection = direction === "up" ? "increase" : "decrease";
        await builder.resizeRow(
          context.sectionId,
          context.areaIndex,
          context.rowIndex,
          resizeDirection
        );
        // Restore focus to the same sub-row after re-render
        this.restoreFocusToArea(context.sectionId, context.areaIndex, context.rowIndex);
      }
      // For regular areas without sub-rows, row resize doesn't apply
    } else {
      // Horizontal: resize column width
      // Left = shrink, Right = expand
      const resizeDirection = direction === "left" ? "decrease" : "increase";
      await builder.resizeColumn(context.sectionId, context.areaIndex, resizeDirection);
      // Restore focus to the same area after re-render
      this.restoreFocusToArea(context.sectionId, context.areaIndex, context.isSubRow ? context.rowIndex : null);
    }
  }

  /**
   * Handle tweak mode delete (Alt+Backspace)
   * For sub-rows: delete the row
   * For regular areas: delete the column
   */
  async handleTweakDelete() {
    const context = this.getFocusedAreaContext();
    if (!context) return;

    const builder = this.getDashboardBuilder();
    if (!builder) return;

    if (context.isSubRow) {
      // Delete the row
      const getRowCount = () => {
        const section = document.querySelector(
          `.dashboard-section[data-section-id="${context.sectionId}"]`
        );
        if (!section) return 0;
        const area = section.querySelector(
          `.dashboard-area[data-area-index="${context.areaIndex}"]`
        );
        if (!area) return 0;
        return area.querySelectorAll(".dashboard-sub-row").length;
      };

      const rowCountBefore = getRowCount();
      await builder.removeRow(context.sectionId, context.areaIndex, context.rowIndex);
      const rowCountAfter = getRowCount();

      // If row was deleted, focus on adjacent row or area
      if (rowCountAfter < rowCountBefore) {
        if (rowCountAfter === 0) {
          // Area no longer has sub-rows, focus on the area itself
          this.restoreFocusToArea(context.sectionId, context.areaIndex, null);
        } else {
          // Focus on previous row, or first row if we deleted the first one
          const newRowIndex = Math.min(context.rowIndex, rowCountAfter - 1);
          this.restoreFocusToArea(context.sectionId, context.areaIndex, newRowIndex);
        }
      } else {
        // Row wasn't deleted (min reached), restore focus to same row
        this.restoreFocusToArea(context.sectionId, context.areaIndex, context.rowIndex);
      }
    } else {
      // Delete the column
      const getColumnCount = () => {
        const section = document.querySelector(
          `.dashboard-section[data-section-id="${context.sectionId}"]`
        );
        return section
          ? section.querySelectorAll(":scope > .dashboard-area").length
          : 0;
      };

      const columnCountBefore = getColumnCount();
      await builder.removeColumn(context.sectionId, context.areaIndex);
      const columnCountAfter = getColumnCount();

      // If column was deleted, focus on adjacent column
      if (columnCountAfter < columnCountBefore) {
        // Focus on previous column, or first column if we deleted the first one
        const newAreaIndex = Math.min(context.areaIndex, columnCountAfter - 1);
        this.restoreFocusToArea(context.sectionId, newAreaIndex, null);
      } else {
        // Column wasn't deleted (min reached), restore focus to same column
        this.restoreFocusToArea(context.sectionId, context.areaIndex, null);
      }
    }
  }

  /**
   * Handle delete section (Ctrl+Alt+Backspace)
   * Deletes the entire section containing the focused area
   */
  async handleDeleteSection() {
    const context = this.getFocusedAreaContext();
    if (!context) return;

    const builder = this.getDashboardBuilder();
    if (!builder) return;

    // Get section count and index before deletion
    const container = this.getContainer();
    if (!container) return;

    const allSections = Array.from(container.querySelectorAll(".dashboard-section"));
    const currentSection = document.querySelector(
      `.dashboard-section[data-section-id="${context.sectionId}"]`
    );
    if (!currentSection) return;

    const currentIndex = allSections.indexOf(currentSection);
    const sectionCountBefore = allSections.length;

    // Call removeSection (which shows confirmation dialog)
    await builder.removeSection(context.sectionId);

    // Check if section was actually deleted
    const sectionsAfter = container.querySelectorAll(".dashboard-section");
    const sectionCountAfter = sectionsAfter.length;

    if (sectionCountAfter < sectionCountBefore) {
      // Section was deleted
      if (sectionCountAfter > 0) {
        // Focus on adjacent section (previous if exists, otherwise next)
        const newIndex = Math.min(currentIndex, sectionCountAfter - 1);
        const newSection = sectionsAfter[newIndex];
        if (newSection) {
          const newSectionId = newSection.dataset.sectionId;
          // Focus on first area of the new section
          this.restoreFocusToArea(newSectionId, 0, null);
        }
      }
      // If no sections left, nothing to focus on
    } else {
      // Cancelled - restore focus to the original area
      this.restoreFocusToArea(
        context.sectionId,
        context.areaIndex,
        context.isSubRow ? context.rowIndex : null
      );
    }
  }

  /**
   * Handle moving section up/down (Shift+Arrow)
   * @param {string} direction - "up" or "down"
   */
  async handleMoveSection(direction) {
    const context = this.getFocusedAreaContext();
    if (!context) return;

    const builder = this.getDashboardBuilder();
    if (!builder) return;

    // Get current section index
    const container = this.getContainer();
    if (!container) return;

    const allSections = Array.from(container.querySelectorAll(".dashboard-section"));
    const currentSection = document.querySelector(
      `.dashboard-section[data-section-id="${context.sectionId}"]`
    );
    if (!currentSection) return;

    const currentIndex = allSections.indexOf(currentSection);
    if (currentIndex === -1) return;

    // Check if move is possible
    if (direction === "up" && currentIndex === 0) return;
    if (direction === "down" && currentIndex === allSections.length - 1) return;

    // Move section
    await builder.moveSection(context.sectionId, direction);

    // Restore focus to the same area in the moved section
    this.restoreFocusToArea(
      context.sectionId,
      context.areaIndex,
      context.isSubRow ? context.rowIndex : null
    );
  }

  /**
   * Handle moving column left/right (Shift+Ctrl+Arrow)
   * @param {string} direction - "left" or "right"
   */
  async handleMoveColumn(direction) {
    const context = this.getFocusedAreaContext();
    if (!context) return;

    const builder = this.getDashboardBuilder();
    if (!builder) return;

    // Get current column count
    const section = document.querySelector(
      `.dashboard-section[data-section-id="${context.sectionId}"]`
    );
    if (!section) return;

    const columnCount = section.querySelectorAll(":scope > .dashboard-area").length;

    // Check if move is possible
    if (direction === "left" && context.areaIndex === 0) return;
    if (direction === "right" && context.areaIndex === columnCount - 1) return;

    // Move column
    await builder.moveColumn(context.sectionId, context.areaIndex, direction);

    // Calculate new area index after move
    const newAreaIndex = direction === "left" ? context.areaIndex - 1 : context.areaIndex + 1;

    // Restore focus to the moved column
    this.restoreFocusToArea(
      context.sectionId,
      newAreaIndex,
      context.isSubRow ? context.rowIndex : null
    );
  }

  /**
   * Handle moving row up/down (Shift+Alt+Arrow)
   * @param {string} direction - "up" or "down"
   */
  async handleMoveRow(direction) {
    const context = this.getFocusedAreaContext();
    if (!context) return;

    // Only works for sub-rows
    if (!context.isSubRow) return;

    const builder = this.getDashboardBuilder();
    if (!builder) return;

    // Get current row count
    const section = document.querySelector(
      `.dashboard-section[data-section-id="${context.sectionId}"]`
    );
    if (!section) return;

    const area = section.querySelector(
      `.dashboard-area[data-area-index="${context.areaIndex}"]`
    );
    if (!area) return;

    const rowCount = area.querySelectorAll(".dashboard-sub-row").length;

    // Check if move is possible
    if (direction === "up" && context.rowIndex === 0) return;
    if (direction === "down" && context.rowIndex === rowCount - 1) return;

    // Move row
    await builder.moveRow(context.sectionId, context.areaIndex, context.rowIndex, direction);

    // Calculate new row index after move
    const newRowIndex = direction === "up" ? context.rowIndex - 1 : context.rowIndex + 1;

    // Restore focus to the moved row
    this.restoreFocusToArea(context.sectionId, context.areaIndex, newRowIndex);
  }

  /**
   * Restore focus to a specific area after DOM re-render
   * @param {string} sectionId - The section ID
   * @param {number} areaIndex - The area index
   * @param {number|null} rowIndex - The row index for sub-rows, or null for regular areas
   */
  restoreFocusToArea(sectionId, areaIndex, rowIndex = null) {
    // Use requestAnimationFrame to ensure DOM has updated
    requestAnimationFrame(() => {
      const section = document.querySelector(
        `.dashboard-section[data-section-id="${sectionId}"]`
      );
      if (!section) return;

      if (rowIndex !== null) {
        // Focus on specific sub-row
        const subRow = section.querySelector(
          `.dashboard-area[data-area-index="${areaIndex}"] .dashboard-sub-row[data-row-index="${rowIndex}"]`
        );
        if (subRow) {
          if (!subRow.hasAttribute("tabindex")) {
            subRow.setAttribute("tabindex", "0");
          }
          subRow.focus();
        }
      } else {
        // Focus on regular area
        const area = section.querySelector(
          `.dashboard-area[data-area-index="${areaIndex}"]`
        );
        if (area) {
          // If it's a nested area, focus on first sub-row
          if (area.classList.contains("dashboard-area-nested")) {
            const firstSubRow = area.querySelector(".dashboard-sub-row");
            if (firstSubRow) {
              if (!firstSubRow.hasAttribute("tabindex")) {
                firstSubRow.setAttribute("tabindex", "0");
              }
              firstSubRow.focus();
              return;
            }
          }
          if (!area.hasAttribute("tabindex")) {
            area.setAttribute("tabindex", "0");
          }
          area.focus();
        }
      }
    });
  }

  /**
   * Toggle keyboard navigation on/off
   */
  toggleNavigation() {
    this.navigationEnabled = !this.navigationEnabled;

    if (this.navigationEnabled) {
      this.showIndicator();
    } else {
      this.hideIndicator();
    }

    // Persist state to localStorage
    this.saveNavigationState();
  }

  /**
   * Create and show the navigation indicator
   */
  showIndicator() {
    if (this.indicatorElement) return;

    this.indicatorElement = document.createElement("div");
    this.indicatorElement.className = "keyboard-nav-indicator";
    this.indicatorElement.innerHTML = '<i class="fa-solid fa-arrows-up-down-left-right"></i>';
    this.indicatorElement.title = "Keyboard Navigation Active (Alt+N to disable)";
    document.body.appendChild(this.indicatorElement);
  }

  /**
   * Hide and remove the navigation indicator
   */
  hideIndicator() {
    if (this.indicatorElement) {
      this.indicatorElement.remove();
      this.indicatorElement = null;
    }
  }

  /**
   * Check if navigation is currently enabled
   * @returns {boolean}
   */
  isNavigationEnabled() {
    return this.navigationEnabled;
  }
}

// Create singleton instance
export const keyboardNavigation = new KeyboardNavigation();
