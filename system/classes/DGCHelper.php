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
    /** Query result page size for data filter testing */
    const QUERY_RESULT_PAGE_SIZE = 10;

    /**
     * Send successful AJAX response
     * DGC-compatible format that works in both dev and live environments
     *
     * @param string $message Success message
     * @param mixed $data Optional response data
     */
    public static function ajaxResponseTrue($message, $data = null)
    {
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $data
        );

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    /**
     * Send error AJAX response
     * DGC-compatible format that works in both dev and live environments
     *
     * @param string $message Error message
     * @param mixed $data Optional response data
     */
    public static function ajaxResponseFalse($message, $data = null)
    {
        $response = array(
            'success' => false,
            'message' => $message,
            'data' => $data
        );

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

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

        // Handle counter widgets
        if ($widgetType === 'counter') {
            return self::renderDashboardCounterContent($widgetId);
        }

        // Handle table widgets
        if ($widgetType === 'table') {
            return self::renderDashboardTableContent($widgetId);
        }

        // Handle graph widgets (default)
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
     * Render counter widget content for dashboard preview
     *
     * @param int $counterId The counter ID
     * @return string HTML for the counter widget
     */
    public static function renderDashboardCounterContent($counterId)
    {
        $counterName = 'Counter #' . $counterId;
        $counterIcon = 'analytics';
        $counterColor = '#4361ee';

        if ($counterId) {
            $counter = new WidgetCounter($counterId);
            if ($counter->getId()) {
                $counterName = $counter->getName();
                $config = $counter->getConfigArray();
                $defaultConfig = WidgetCounter::getDefaultConfig();
                $counterIcon = isset($config['icon']) && $config['icon'] ? $config['icon'] : $defaultConfig['icon'];
                $counterColor = isset($config['color']) && $config['color'] ? $config['color'] : $defaultConfig['color'];
            }
        }

        $html = '<div class="area-content has-widget" data-widget-id="' . $counterId . '" data-widget-type="counter">';
        $html .= '<div class="widget-counter-wrapper">';
        $html .= '<div class="widget-counter-container" data-counter-id="' . $counterId . '" data-counter-icon="' . htmlspecialchars($counterIcon) . '" data-counter-color="' . htmlspecialchars($counterColor) . '">';
        $html .= self::renderCounterSkeleton($counterColor, $counterIcon);
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render table widget content for dashboard preview
     *
     * @param int $tableId The table ID
     * @return string HTML for the table widget
     */
    public static function renderDashboardTableContent($tableId)
    {
        $tableName = 'Table #' . $tableId;

        if ($tableId) {
            $table = new WidgetTable($tableId);
            if ($table->getId()) {
                $tableName = $table->getName();
            }
        }

        $html = '<div class="area-content has-widget" data-widget-id="' . $tableId . '" data-widget-type="table">';
        $html .= '<div class="widget-table-wrapper">';
        $html .= '<div class="widget-table-header">';
        $html .= '<div class="widget-table-title-section">';
        $html .= '<span class="widget-table-name">' . htmlspecialchars($tableName) . '</span>';
        $html .= '</div>'; // end title-section
        $html .= '</div>'; // end header
        $html .= '<div class="widget-table-container" data-table-id="' . $tableId . '">';
        $html .= self::renderTableSkeleton();
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a table skeleton loader
     *
     * @param int $rows Number of skeleton rows to display (default 5)
     * @return string HTML for the skeleton
     */
    public static function renderTableSkeleton($rows = 5)
    {
        $html = '<div class="table-skeleton">';

        // Header row
        $html .= '<div class="skeleton-row skeleton-header">';
        $html .= '<div class="skeleton-cell skeleton-narrow"></div>';
        $html .= '<div class="skeleton-cell skeleton-wide"></div>';
        $html .= '<div class="skeleton-cell"></div>';
        $html .= '<div class="skeleton-cell"></div>';
        $html .= '</div>';

        // Data rows
        for ($i = 0; $i < $rows; $i++) {
            $html .= '<div class="skeleton-row">';
            $html .= '<div class="skeleton-cell skeleton-narrow"></div>';
            $html .= '<div class="skeleton-cell skeleton-wide"></div>';
            $html .= '<div class="skeleton-cell"></div>';
            $html .= '<div class="skeleton-cell"></div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render a counter skeleton loader
     *
     * @param string $color The counter background color
     * @param string $icon The counter icon
     * @param string $size Size variant: 'default' or 'compact' (for dashboard)
     * @return string HTML for the skeleton
     */
    public static function renderCounterSkeleton($color = '#4361ee', $icon = 'analytics', $size = 'compact')
    {
        $sizeClass = $size === 'default' ? 'counter-skeleton--default' : 'counter-skeleton--compact';
        $html = '<div class="counter-skeleton ' . $sizeClass . '" style="background: ' . htmlspecialchars($color) . ';">';
        $html .= '<div class="counter-skeleton-icon"><span class="material-icons">' . htmlspecialchars($icon) . '</span></div>';
        $html .= '<div class="counter-skeleton-content">';
        $html .= '<div class="counter-skeleton-value"></div>';
        $html .= '<div class="counter-skeleton-name"></div>';
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
        $html = '<div class="chart-skeleton">';

        if ($chartType === 'pie') {
            $html .= '<div class="skeleton-chart-pie">';
            $html .= '<div class="skeleton-pie"></div>';
            $html .= '</div>';
        } elseif ($chartType === 'line') {
            $html .= '<div class="skeleton-chart-line">';
            $html .= '<div class="skeleton-line-wave"></div>';
            $html .= '</div>';
        } else {
            // Bar chart skeleton (default)
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
            $html .= '<div class="skeleton-grid">';

            for ($j = 0; $j < $cols; $j++) {
                $chartType = ($i === 0 && $j === 1) ? 'pie' : 'bar';
                $html .= '<div class="skeleton-widget">';
                $html .= '<div class="skeleton-widget-header">';
                $html .= '<div class="skeleton-widget-title">';
                $html .= '<div class="skeleton-line"></div>';
                $html .= '<div class="skeleton-line"></div>';
                $html .= '</div>';
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
        // Sidebar with Types and Categories
        $html .= '<div class="widget-sidebar">';
        // Widget Types Section
        $html .= '<div class="sidebar-section">';
        $html .= '<div class="sidebar-header">';
        $html .= '<span class="sidebar-title">Widget Types</span>';
        $html .= '</div>';
        $html .= '<div class="sidebar-types" id="widget-type-list"></div>';
        $html .= '</div>';
        // Categories Section
        $html .= '<div class="sidebar-section">';
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
     * Throws 403 Forbidden error if user doesn't have access
     *
     * @return void
     */
    public static function requireAdminAccess()
    {
        if (!self::hasAdminAccess()) {
            header('HTTP/1.1 403 Forbidden');
            echo load_403();
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
     * Render the Widgets dropdown menu for navigation
     * Contains links to Graphs, Tables, Lists, Counters
     *
     * @param string $activeItem Currently active widget type (e.g., 'graph', 'table')
     * @return string HTML for the dropdown
     */
    public static function renderWidgetDropdown($activeItem = '')
    {
        $items = array(
            array('slug' => 'graph', 'label' => 'Graphs', 'icon' => 'fa-chart-bar', 'url' => '?urlq=widget-graph'),
            array('slug' => 'table', 'label' => 'Tables', 'icon' => 'fa-table', 'url' => '?urlq=widget-table'),
            // array('slug' => 'list', 'label' => 'Lists', 'icon' => 'fa-list', 'url' => '?urlq=widget-list'),
            array('slug' => 'counter', 'label' => 'Counters', 'icon' => 'fa-hashtag', 'url' => '?urlq=widget-counter'),
        );

        // Find active item for dropdown button text
        $activeLabel = 'Widgets';
        $activeIcon = 'fa-th-large';
        foreach ($items as $item) {
            if ($item['slug'] === $activeItem) {
                $activeLabel = $item['label'];
                $activeIcon = $item['icon'];
                break;
            }
        }

        $html = '<div class="dropdown widget-nav-dropdown">';
        $html .= '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
        $html .= '<i class="fas ' . $activeIcon . '"></i> ' . htmlspecialchars($activeLabel);
        $html .= '</button>';
        $html .= '<ul class="dropdown-menu">';

        foreach ($items as $item) {
            $isActive = ($item['slug'] === $activeItem);
            $activeClass = $isActive ? ' active' : '';
            $html .= '<li>';
            $html .= '<a class="dropdown-item' . $activeClass . '" href="' . htmlspecialchars($item['url']) . '">';
            $html .= '<i class="fas ' . htmlspecialchars($item['icon']) . '"></i> ';
            $html .= htmlspecialchars($item['label']);
            if ($isActive) {
                $html .= ' <i class="fas fa-check ms-auto"></i>';
            }
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

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
        $isRequired = isset($filter['is_required']) && $filter['is_required'] ? '1' : '0';

        // Get filter config for inline display
        $filterConfig = isset($filter['filter_config']) ? $filter['filter_config'] : '';
        $filterConfigArr = $filterConfig ? json_decode($filterConfig, true) : array();
        $isInline = isset($filterConfigArr['inline']) && $filterConfigArr['inline'];

        // Build data attributes
        $dataAttrs = 'data-filter-key="' . htmlspecialchars($filterKeyClean) . '"';
        $dataAttrs .= ' data-filter-type="' . htmlspecialchars($filterType) . '"';
        $dataAttrs .= ' data-is-required="' . $isRequired . '"';
        if ($isRequired === '1' && !empty($defaultValue)) {
            $dataAttrs .= ' data-default-value="' . htmlspecialchars($defaultValue) . '"';
        }

        $html = '<div class="filter-input-item" ' . $dataAttrs . '>';
        $html .= '<div class="filter-input-header">';
        $html .= '<label class="filter-input-label">' . htmlspecialchars($filterLabel);
        if ($isRequired === '1') {
            $html .= ' <span class="required-indicator" title="Required">*</span>';
        }
        $html .= '</label>';
        $html .= '</div>';

        // Render input based on type
        switch ($filterType) {
            case 'select':
                // Find selected option for placeholder
                $selectedLabel = '-- Select --';
                foreach ($options as $opt) {
                    $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                    $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                    if ($value == $defaultValue) {
                        $selectedLabel = $label;
                        break;
                    }
                }

                // Searchable dropdown with radio buttons
                $html .= '<div class="dropdown filter-select-dropdown" data-filter-name="' . htmlspecialchars($filterKeyClean) . '">';
                $html .= '<button class="btn btn-outline-secondary dropdown-toggle filter-select-trigger btn-sm" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">';
                $html .= '<span class="filter-select-placeholder">' . htmlspecialchars($selectedLabel) . '</span>';
                $html .= '</button>';
                $html .= '<div class="dropdown-menu filter-select-options">';

                // Search header
                $html .= '<div class="filter-select-header">';
                $html .= '<input type="text" class="form-control form-control-sm select-search" placeholder="Search...">';
                $html .= '</div>';

                // Empty option
                $html .= '<div class="dropdown-item filter-select-option" data-value="">';
                $html .= '<div class="form-check">';
                $html .= '<input class="form-check-input" type="radio" name="' . htmlspecialchars($filterKeyClean) . '" value="" id="select-' . htmlspecialchars($filterKeyClean) . '-none"' . (empty($defaultValue) ? ' checked' : '') . '>';
                $html .= '<label class="form-check-label" for="select-' . htmlspecialchars($filterKeyClean) . '-none">-- Select --</label>';
                $html .= '</div></div>';

                // Options
                foreach ($options as $index => $opt) {
                    $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                    $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                    $isSelected = ($value == $defaultValue);
                    $optId = 'select-' . $filterKeyClean . '-' . $index;

                    $html .= '<div class="dropdown-item filter-select-option" data-value="' . htmlspecialchars($value) . '">';
                    $html .= '<div class="form-check">';
                    $html .= '<input class="form-check-input" type="radio" name="' . htmlspecialchars($filterKeyClean) . '" value="' . htmlspecialchars($value) . '" id="' . $optId . '"' . ($isSelected ? ' checked' : '') . '>';
                    $html .= '<label class="form-check-label" for="' . $optId . '">' . htmlspecialchars($label) . '</label>';
                    $html .= '</div></div>';
                }

                $html .= '</div>'; // End dropdown-menu

                // Hidden input to store value
                $inputId = 'filter-input-' . htmlspecialchars($filterKeyClean);
                $html .= '<input type="hidden" class="filter-input" id="' . $inputId . '" name="' . htmlspecialchars($filterKeyClean) . '" data-filter-key="' . htmlspecialchars($filterKeyClean) . '" value="' . htmlspecialchars($defaultValue) . '">';
                $html .= '</div>'; // End dropdown
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
