/**
 * WidgetSelectorModal - Widget (Graph) selector for dashboard areas
 * Allows selecting graphs to place in dashboard areas with category filtering and search
 */
const Ajax = window.Ajax;

export class WidgetSelectorModal {
  constructor(options = {}) {
    this.modalElement = null;
    this.modalInstance = null;
    this.graphs = [];
    this.categories = [];
    this.selectedCategories = new Set();
    this.searchQuery = "";
    this.currentAreaContext = null;
    this.currentWidgetId = null;
    this.usedWidgetIds = new Set(); // Widget IDs already used in the dashboard
    this.isLoading = false;
    this.dataLoaded = false;
    this.loadingPromise = null;

    // Callbacks
    this.onSelect = options.onSelect || null;
    this.onDeselect = options.onDeselect || null;

    // DOM elements (cached after init)
    this.categoryListEl = null;
    this.widgetGridEl = null;
    this.searchInputEl = null;
    this.countSubtitleEl = null;
  }

  /**
   * Initialize the modal
   */
  init() {
    this.modalElement = document.getElementById("widget-selector-modal");
    if (!this.modalElement) {
      console.warn("Widget selector modal not found");
      return;
    }

    // Cache DOM elements
    this.categoryListEl = this.modalElement.querySelector("#widget-category-list");
    this.widgetGridEl = this.modalElement.querySelector("#widget-grid");
    this.searchInputEl = this.modalElement.querySelector("#widget-search-input");
    this.countSubtitleEl = this.modalElement.querySelector("#widget-count-subtitle");

    // Initialize Bootstrap modal
    this.modalInstance = new bootstrap.Modal(this.modalElement);

    // Bind events
    this.bindEvents();
  }

