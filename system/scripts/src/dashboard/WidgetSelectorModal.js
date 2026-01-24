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
    this.counters = [];
    this.tables = [];
    this.categories = [];
    this.selectedCategories = new Set();
    this.selectedTypes = new Set(); // Selected widget types
    this.searchQuery = "";
    this.currentAreaContext = null;
    this.currentWidgetId = null;
    this.usedWidgetIds = new Set(); // Widget IDs already used in the dashboard
    this.isLoading = false;
    this.dataLoaded = false;
    this.loadingPromise = null;

    // Widget types (loaded from database)
    this.widgetTypes = [];

    // Widget type colors (used if not in database)
    this.typeColors = {
      graph: "#4361ee",
      counter: "#10b981",
      table: "#8b5cf6",
      list: "#f59e0b",
      link: "#ef4444",
    };

    // Callbacks
    this.onSelect = options.onSelect || null;
    this.onDeselect = options.onDeselect || null;

    // DOM elements (cached after init)
    this.typeListEl = null;
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
    this.typeListEl = this.modalElement.querySelector("#widget-type-list");
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
        this.renderWidgetGrid();
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

    // Widget type chip clicks (event delegation)
    if (this.typeListEl) {
      this.typeListEl.addEventListener("click", (e) => {
        const chip = e.target.closest(".type-chip");
        if (chip) {
          const typeId = chip.dataset.typeId;
          const isCurrentlySelected = this.selectedTypes.has(typeId);
          this.handleTypeToggle(typeId, !isCurrentlySelected);

          // Update chip visual state
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
        // Handle "Read more" button click
        const readMoreBtn = e.target.closest(".widget-card-readmore");
        if (readMoreBtn) {
          e.stopPropagation();
          const card = readMoreBtn.closest(".widget-card");
          const expandedEl = card.querySelector(".widget-card-description-expanded");
          if (expandedEl) {
            expandedEl.classList.add("visible");
          }
          return;
        }

        // Handle expanded description close button
        const closeBtn = e.target.closest(".description-expanded-close");
        if (closeBtn) {
          e.stopPropagation();
          const expandedEl = closeBtn.closest(".widget-card-description-expanded");
          if (expandedEl) {
            expandedEl.classList.remove("visible");
          }
          return;
        }

        // Handle card selection (but not if clicking inside expanded description)
        if (e.target.closest(".widget-card-description-expanded")) {
          e.stopPropagation();
          return;
        }

        const card = e.target.closest(".widget-card");
        if (card) {
          // Close any visible expanded description when clicking outside it
          const expandedEl = card.querySelector(".widget-card-description-expanded.visible");
          if (expandedEl) {
            expandedEl.classList.remove("visible");
            return;
          }

          this.handleWidgetCardSelect(card);
        }
      });

      // Keyboard support for cards
      this.widgetGridEl.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          const card = e.target.closest(".widget-card");
          if (card) {
            e.preventDefault();
            this.handleWidgetCardSelect(card);
          }
        }
      });
    }

    // Close expanded descriptions when clicking outside (anywhere in modal)
    this.modalElement.addEventListener("click", (e) => {
      // Don't close if clicking inside expanded description or on read more button
      if (e.target.closest(".widget-card-description-expanded") ||
          e.target.closest(".widget-card-readmore")) {
        return;
      }
      // Close all visible expanded descriptions
      const visibleExpanded = this.modalElement.querySelectorAll(".widget-card-description-expanded.visible");
      visibleExpanded.forEach(el => el.classList.remove("visible"));
    });

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
    // Ensure currentWidgetId is an integer or null
    this.currentWidgetId = currentWidgetId ? parseInt(currentWidgetId, 10) : null;
    // Convert to Set if array was passed (usedWidgetIds should already contain integers)
    this.usedWidgetIds = usedWidgetIds instanceof Set ? usedWidgetIds : new Set(usedWidgetIds);

    // Load data if not already loaded
    if (!this.dataLoaded) {
      await this.loadData();
    }

    // Render content
    this.renderTypeSidebar();
    this.renderSidebar();
    this.renderWidgetGrid();

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
          this.widgetTypes = response.data.widget_types || [];
          this.graphs = response.data.graphs || [];
          this.counters = response.data.counters || [];
          this.tables = response.data.tables || [];
          this.categories = response.data.categories || [];

          // Select all categories by default (convert wcid to int for consistent comparison)
          this.selectedCategories = new Set(this.categories.map((c) => parseInt(c.wcid, 10)));

          // Select all available widget types by default
          this.selectedTypes = new Set();
          if (this.graphs.length > 0) this.selectedTypes.add("graph");
          if (this.counters.length > 0) this.selectedTypes.add("counter");
          if (this.tables.length > 0) this.selectedTypes.add("table");
          // list, link will be added when available

          this.dataLoaded = true;
        } else {
          console.error("Failed to load widgets:", response.message);
          this.widgetTypes = [];
          this.graphs = [];
          this.counters = [];
          this.tables = [];
          this.categories = [];
        }
      } catch (error) {
        console.error("Error loading widgets:", error);
        this.widgetTypes = [];
        this.graphs = [];
        this.counters = [];
        this.tables = [];
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
   * Get widget count by type slug
   * @param {string} typeSlug - Widget type slug
   * @returns {number} Count of widgets of this type
   */
  getWidgetCountByType(typeSlug) {
    switch (typeSlug) {
      case "graph":
        return this.graphs.length;
      case "counter":
        return this.counters.length;
      case "table":
        return this.tables.length;
      default:
        return 0; // list, link not available yet
    }
  }

  /**
   * Get color for widget type
   * @param {Object} type - Widget type object from API
   * @returns {string} Color hex code
   */
  getTypeColor(type) {
    // Use color from API or fallback to predefined colors
    return this.typeColors[type.slug] || "#6c757d";
  }

  /**
   * Render the widget types sidebar
   */
  renderTypeSidebar() {
    if (!this.typeListEl) return;

    if (this.widgetTypes.length === 0) {
      this.typeListEl.innerHTML = `
        <div class="widget-empty-state" style="padding: 0.5rem;">
          <div class="empty-description">No types found</div>
        </div>
      `;
      return;
    }

    let html = "";
    for (const type of this.widgetTypes) {
      const count = this.getWidgetCountByType(type.slug);
      const isAvailable = count > 0;
      const isSelected = this.selectedTypes.has(type.slug);
      const activeClass = isSelected ? "active" : "";
      const disabledClass = !isAvailable ? "disabled" : "";
      const color = this.getTypeColor(type);
      const bgStyle = isSelected
        ? `background-color: ${color}; border-color: ${color}; color: #fff;`
        : "";
      const checkIcon = isSelected ? "fa-check-circle" : "fa-circle";

      html += `
        <button type="button"
                class="btn type-chip ${activeClass} ${disabledClass}"
                data-type-id="${type.slug}"
                data-color="${color}"
                style="${bgStyle}"
                ${!isAvailable ? "disabled" : ""}>
          <i class="far ${checkIcon} chip-check-icon"></i>
          <i class="fas ${type.icon || "fa-cube"}"></i>
          ${this.escapeHtml(type.name)}
          <span class="type-chip-count">(${count})</span>
        </button>
      `;
    }

    this.typeListEl.innerHTML = html;
  }

  /**
   * Handle widget type toggle
   * @param {string} typeId - Type ID
   * @param {boolean} isChecked - Whether type should be selected
   */
  handleTypeToggle(typeId, isChecked) {
    if (isChecked) {
      this.selectedTypes.add(typeId);
    } else {
      this.selectedTypes.delete(typeId);
    }
    this.renderWidgetGrid();
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
          <span class="category-chip-count">(${category.widget_count || category.graph_count || 0})</span>
        </button>
      `;
    }

    this.categoryListEl.innerHTML = html;
  }

  /**
   * Render the widget grid grouped by type
   */
  renderWidgetGrid() {
    if (!this.widgetGridEl) return;

    const filteredGraphs = this.selectedTypes.has("graph") ? this.filterWidgets(this.graphs, "graph") : [];
    const filteredCounters = this.selectedTypes.has("counter") ? this.filterWidgets(this.counters, "counter") : [];
    const filteredTables = this.selectedTypes.has("table") ? this.filterWidgets(this.tables, "table") : [];
    const totalCount = filteredGraphs.length + filteredCounters.length + filteredTables.length;

    // Update count subtitle
    if (this.countSubtitleEl) {
      this.countSubtitleEl.textContent = `${totalCount} widget${totalCount !== 1 ? "s" : ""} available`;
    }

    if (totalCount === 0) {
      this.widgetGridEl.innerHTML = `
        <div class="widget-empty-state">
          <i class="fas fa-search empty-icon"></i>
          <div class="empty-title">No widgets found</div>
          <div class="empty-description">Try adjusting your search, type, or category filters</div>
        </div>
      `;
      return;
    }

    let html = "";

    // Render widgets grouped by type in the order of widget types
    for (const type of this.widgetTypes) {
      if (!this.selectedTypes.has(type.slug)) continue;

      let widgets = [];
      if (type.slug === "graph") {
        widgets = filteredGraphs;
      } else if (type.slug === "counter") {
        widgets = filteredCounters;
      } else if (type.slug === "table") {
        widgets = filteredTables;
      }

      if (widgets.length === 0) continue;

      // Section header
      const color = this.getTypeColor(type);
      html += `
        <div class="widget-section" data-type="${type.slug}">
          <div class="widget-section-header" style="--section-color: ${color};">
            <i class="fas ${type.icon || "fa-cube"}"></i>
            <span class="section-title">${this.escapeHtml(type.name)}</span>
            <span class="section-count">${widgets.length}</span>
          </div>
          <div class="widget-section-grid">
      `;

      // Render cards based on type
      for (const widget of widgets) {
        if (type.slug === "graph") {
          html += this.renderGraphCard(widget);
        } else if (type.slug === "counter") {
          html += this.renderCounterCard(widget);
        } else if (type.slug === "table") {
          html += this.renderTableCard(widget);
        }
      }

      html += `
          </div>
        </div>
      `;
    }

    this.widgetGridEl.innerHTML = html;
  }

  /**
   * Render a single graph card
   * @param {Object} graph - Graph data
   * @returns {string} HTML string
   */
  renderGraphCard(graph) {
    const isCurrent = this.currentWidgetId === parseInt(graph.gid, 10);
    const isUsedElsewhere = this.usedWidgetIds.has(parseInt(graph.gid, 10)) && !isCurrent;
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

    // Status badge - show check icon for current selection or used elsewhere
    let statusBadgeHtml = "";
    if (isCurrent) {
      // Current selection - primary color check
      statusBadgeHtml = `
        <div class="widget-card-status current" title="Current selection">
          <i class="fas fa-check-circle"></i>
        </div>
      `;
    } else if (isUsedElsewhere) {
      // Used elsewhere in dashboard - gray check
      statusBadgeHtml = `
        <div class="widget-card-status used" title="Already added">
          <i class="fas fa-check-circle"></i>
        </div>
      `;
    }

    // Card classes
    const cardClasses = ["widget-card"];
    if (isCurrent) cardClasses.push("selected");
    if (isUsedElsewhere) cardClasses.push("used-elsewhere", "disabled");

    // Accessibility attributes
    const ariaDisabled = isUsedElsewhere ? 'aria-disabled="true"' : '';
    const tabIndex = isUsedElsewhere ? 'tabindex="-1"' : 'tabindex="0"';

    return `
      <div class="${cardClasses.join(" ")}"
           data-graph-id="${graph.gid}"
           data-widget-type="graph"
           ${tabIndex}
           role="button"
           ${ariaDisabled}
           aria-label="${isCurrent ? 'Current selection: ' : isUsedElsewhere ? 'Already added: ' : 'Select '}${this.escapeHtml(graph.name)}">
        ${statusBadgeHtml}
        <div class="widget-card-header">
          <div class="widget-card-icon ${graph.graph_type}">
            <i class="fas ${icon}"></i>
          </div>
          <div class="widget-card-info">
            <h4 class="widget-card-name">${this.escapeHtml(graph.name)}</h4>
            <span class="widget-card-type">${graph.graph_type} Chart</span>
          </div>
        </div>
        ${graph.description ? `
          <div class="widget-card-description-wrapper">
            <p class="widget-card-description">${this.escapeHtml(graph.description)}</p>
            <button type="button" class="widget-card-readmore">Read more</button>
          </div>
          <div class="widget-card-description-expanded">
            <div class="description-expanded-content">
              <p>${this.escapeHtml(graph.description)}</p>
            </div>
            <button type="button" class="description-expanded-close"><i class="fas fa-times"></i></button>
          </div>
        ` : ""}
        ${categoriesHtml}
      </div>
    `;
  }

  /**
   * Render a single counter card
   * @param {Object} counter - Counter data
   * @returns {string} HTML string
   */
  renderCounterCard(counter) {
    const isCurrent = this.currentWidgetId === `counter-${counter.cid}`;
    const widgetKey = `counter-${counter.cid}`;
    const isUsedElsewhere = this.usedWidgetIds.has(widgetKey) && !isCurrent;

    // Render category badges
    let categoriesHtml = "";
    if (counter.categories && counter.categories.length > 0) {
      categoriesHtml = '<div class="widget-card-categories widget-category-badges">';
      for (const cat of counter.categories.slice(0, 3)) {
        categoriesHtml += `
          <span class="widget-category-badge widget-category-badge-sm"
                style="background-color: ${cat.color || "#6c757d"};">
            ${cat.icon ? `<i class="fas ${cat.icon}"></i>` : ""}
            ${this.escapeHtml(cat.name)}
          </span>
        `;
      }
      if (counter.categories.length > 3) {
        categoriesHtml += `<span class="widget-category-badge widget-category-badge-sm" style="background-color: #6c757d;">+${counter.categories.length - 3}</span>`;
      }
      categoriesHtml += "</div>";
    }

    // Status badge - show check icon for current selection or used elsewhere
    let statusBadgeHtml = "";
    if (isCurrent) {
      statusBadgeHtml = `
        <div class="widget-card-status current" title="Current selection">
          <i class="fas fa-check-circle"></i>
        </div>
      `;
    } else if (isUsedElsewhere) {
      statusBadgeHtml = `
        <div class="widget-card-status used" title="Already added">
          <i class="fas fa-check-circle"></i>
        </div>
      `;
    }

    // Card classes
    const cardClasses = ["widget-card", "counter-card"];
    if (isCurrent) cardClasses.push("selected");
    if (isUsedElsewhere) cardClasses.push("used-elsewhere", "disabled");

    // Accessibility attributes
    const ariaDisabled = isUsedElsewhere ? 'aria-disabled="true"' : '';
    const tabIndex = isUsedElsewhere ? 'tabindex="-1"' : 'tabindex="0"';

    return `
      <div class="${cardClasses.join(" ")}"
           data-counter-id="${counter.cid}"
           data-widget-type="counter"
           ${tabIndex}
           role="button"
           ${ariaDisabled}
           aria-label="${isCurrent ? 'Current selection: ' : isUsedElsewhere ? 'Already added: ' : 'Select '}${this.escapeHtml(counter.name)}">
        ${statusBadgeHtml}
        <div class="widget-card-header">
          <div class="widget-card-icon counter" style="background-color: ${counter.color}15; color: ${counter.color};">
            <span class="material-icons">${counter.icon || "analytics"}</span>
          </div>
          <div class="widget-card-info">
            <h4 class="widget-card-name">${this.escapeHtml(counter.name)}</h4>
            <span class="widget-card-type">Counter</span>
          </div>
        </div>
        ${counter.description ? `
          <div class="widget-card-description-wrapper">
            <p class="widget-card-description">${this.escapeHtml(counter.description)}</p>
            <button type="button" class="widget-card-readmore">Read more</button>
          </div>
          <div class="widget-card-description-expanded">
            <div class="description-expanded-content">
              <p>${this.escapeHtml(counter.description)}</p>
            </div>
            <button type="button" class="description-expanded-close"><i class="fas fa-times"></i></button>
          </div>
        ` : ""}
        ${categoriesHtml}
      </div>
    `;
  }

  /**
   * Render a single table card
   * @param {Object} table - Table data
   * @returns {string} HTML string
   */
  renderTableCard(table) {
    const widgetKey = `table-${table.tid}`;
    const isCurrent = this.currentWidgetId === widgetKey;
    const isUsedElsewhere = this.usedWidgetIds.has(widgetKey) && !isCurrent;

    // Render category badges
    let categoriesHtml = "";
    if (table.categories && table.categories.length > 0) {
      categoriesHtml = '<div class="widget-card-categories widget-category-badges">';
      for (const cat of table.categories.slice(0, 3)) {
        categoriesHtml += `
          <span class="widget-category-badge widget-category-badge-sm"
                style="background-color: ${cat.color || "#6c757d"};">
            ${cat.icon ? `<i class="fas ${cat.icon}"></i>` : ""}
            ${this.escapeHtml(cat.name)}
          </span>
        `;
      }
      if (table.categories.length > 3) {
        categoriesHtml += `<span class="widget-category-badge widget-category-badge-sm" style="background-color: #6c757d;">+${table.categories.length - 3}</span>`;
      }
      categoriesHtml += "</div>";
    }

    // Status badge - show check icon for current selection or used elsewhere
    let statusBadgeHtml = "";
    if (isCurrent) {
      statusBadgeHtml = `
        <div class="widget-card-status current" title="Current selection">
          <i class="fas fa-check-circle"></i>
        </div>
      `;
    } else if (isUsedElsewhere) {
      statusBadgeHtml = `
        <div class="widget-card-status used" title="Already added">
          <i class="fas fa-check-circle"></i>
        </div>
      `;
    }

    // Card classes
    const cardClasses = ["widget-card", "table-card"];
    if (isCurrent) cardClasses.push("selected");
    if (isUsedElsewhere) cardClasses.push("used-elsewhere", "disabled");

    // Accessibility attributes
    const ariaDisabled = isUsedElsewhere ? 'aria-disabled="true"' : '';
    const tabIndex = isUsedElsewhere ? 'tabindex="-1"' : 'tabindex="0"';

    return `
      <div class="${cardClasses.join(" ")}"
           data-table-id="${table.tid}"
           data-widget-type="table"
           ${tabIndex}
           role="button"
           ${ariaDisabled}
           aria-label="${isCurrent ? 'Current selection: ' : isUsedElsewhere ? 'Already added: ' : 'Select '}${this.escapeHtml(table.name)}">
        ${statusBadgeHtml}
        <div class="widget-card-header">
          <div class="widget-card-icon table" style="background-color: ${this.typeColors.table}15; color: ${this.typeColors.table};">
            <i class="fas fa-table"></i>
          </div>
          <div class="widget-card-info">
            <h4 class="widget-card-name">${this.escapeHtml(table.name)}</h4>
            <span class="widget-card-type">Table</span>
          </div>
        </div>
        ${table.description ? `
          <div class="widget-card-description-wrapper">
            <p class="widget-card-description">${this.escapeHtml(table.description)}</p>
            <button type="button" class="widget-card-readmore">Read more</button>
          </div>
          <div class="widget-card-description-expanded">
            <div class="description-expanded-content">
              <p>${this.escapeHtml(table.description)}</p>
            </div>
            <button type="button" class="description-expanded-close"><i class="fas fa-times"></i></button>
          </div>
        ` : ""}
        ${categoriesHtml}
      </div>
    `;
  }

  /**
   * Filter widgets (graphs or counters) based on search query and selected categories
   * @param {Array} widgets - Array of widgets
   * @param {string} widgetType - Type of widget ('graph' or 'counter')
   * @returns {Array} Filtered widgets
   */
  filterWidgets(widgets, widgetType) {
    return widgets.filter((widget) => {
      // Category filter (OR logic - show if in ANY selected category)
      if (this.selectedCategories.size > 0) {
        const widgetCategoryIds = widget.category_ids || [];
        const hasMatchingCategory = widgetCategoryIds.some((id) => this.selectedCategories.has(id));
        if (!hasMatchingCategory && widgetCategoryIds.length > 0) {
          return false;
        }
        // Show widgets without categories only if all categories are selected
        if (widgetCategoryIds.length === 0 && this.selectedCategories.size !== this.categories.length) {
          return false;
        }
      }

      // Search filter
      if (this.searchQuery) {
        const searchLower = this.searchQuery.toLowerCase();
        const nameMatch = widget.name.toLowerCase().includes(searchLower);
        const descMatch = widget.description && widget.description.toLowerCase().includes(searchLower);
        if (!nameMatch && !descMatch) {
          return false;
        }
      }

      return true;
    });
  }

  /**
   * Filter graphs based on search query and selected categories
   * @returns {Array} Filtered graphs
   * @deprecated Use filterWidgets instead
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
    this.renderWidgetGrid();
  }

  /**
   * Clear all categories
   */
  handleClearAll() {
    this.selectedCategories.clear();
    this.renderSidebar();
    this.renderWidgetGrid();
  }

  /**
   * Handle widget card selection (graph or counter)
   * @param {HTMLElement} card - The widget card element
   */
  handleWidgetCardSelect(card) {
    const widgetType = card.dataset.widgetType;

    if (widgetType === "counter") {
      const counterId = parseInt(card.dataset.counterId, 10);
      this.handleCounterSelect(counterId);
    } else if (widgetType === "table") {
      const tableId = parseInt(card.dataset.tableId, 10);
      this.handleTableSelect(tableId);
    } else {
      // Default to graph
      const graphId = parseInt(card.dataset.graphId, 10);
      this.handleGraphSelect(graphId);
    }
  }

  /**
   * Handle counter card selection
   * @param {number} counterId
   */
  handleCounterSelect(counterId) {
    const widgetKey = `counter-${counterId}`;

    // If clicking the currently selected counter, deselect it
    if (this.currentWidgetId === widgetKey) {
      this.handleGraphDeselect();
      return;
    }

    // Prevent selecting counters that are already used elsewhere
    if (this.usedWidgetIds.has(widgetKey) && this.currentWidgetId !== widgetKey) {
      return;
    }

    // Call the onSelect callback with counter info
    if (this.onSelect && this.currentAreaContext) {
      this.onSelect({ type: "counter", id: counterId }, this.currentAreaContext);
    }

    this.hide();
  }

  /**
   * Handle table card selection
   * @param {number} tableId
   */
  handleTableSelect(tableId) {
    const widgetKey = `table-${tableId}`;

    // If clicking the currently selected table, deselect it
    if (this.currentWidgetId === widgetKey) {
      this.handleGraphDeselect();
      return;
    }

    // Prevent selecting tables that are already used elsewhere
    if (this.usedWidgetIds.has(widgetKey) && this.currentWidgetId !== widgetKey) {
      return;
    }

    // Call the onSelect callback with table info
    if (this.onSelect && this.currentAreaContext) {
      this.onSelect({ type: "table", id: tableId }, this.currentAreaContext);
    }

    this.hide();
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

    // Prevent selecting graphs that are already used elsewhere in the dashboard
    if (this.usedWidgetIds.has(graphId) && this.currentWidgetId !== graphId) {
      // Graph is already used in another area - don't allow selection
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
    this.renderTypeSidebar();
    this.renderSidebar();
    this.renderWidgetGrid();
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
   * Get counter by ID
   * @param {number} counterId - Counter ID to find
   * @returns {Object|null} Counter object or null if not found
   */
  getCounterById(counterId) {
    const id = parseInt(counterId, 10);
    return this.counters.find((c) => parseInt(c.cid, 10) === id) || null;
  }

  /**
   * Get table by ID
   * @param {number} tableId - Table ID to find
   * @returns {Object|null} Table object or null if not found
   */
  getTableById(tableId) {
    const id = parseInt(tableId, 10);
    return this.tables.find((t) => parseInt(t.tid, 10) === id) || null;
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
