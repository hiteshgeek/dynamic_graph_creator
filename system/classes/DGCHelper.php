<?php

/**
 * DGCHelper - UI Components for Dynamic Graph Creator
 * Contains DGC-specific rendering functions that work in both
 * DGC dev environment and Rapidkart live environment
 *
 * @author Dynamic Graph Creator
 */
class DGCHelper
{
    /**
     * Render an empty state component
     *
     * @param string $icon FontAwesome icon class (e.g., 'fa-chart-bar', 'fa-th-large')
     * @param string $title The main heading text
     * @param string $description The description text (supports HTML)
     * @param string|null $buttonText The button label (null or empty to hide button)
     * @param string|null $buttonUrl The button URL (use '#' or empty for button element, null to hide)
     * @param string $color Color theme: 'blue' (default), 'green', 'orange', 'purple'
     * @param string $buttonClass Optional additional CSS class for the button (for JS handlers)
     * @return string HTML markup for the empty state
     */
    public static function renderEmptyState($icon, $title, $description, $buttonText = null, $buttonUrl = null, $color = 'blue', $buttonClass = '')
    {
        $colorClass = ' empty-state-' . htmlspecialchars($color);
        $html = '<div class="empty-state' . $colorClass . '">';
        $html .= '<div class="empty-state-content">';
        $html .= '<div class="empty-state-icon">';
        $html .= '<i class="fas ' . htmlspecialchars($icon) . '"></i>';
        $html .= '</div>';
        $html .= '<h3>' . htmlspecialchars($title) . '</h3>';
        $html .= '<p>' . $description . '</p>'; // Allow HTML in description

        // Only render button if buttonText is provided
        if (!empty($buttonText)) {
            // Use button element if URL is empty or '#', otherwise use anchor
            if (empty($buttonUrl) || $buttonUrl === '#') {
                $btnClass = 'btn btn-primary btn-sm' . ($buttonClass ? ' ' . htmlspecialchars($buttonClass) : '');
                $html .= '<button type="button" class="' . $btnClass . '" autofocus>';
                $html .= '<i class="fas fa-plus"></i> ' . htmlspecialchars($buttonText);
                $html .= '</button>';
            } else {
                $btnClass = 'btn btn-primary btn-sm' . ($buttonClass ? ' ' . htmlspecialchars($buttonClass) : '');
                $html .= '<a href="' . htmlspecialchars($buttonUrl) . '" class="' . $btnClass . '" autofocus>';
                $html .= '<i class="fas fa-plus"></i> ' . htmlspecialchars($buttonText);
                $html .= '</a>';
            }
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a dashboard cell empty state
     * Used for empty areas/cells within dashboard sections (both edit and view mode)
     *
     * @param string $icon FontAwesome icon class (e.g., 'fa-chart-line', 'fa-plus-circle')
     * @param string $message The message to display below the icon
     * @param bool $viewMode If true, renders view mode (no "add" prompt)
     * @return string HTML markup for the dashboard cell empty state
     */
    public static function renderDashboardCellEmpty($icon = 'fa-plus-circle', $message = 'Add content here', $viewMode = false)
    {
        // In view mode (preview), show different icon and message
        if ($viewMode) {
            $icon = 'fa-chart-simple';
            $message = 'No widget assigned';
        }

        $html = '<div class="dashboard-cell-empty' . ($viewMode ? ' view-mode' : '') . '">';
        $html .= '<div class="cell-empty-icon">';
        $html .= '<i class="fas ' . htmlspecialchars($icon) . '"></i>';
        $html .= '</div>';
        $html .= '<div class="cell-empty-message">';
        $html .= htmlspecialchars($message);
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render dashboard widget content (graph, etc.)
     * Used in preview mode to render actual widget containers
     *
     * @param array $content The content object with type, widgetId, widgetType, config
     * @return string HTML for the widget content
     */
    public static function renderDashboardWidgetContent($content)
    {
        $widgetId = isset($content['widgetId']) ? intval($content['widgetId']) : 0;
        $widgetType = isset($content['widgetType']) ? $content['widgetType'] : 'graph';

        // Get graph info for display
        $graphName = 'Graph #' . $widgetId;
        $graphType = 'bar';
        $graphDescription = '';

        if ($widgetId) {
            $graph = new Graph($widgetId);
            if ($graph->getId()) {
                $graphName = $graph->getName();
                $graphType = $graph->getGraphType();
                $graphDescription = $graph->getDescription();
            }
        }

        $html = '<div class="area-content has-widget" data-widget-id="' . $widgetId . '" data-widget-type="' . htmlspecialchars($widgetType) . '" data-graph-type="' . htmlspecialchars($graphType) . '">';
        $html .= '<div class="widget-graph-wrapper">';
        $html .= '<div class="widget-graph-header">';
        $html .= '<div class="widget-graph-title-section">';
        $html .= '<span class="widget-graph-name">' . htmlspecialchars($graphName) . '</span>';

        // Add description with read more/less toggle if description exists
        if (!empty($graphDescription)) {
            $escapedDesc = htmlspecialchars($graphDescription);
            $html .= '<div class="widget-graph-description collapsed" data-full-text="' . $escapedDesc . '">';
            $html .= '<span class="description-text">' . $escapedDesc . '</span>';
            $html .= '<span class="description-toggle" onclick="this.parentElement.classList.toggle(\'collapsed\'); this.parentElement.classList.toggle(\'expanded\'); this.textContent = this.parentElement.classList.contains(\'collapsed\') ? \'read more\' : \'read less\';">read more</span>';
            $html .= '</div>';
        }

        $html .= '</div>'; // end title-section
        $html .= '</div>'; // end header
        $html .= '<div class="widget-graph-container" data-graph-id="' . $widgetId . '">';
        $html .= self::renderChartSkeleton($graphType);
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a chart skeleton loader
     * Used as loading placeholder while charts load
     *
     * @param string $chartType The chart type (bar, line, pie)
     * @return string HTML for the skeleton
     */
    public static function renderChartSkeleton($chartType = 'bar')
    {
        $html = '<div class="widget-graph-loading chart-skeleton">';

        if ($chartType === 'pie') {
            $html .= '<div class="skeleton-chart-pie">';
            $html .= '<div class="skeleton-pie"></div>';
            $html .= '</div>';
        } else {
            // Bar/Line chart skeleton
            $html .= '<div class="skeleton-chart-bar">';
            $html .= '<div class="skeleton-bar"></div>';
            $html .= '<div class="skeleton-bar"></div>';
            $html .= '<div class="skeleton-bar"></div>';
            $html .= '<div class="skeleton-bar"></div>';
            $html .= '<div class="skeleton-bar"></div>';
            $html .= '<div class="skeleton-bar"></div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render a full dashboard skeleton loader
     * Used as loading placeholder while dashboard structure loads
     *
     * @param int $sections Number of sections to show (default 2)
     * @return string HTML for the skeleton
     */
    public static function renderDashboardSkeleton($sections = 2)
    {
        $html = '<div class="dashboard-skeleton" id="dashboard-skeleton">';

        for ($i = 0; $i < $sections; $i++) {
            $colClass = $i === 0 ? '' : ' skeleton-two-col';
            $cols = $i === 0 ? 3 : 2;

            $html .= '<div class="skeleton-section' . $colClass . '">';
            $html .= '<div class="skeleton-section-header">';
            $html .= '<div class="skeleton-title"></div>';
            if ($i === 0) {
                $html .= '<div class="skeleton-badge"></div>';
            }
            $html .= '</div>';
            $html .= '<div class="skeleton-grid">';

            for ($j = 0; $j < $cols; $j++) {
                $chartType = ($i === 0 && $j === 1) ? 'pie' : 'bar';
                $html .= '<div class="skeleton-widget">';
                $html .= '<div class="skeleton-widget-header">';
                $html .= '<div class="skeleton-widget-title">';
                $html .= '<div class="skeleton-line"></div>';
                $html .= '<div class="skeleton-line"></div>';
                $html .= '</div>';
                $html .= '<div class="skeleton-widget-icon"></div>';
                $html .= '</div>';

                if ($chartType === 'pie') {
                    $html .= '<div class="skeleton-widget-chart skeleton-chart-pie">';
                    $html .= '<div class="skeleton-pie"></div>';
                    $html .= '</div>';
                } else {
                    $html .= '<div class="skeleton-widget-chart skeleton-chart-bar">';
                    $html .= '<div class="skeleton-bar"></div>';
                    $html .= '<div class="skeleton-bar"></div>';
                    $html .= '<div class="skeleton-bar"></div>';
                    $html .= '<div class="skeleton-bar"></div>';
                    $html .= '<div class="skeleton-bar"></div>';
                    $html .= '<div class="skeleton-bar"></div>';
                    $html .= '</div>';
                }

                $html .= '</div>';
            }

            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render the Widget Selector Modal
     * Used in dashboard builder and template builder for selecting graphs
     *
     * @return string HTML for the modal
     */
    public static function renderWidgetSelectorModal()
    {
        $html = '<div id="widget-selector-modal" class="modal fade" tabindex="-1">';
        $html .= '<div class="modal-dialog modal-lg modal-dialog-scrollable">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header">';
        $html .= '<div class="modal-title-wrapper">';
        $html .= '<h5 class="modal-title">Select Widget</h5>';
        $html .= '<span class="modal-subtitle text-muted" id="widget-count-subtitle">Loading...</span>';
        $html .= '</div>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        // Category Sidebar
        $html .= '<div class="widget-sidebar">';
        $html .= '<div class="sidebar-header">';
        $html .= '<span class="sidebar-title">Categories</span>';
        $html .= '<div class="sidebar-actions">';
        $html .= '<button type="button" class="btn btn-link btn-sm" id="select-all-categories">All</button>';
        $html .= '<span class="text-muted">|</span>';
        $html .= '<button type="button" class="btn btn-link btn-sm" id="clear-all-categories">None</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="sidebar-categories" id="widget-category-list"></div>';
        $html .= '</div>';
        // Main Content
        $html .= '<div class="widget-main">';
        $html .= '<div class="widget-search">';
        $html .= '<div class="search-input-wrapper">';
        $html .= '<i class="fas fa-search search-icon"></i>';
        $html .= '<input type="text" class="form-control" id="widget-search-input" placeholder="Search widgets...">';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="widget-grid-container" id="widget-grid-container">';
        $html .= '<div class="widget-grid" id="widget-grid"></div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a UUID v4
     * Format: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
     * PHP 5.6 compatible
     *
     * @return string UUID string
     */
    public static function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 4095) | 16384,
            mt_rand(0, 16383) | 32768,
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }

    /**
     * Generate a short unique ID (8 characters from UUID)
     *
     * @param string $prefix Optional prefix for the ID
     * @return string Short unique ID with optional prefix
     */
    public static function generateShortId($prefix = '')
    {
        $uuid = self::generateUUID();
        $shortId = substr(str_replace('-', '', $uuid), 0, 8);
        return $prefix ? $prefix . '-' . $shortId : $shortId;
    }

    /**
     * Render the page header component
     *
     * @param array $options Configuration options:
     *   - 'title' (string) Required. The page title
     *   - 'backUrl' (string|null) Optional. URL for back button
     *   - 'backLabel' (string) Optional. Back button label (default: 'Back')
     *   - 'badges' (array) Optional. Array of badge configs: ['label' => 'text', 'icon' => 'fa-icon', 'class' => 'badge-system']
     *   - 'leftContent' (string) Optional. Additional HTML for left section (after theme toggle)
     *   - 'rightContent' (string) Optional. HTML for right section (page-header-right)
     *   - 'titleEditable' (bool) Optional. If true, title can be edited via modal (default: false)
     *   - 'titleId' (string) Optional. ID for the title element when editable
     *   - 'titleDescription' (string) Optional. Description to show in info tooltip
     * @return string HTML markup for the page header
     */
    public static function renderPageHeader($options)
    {
        $title = isset($options['title']) ? $options['title'] : '';
        $backUrl = isset($options['backUrl']) ? $options['backUrl'] : null;
        $backLabel = isset($options['backLabel']) ? $options['backLabel'] : 'Back';
        $badges = isset($options['badges']) ? $options['badges'] : [];
        $leftContent = isset($options['leftContent']) ? $options['leftContent'] : '';
        $rightContent = isset($options['rightContent']) ? $options['rightContent'] : '';
        $titleEditable = isset($options['titleEditable']) ? $options['titleEditable'] : false;
        $titleId = isset($options['titleId']) ? $options['titleId'] : '';
        $titleDescription = isset($options['titleDescription']) ? $options['titleDescription'] : '';

        $html = '<div class="page-header">';
        $html .= '<div class="page-header-left">';

        // Back button
        if ($backUrl) {
            $html .= '<a href="' . htmlspecialchars($backUrl) . '" class="btn btn-secondary btn-sm" data-back-to-list>';
            $html .= '<i class="fas fa-arrow-left"></i> ' . htmlspecialchars($backLabel);
            $html .= '</a>';
        }

        // Title
        if ($titleEditable) {
            $html .= '<div class="dashboard-name-editor">';
            $idAttr = $titleId ? ' id="' . htmlspecialchars($titleId) . '"' : '';
            $html .= '<h1' . $idAttr . '>' . htmlspecialchars($title) . '</h1>';
            // Info icon with description tooltip (supports multiline with <br>)
            if ($titleDescription) {
                $descriptionHtml = nl2br(htmlspecialchars($titleDescription));
                $html .= '<span class="description-tooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="' . $descriptionHtml . '"><i class="fas fa-info-circle"></i></span>';
            }
            $html .= '<button id="edit-dashboard-details-btn" class="btn btn-icon btn-outline-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Details"><i class="fas fa-pencil"></i></button>';
            $html .= '</div>';
        } else {
            $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
        }

        // Badges
        foreach ($badges as $badge) {
            $badgeClass = isset($badge['class']) ? $badge['class'] : 'badge-secondary';
            $html .= '<span class="badge ' . htmlspecialchars($badgeClass) . '">';
            if (isset($badge['icon'])) {
                $html .= '<i class="fas ' . htmlspecialchars($badge['icon']) . '"></i> ';
            }
            $html .= htmlspecialchars($badge['label']);
            $html .= '</span>';
        }

        // Additional left content (custom buttons, etc.)
        if ($leftContent) {
            $html .= $leftContent;
        }

        $html .= '</div>'; // End page-header-left

        // Right section
        $html .= '<div class="page-header-right">';
        if ($rightContent) {
            $html .= $rightContent;
        }

        // Theme toggle (always last, with separator)
        // Icon is set immediately via inline script to prevent flash
        $html .= '<div class="header-separator"></div>';
        $html .= '<button type="button" class="btn btn-icon theme-toggle-btn">';
        $html .= '<i class="fas"></i>';
        $html .= '</button>';
        $html .= '<script>(function(){var m=localStorage.getItem("dgc-theme-mode")||"light",i=document.querySelector(".theme-toggle-btn i");if(i){i.classList.add(m==="dark"?"fa-moon":m==="system"?"fa-desktop":"fa-sun");}})();</script>';

        $html .= '</div>'; // End page-header-right

        $html .= '</div>'; // End page-header

        return $html;
    }

    /**
     * Render JavaScript to set window.companyStartDate for datepicker presets
     * Gets company start date from LicenceCompanies using BaseConfig::$company_id
     *
     * @return string HTML script tag or empty string if no valid date
     */
    public static function renderCompanyStartDateScript()
    {
        if (BaseConfig::$company_id > 0) {
            $company = new LicenceCompanies(BaseConfig::$company_id);
            $companyStartDate = $company->getStartDate();
            if ($companyStartDate && $companyStartDate !== '0000-00-00') {
                return '<script>window.companyStartDate = "' . htmlspecialchars($companyStartDate) . '";</script>';
            }
        }
        return '';
    }

    /**
     * Company ID that has access to DGC admin pages (templates, graph, filter)
     */
    const DGC_ADMIN_COMPANY_ID = 232;

    /**
     * Check if current user has access to DGC admin pages
     * Only admin users from company ID 232 can access templates, graph, and filter pages
     *
     * @return bool True if user has access, false otherwise
     */
    public static function hasAdminAccess()
    {
        // Check company ID
        if (BaseConfig::$company_id != self::DGC_ADMIN_COMPANY_ID) {
            return false;
        }

        // Check if user is admin
        $user = SystemConfig::getUser();
        if (!$user || !$user->getIsAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Require admin access for protected pages
     * Redirects to dashboard list if user doesn't have access
     *
     * @param string $redirectUrl URL to redirect to if access denied (default: dashboard list)
     * @return void
     */
    public static function requireAdminAccess($redirectUrl = '?urlq=home')
    {
        if (!self::hasAdminAccess()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Render widget category badges HTML
     * Reusable component for displaying category badges across all pages
     *
     * @param array $categories Array of WidgetCategory objects or arrays
     * @param string $size Size variant: 'sm', 'md' (default), 'lg', 'xl', 'xxl'
     * @param bool $stacked If true, badges are displayed vertically stacked
     * @return string HTML for category badges
     */
    public static function renderWidgetCategoryBadges($categories, $size = 'md', $stacked = false)
    {
        if (empty($categories)) {
            return '';
        }

        // Build size class (md is default, no class needed)
        $sizeClass = '';
        if (in_array($size, array('sm', 'lg', 'xl', 'xxl'))) {
            $sizeClass = ' widget-category-badge-' . $size;
        }

        // Build container class with optional stacked modifier
        $containerClass = 'widget-category-badges';
        if ($stacked) {
            $containerClass .= ' widget-category-badges-stacked';
        }
        $html = '<div class="' . $containerClass . '">';

        foreach ($categories as $cat) {
            $name = is_array($cat) ? $cat['name'] : $cat->getName();
            $color = is_array($cat) ? $cat['color'] : $cat->getColor();
            $icon = is_array($cat) ? (isset($cat['icon']) ? $cat['icon'] : '') : $cat->getIcon();

            $html .= '<span class="widget-category-badge' . $sizeClass . '" style="background-color: ' . htmlspecialchars($color) . ';">';
            if (!empty($icon)) {
                $html .= '<i class="fas ' . htmlspecialchars($icon) . '"></i> ';
            }
            $html .= htmlspecialchars($name);
            $html .= '</span>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render dashboard filter bar with filters
     * Renders filters directly via PHP using same markup as graph-view.tpl.php
     *
     * @param array $filterKeys Array of filter keys in display order (e.g., ['company_list', 'outlet_list', 'main_datepicker'])
     * @return string HTML markup for the filter bar with filters
     */
    public static function renderDashboardFilterBar($filterKeys = array())
    {
        if (empty($filterKeys)) {
            return '';
        }

        // Normalize keys (ensure :: prefix)
        $normalizedKeys = array();
        foreach ($filterKeys as $key) {
            $normalizedKeys[] = (strpos($key, '::') === 0) ? $key : '::' . $key;
        }

        // Fetch filters by keys
        $matchedFilters = DataFilterManager::getByKeys($normalizedKeys);

        // Build filters array in order of keys
        $filters = array();
        foreach ($normalizedKeys as $key) {
            if (isset($matchedFilters[$key])) {
                $filters[] = $matchedFilters[$key]->toArray();
            }
        }

        if (empty($filters)) {
            return '';
        }

        // Use dashboard-filter-bar class (styles in _filter-bar.scss)
        $html = '<div class="dashboard-filter-bar">';

        // Filters list
        $html .= '<div class="filters-list" id="dashboard-filters">';
        foreach ($filters as $filter) {
            $html .= self::renderFilterInput($filter);
        }
        $html .= '</div>';

        // Filter actions (same as graph-view)
        $html .= '<div class="filter-actions">';
        // Collapse/Expand toggle button
        $html .= '<button type="button" class="btn btn-icon btn-outline-secondary filter-collapse-btn" title="Collapse Filters">';
        $html .= '<i class="fas fa-chevron-up"></i>';
        $html .= '</button>';
        $html .= '<span class="filter-collapsed-label">Filters</span>';
        $html .= '<div class="filter-actions-separator visible"></div>';
        $html .= '<div class="auto-apply-toggle">';
        $html .= '<span class="auto-apply-label">Live Filtering</span>';
        $html .= '<div class="form-check form-switch custom-switch">';
        $html .= '<input class="form-check-input" type="checkbox" role="switch" id="dashboard-auto-apply-switch">';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="filter-actions-separator"></div>';
        $html .= '<button type="button" class="btn btn-primary btn-sm filter-apply-btn dashboard-filter-apply-btn">';
        $html .= '<i class="fas fa-check"></i> Apply Filters';
        $html .= '</button>';
        $html .= '</div>';

        $html .= '</div>'; // End card

        return $html;
    }

    /**
     * Render a single filter input
     * Same markup as used in graph-view.tpl.php for consistency
     *
     * @param array $filter Filter data array from toArray()
     * @return string HTML markup for the filter input
     */
    public static function renderFilterInput($filter)
    {
        $filterKey = $filter['filter_key'];
        $filterKeyClean = ltrim($filterKey, ':');
        $filterType = $filter['filter_type'];
        $filterLabel = $filter['filter_label'];
        $defaultValue = isset($filter['default_value']) ? $filter['default_value'] : '';
        $options = isset($filter['options']) ? $filter['options'] : array();

        // Get filter config for inline display
        $filterConfig = isset($filter['filter_config']) ? $filter['filter_config'] : '';
        $filterConfigArr = $filterConfig ? json_decode($filterConfig, true) : array();
        $isInline = isset($filterConfigArr['inline']) && $filterConfigArr['inline'];

        $html = '<div class="filter-input-item" data-filter-key="' . htmlspecialchars($filterKeyClean) . '">';
        $html .= '<div class="filter-input-header">';
        $html .= '<label class="filter-input-label">' . htmlspecialchars($filterLabel) . '</label>';
        $html .= '</div>';

        // Render input based on type
        switch ($filterType) {
            case 'select':
                $html .= '<select class="form-control form-control-sm filter-input" name="' . htmlspecialchars($filterKeyClean) . '">';
                $html .= '<option value="">-- Select --</option>';
                foreach ($options as $opt) {
                    $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                    $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                    $selected = ($value == $defaultValue) ? 'selected' : '';
                    $html .= '<option value="' . htmlspecialchars($value) . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
                }
                $html .= '</select>';
                break;

            case 'multi_select':
                $html .= '<div class="dropdown filter-multiselect-dropdown" data-filter-name="' . htmlspecialchars($filterKeyClean) . '">';
                $html .= '<button class="btn btn-outline-secondary dropdown-toggle filter-multiselect-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">';
                $html .= '<span class="filter-multiselect-placeholder">-- Select multiple --</span>';
                $html .= '</button>';
                $html .= '<div class="dropdown-menu filter-multiselect-options">';
                $html .= '<div class="filter-multiselect-header">';
                $html .= '<div class="filter-multiselect-actions">';
                $html .= '<button type="button" class="btn btn-link btn-sm multiselect-select-all">All</button>';
                $html .= '<span class="filter-multiselect-divider">|</span>';
                $html .= '<button type="button" class="btn btn-link btn-sm multiselect-select-none">None</button>';
                $html .= '</div>';
                $html .= '<input type="text" class="form-control form-control-sm multiselect-search" placeholder="Search...">';
                $html .= '</div>';
                foreach ($options as $index => $opt) {
                    $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                    $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                    $isSelected = is_array($opt) && isset($opt['is_selected']) && $opt['is_selected'];
                    $optId = 'multiselect-' . $filterKeyClean . '-' . $index;
                    $html .= '<div class="dropdown-item filter-multiselect-option">';
                    $html .= '<div class="form-check">';
                    $html .= '<input class="form-check-input" type="checkbox" name="' . htmlspecialchars($filterKeyClean) . '[]" value="' . htmlspecialchars($value) . '" id="' . $optId . '" ' . ($isSelected ? 'checked' : '') . '>';
                    $html .= '<label class="form-check-label" for="' . $optId . '">' . htmlspecialchars($label) . '</label>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
                $html .= '</div>'; // End dropdown-menu
                $html .= '</div>'; // End dropdown
                break;

            case 'checkbox':
                $html .= '<div class="filter-checkbox-group' . ($isInline ? ' inline' : '') . '">';
                foreach ($options as $index => $opt) {
                    $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                    $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                    $isSelected = is_array($opt) && isset($opt['is_selected']) && $opt['is_selected'];
                    $optId = 'checkbox-' . $filterKeyClean . '-' . $index;
                    $html .= '<div class="form-check">';
                    $html .= '<input class="form-check-input" type="checkbox" name="' . htmlspecialchars($filterKeyClean) . '[]" value="' . htmlspecialchars($value) . '" id="' . $optId . '" ' . ($isSelected ? 'checked' : '') . '>';
                    $html .= '<label class="form-check-label" for="' . $optId . '">' . htmlspecialchars($label) . '</label>';
                    $html .= '</div>';
                }
                $html .= '</div>';
                break;

            case 'radio':
                $html .= '<div class="filter-radio-group' . ($isInline ? ' inline' : '') . '">';
                foreach ($options as $index => $opt) {
                    $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                    $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                    $isSelected = is_array($opt) && isset($opt['is_selected']) && $opt['is_selected'];
                    $checked = $isSelected || ($value == $defaultValue) ? 'checked' : '';
                    $optId = 'radio-' . $filterKeyClean . '-' . $index;
                    $html .= '<div class="form-check">';
                    $html .= '<input class="form-check-input" type="radio" name="' . htmlspecialchars($filterKeyClean) . '" value="' . htmlspecialchars($value) . '" id="' . $optId . '" ' . $checked . '>';
                    $html .= '<label class="form-check-label" for="' . $optId . '">' . htmlspecialchars($label) . '</label>';
                    $html .= '</div>';
                }
                $html .= '</div>';
                break;

            case 'date':
                $html .= '<input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="' . htmlspecialchars($filterKeyClean) . '" value="' . htmlspecialchars($defaultValue) . '" data-picker-type="single" placeholder="Select date" autocomplete="off">';
                break;

            case 'date_range':
                $html .= '<input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="' . htmlspecialchars($filterKeyClean) . '" data-picker-type="range" placeholder="Select date range" autocomplete="off">';
                break;

            case 'main_datepicker':
                $html .= '<input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="' . htmlspecialchars($filterKeyClean) . '" data-picker-type="main" placeholder="Select date range" autocomplete="off">';
                break;

            case 'number':
                $html .= '<input type="number" class="form-control form-control-sm filter-input" name="' . htmlspecialchars($filterKeyClean) . '" value="' . htmlspecialchars($defaultValue) . '" placeholder="Enter number">';
                break;

            default: // text
                $html .= '<input type="text" class="form-control form-control-sm filter-input" name="' . htmlspecialchars($filterKeyClean) . '" value="' . htmlspecialchars($defaultValue) . '" placeholder="Enter value">';
        }

        $html .= '</div>'; // End filter-input-item

        return $html;
    }
}