  /**
   * Bind event listeners
   */
  bindEvents() {
    // Search input
    if (this.searchInputEl) {
      this.searchInputEl.addEventListener("input", (e) => {
        this.searchQuery = e.target.value.trim().toLowerCase();
        this.renderGraphGrid();
      });
    }

    // Select All / Clear All
    const selectAllBtn = this.modalElement.querySelector("#select-all-categories");
    const clearAllBtn = this.modalElement.querySelector("#clear-all-categories");

    if (selectAllBtn) {
      selectAllBtn.addEventListener("click", () => this.handleSelectAll());
    }
    if (clearAllBtn) {
      clearAllBtn.addEventListener("click", () => this.handleClearAll());
    }

    // Category chip clicks (event delegation)
    if (this.categoryListEl) {
      this.categoryListEl.addEventListener("click", (e) => {
        const chip = e.target.closest(".category-chip");
        if (chip) {
          const categoryId = parseInt(chip.dataset.categoryId, 10);
          const isCurrentlySelected = this.selectedCategories.has(categoryId);
          this.handleCategoryToggle(categoryId, !isCurrentlySelected);

          // Update chip visual state and checkbox icon
          const checkIcon = chip.querySelector(".chip-check-icon");
          if (isCurrentlySelected) {
            chip.classList.remove("active");
            chip.style.backgroundColor = "";
            chip.style.borderColor = "";
            chip.style.color = "";
            if (checkIcon) {
              checkIcon.classList.remove("fa-check-circle");
              checkIcon.classList.add("fa-circle");
            }
          } else {
            const color = chip.dataset.color || "#6c757d";
            chip.classList.add("active");
            chip.style.backgroundColor = color;
            chip.style.borderColor = color;
            chip.style.color = "#fff";
            if (checkIcon) {
              checkIcon.classList.remove("fa-circle");
              checkIcon.classList.add("fa-check-circle");
            }
          }
        }
      });
    }

    // Widget card clicks (event delegation)
    if (this.widgetGridEl) {
      this.widgetGridEl.addEventListener("click", (e) => {
        const card = e.target.closest(".widget-card");
        if (card) {
          const graphId = parseInt(card.dataset.graphId, 10);
          this.handleGraphSelect(graphId);
        }
      });

      // Keyboard support for cards
      this.widgetGridEl.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          const card = e.target.closest(".widget-card");
          if (card) {
            e.preventDefault();
            const graphId = parseInt(card.dataset.graphId, 10);
            this.handleGraphSelect(graphId);
          }
        }
      });
    }

    // Modal hidden event - clear state
    this.modalElement.addEventListener("hidden.bs.modal", () => {
      this.searchQuery = "";
      if (this.searchInputEl) {
        this.searchInputEl.value = "";
      }
      this.currentAreaContext = null;
      this.currentWidgetId = null;
      this.usedWidgetIds = new Set();
    });
  }

  /**
   * Show the modal
   * @param {Object} context - Area context { dashboardId, sectionId, areaId, rowId }
   * @param {number|null} currentWidgetId - Currently selected widget ID (for edit mode)
   * @param {Set<number>|Array<number>} usedWidgetIds - Widget IDs already used in the dashboard
   */
  async show(context, currentWidgetId = null, usedWidgetIds = new Set()) {
    // Lazy init if not already initialized
    if (!this.modalElement) {
      this.init();
    }

    if (!this.modalInstance) {
      console.error("Widget selector modal could not be initialized");
      return;
    }

    this.currentAreaContext = context;
    this.currentWidgetId = currentWidgetId;
    // Convert to Set if array was passed
    this.usedWidgetIds = usedWidgetIds instanceof Set ? usedWidgetIds : new Set(usedWidgetIds);

    // Load data if not already loaded
    if (!this.dataLoaded) {
      await this.loadData();
    }

    // Render content
    this.renderSidebar();
    this.renderGraphGrid();

    // Show modal
    this.modalInstance.show();

    // Focus search input
    setTimeout(() => {
      if (this.searchInputEl) {
        this.searchInputEl.focus();
      }
    }, 200);
  }

  /**
   * Hide the modal
   */
  hide() {
    if (this.modalInstance) {
      this.modalInstance.hide();
    }
  }

  /**
   * Load graphs and categories data
   * @returns {Promise<void>}
   */
  async loadData() {
    // If already loaded, return immediately
    if (this.dataLoaded) return;

    // If currently loading, wait for it to complete
    if (this.isLoading && this.loadingPromise) {
      return this.loadingPromise;
    }

    this.isLoading = true;

    // Show loading state (only if modal elements exist)
    if (this.widgetGridEl) {
      this.showLoading();
    }

    this.loadingPromise = (async () => {
      try {
        const response = await Ajax.post("get_widgets_for_selector", {});

        if (response.success) {
          this.graphs = response.data.graphs || [];
          this.categories = response.data.categories || [];

          // Select all categories by default (convert wcid to int for consistent comparison)
          this.selectedCategories = new Set(this.categories.map((c) => parseInt(c.wcid, 10)));

          this.dataLoaded = true;
        } else {
          console.error("Failed to load widgets:", response.message);
          this.graphs = [];
          this.categories = [];
        }
      } catch (error) {
        console.error("Error loading widgets:", error);
        this.graphs = [];
        this.categories = [];
      } finally {
        this.isLoading = false;
        this.loadingPromise = null;
      }
    })();

    return this.loadingPromise;
  }

  /**
   * Show loading state
   */
  showLoading() {
    if (this.widgetGridEl) {
      this.widgetGridEl.innerHTML = `
        <div class="widget-loading">
          <div class="spinner"></div>
          <span class="loading-text">Loading widgets...</span>
        </div>
      `;
    }
    if (this.categoryListEl) {
      this.categoryListEl.innerHTML = `
        <div class="widget-loading" style="padding: 1rem;">
          <div class="spinner" style="width: 20px; height: 20px;"></div>
        </div>
      `;
    }
  }

  /**
   * Render the category sidebar
   */
  renderSidebar() {
    if (!this.categoryListEl) return;

    if (this.categories.length === 0) {
      this.categoryListEl.innerHTML = `
        <div class="widget-empty-state" style="padding: 1rem;">
          <div class="empty-description">No categories found</div>
        </div>
      `;
      return;
    }

    let html = "";
    for (const category of this.categories) {
      const categoryId = parseInt(category.wcid, 10);
      const isSelected = this.selectedCategories.has(categoryId);
      const activeClass = isSelected ? "active" : "";
      const bgStyle = isSelected
        ? `background-color: ${category.color || "#6c757d"}; border-color: ${category.color || "#6c757d"}; color: #fff;`
        : "";
      const checkIcon = isSelected ? "fa-check-circle" : "fa-circle";

      html += `
        <button type="button"
                class="btn category-chip ${activeClass}"
                data-category-id="${categoryId}"
                data-color="${category.color || "#6c757d"}"
                style="${bgStyle}">
          <i class="far ${checkIcon} chip-check-icon"></i>
          ${category.icon ? `<i class="fas ${category.icon}"></i>` : ""}
          ${this.escapeHtml(category.name)}
          <span class="category-chip-count">(${category.graph_count || 0})</span>
        </button>
      `;
    }

    this.categoryListEl.innerHTML = html;
  }

  /**
   * Render the graph grid
   */
  renderGraphGrid() {
    if (!this.widgetGridEl) return;

    const filteredGraphs = this.filterGraphs();

    // Update count subtitle
    if (this.countSubtitleEl) {
      this.countSubtitleEl.textContent = `${filteredGraphs.length} widget${filteredGraphs.length !== 1 ? "s" : ""} available`;
    }

    if (filteredGraphs.length === 0) {
      this.widgetGridEl.innerHTML = `
        <div class="widget-empty-state">
          <i class="fas fa-search empty-icon"></i>
          <div class="empty-title">No widgets found</div>
          <div class="empty-description">Try adjusting your search or category filters</div>
        </div>
      `;
      return;
    }

    let html = "";
    for (const graph of filteredGraphs) {
      html += this.renderGraphCard(graph);
    }

    this.widgetGridEl.innerHTML = html;
  }

  /**
   * Render a single graph card
   * @param {Object} graph - Graph data
   * @returns {string} HTML string
   */
  renderGraphCard(graph) {
    const isCurrent = this.currentWidgetId === graph.gid;
    const isUsedElsewhere = this.usedWidgetIds.has(graph.gid) && !isCurrent;
    const graphTypeIcons = {
      bar: "fa-chart-bar",
      line: "fa-chart-line",
      pie: "fa-chart-pie",
      area: "fa-chart-area",
      scatter: "fa-braille",
    };
    const icon = graphTypeIcons[graph.graph_type] || "fa-chart-bar";

    // Render category badges
    let categoriesHtml = "";
    if (graph.categories && graph.categories.length > 0) {
      categoriesHtml = '<div class="widget-card-categories widget-category-badges">';
      for (const cat of graph.categories.slice(0, 3)) {
        categoriesHtml += `
          <span class="widget-category-badge widget-category-badge-sm"
                style="background-color: ${cat.color || "#6c757d"};">
            ${cat.icon ? `<i class="fas ${cat.icon}"></i>` : ""}
            ${this.escapeHtml(cat.name)}
          </span>
        `;
      }
      if (graph.categories.length > 3) {
        categoriesHtml += `<span class="widget-category-badge widget-category-badge-sm" style="background-color: #6c757d;">+${graph.categories.length - 3}</span>`;
      }
      categoriesHtml += "</div>";
    }

    // Checkbox - only show for widgets that are used in the dashboard
    // Current selection: green/primary color with check
    // Used elsewhere in dashboard: gray with check
    let checkboxHtml = "";
    if (isCurrent) {
      // Current selection - primary color
      checkboxHtml = `<div class="widget-card-checkbox checked current"><i class="fas fa-check-circle"></i></div>`;
    } else if (isUsedElsewhere) {
      // Used elsewhere in dashboard - gray
      checkboxHtml = `<div class="widget-card-checkbox checked used"><i class="fas fa-check-circle"></i></div>`;
    }

    // Card classes
    const cardClasses = ["widget-card"];
    if (isCurrent) cardClasses.push("selected");
    if (isUsedElsewhere) cardClasses.push("used-elsewhere");

    return `
      <div class="${cardClasses.join(" ")}"
           data-graph-id="${graph.gid}"
           tabindex="0"
           role="button"
           aria-label="Select ${this.escapeHtml(graph.name)}">
        ${checkboxHtml}
        <div class="widget-card-header">
          <div class="widget-card-icon ${graph.graph_type}">
            <i class="fas ${icon}"></i>
          </div>
          <div class="widget-card-info">
            <h4 class="widget-card-name">${this.escapeHtml(graph.name)}</h4>
            <span class="widget-card-type">${graph.graph_type} Chart</span>
          </div>
        </div>
        ${graph.description ? `<p class="widget-card-description">${this.escapeHtml(graph.description)}</p>` : ""}
        ${categoriesHtml}
      </div>
    `;
  }

  /**
   * Filter graphs based on search query and selected categories
   * @returns {Array} Filtered graphs
   */
  filterGraphs() {
    return this.graphs.filter((graph) => {
      // Category filter (OR logic - show if in ANY selected category)
      if (this.selectedCategories.size > 0) {
        const graphCategoryIds = graph.category_ids || [];
        const hasMatchingCategory = graphCategoryIds.some((id) => this.selectedCategories.has(id));
        if (!hasMatchingCategory && graphCategoryIds.length > 0) {
          return false;
        }
        // Show graphs without categories only if all categories are selected
        if (graphCategoryIds.length === 0 && this.selectedCategories.size !== this.categories.length) {
          return false;
        }
      }

      // Search filter
      if (this.searchQuery) {
        const searchLower = this.searchQuery.toLowerCase();
        const nameMatch = graph.name.toLowerCase().includes(searchLower);
        const descMatch = graph.description && graph.description.toLowerCase().includes(searchLower);
        if (!nameMatch && !descMatch) {
          return false;
        }
      }

      return true;
    });
  }

  /**
   * Handle category checkbox toggle
   * @param {number} categoryId
   * @param {boolean} isChecked
   */
  handleCategoryToggle(categoryId, isChecked) {
    if (isChecked) {
      this.selectedCategories.add(categoryId);
    } else {
      this.selectedCategories.delete(categoryId);
    }
    this.renderGraphGrid();
  }

  /**
   * Select all categories
   */
  handleSelectAll() {
    this.selectedCategories = new Set(this.categories.map((c) => parseInt(c.wcid, 10)));
    this.renderSidebar();
    this.renderGraphGrid();
  }

  /**
   * Clear all categories
   */
  handleClearAll() {
    this.selectedCategories.clear();
    this.renderSidebar();
    this.renderGraphGrid();
  }

  /**
   * Handle graph card selection
   * @param {number} graphId
   */
  handleGraphSelect(graphId) {
    // If clicking the currently selected graph, deselect it
    if (this.currentWidgetId === graphId) {
      this.handleGraphDeselect();
      return;
    }

    // Call the onSelect callback
    if (this.onSelect && this.currentAreaContext) {
      this.onSelect(graphId, this.currentAreaContext);
    }

    // Hide the modal
    this.hide();
  }

  /**
   * Handle graph deselection (remove widget)
   */
  handleGraphDeselect() {
    if (this.onDeselect && this.currentAreaContext) {
      this.onDeselect(this.currentAreaContext);
    }

    this.hide();
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

  /**
   * Reload data (for refresh scenarios)
   */
  async reload() {
    this.dataLoaded = false;
    await this.loadData();
    this.renderSidebar();
    this.renderGraphGrid();
  }

  /**
   * Get graph by ID
   * @param {number} graphId - Graph ID to find
   * @returns {Object|null} Graph object or null if not found
   */
  getGraphById(graphId) {
    // Convert to int for comparison since gid from API is int
    const id = parseInt(graphId, 10);
    return this.graphs.find((g) => parseInt(g.gid, 10) === id) || null;
  }

  /**
   * Check if data has been loaded
   * @returns {boolean}
   */
  isDataLoaded() {
    return this.dataLoaded;
  }
}

export default WidgetSelectorModal;
