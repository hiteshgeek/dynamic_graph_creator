<?php

/**
 * Utility class with helper methods
 * Similar to framework's Utility class
 *
 * @author Dynamic Graph Creator
 */
class Utility
{
    /**
     * Send successful AJAX response
     *
     * @param string $message
     * @param mixed $data
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
     *
     * @param string $message
     * @param mixed $data
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
     * Get asset file with cache busting hash
     *
     * @param string $module Module name ('graph' or 'filter')
     * @param string $type Asset type ('css' or 'js')
     * @return string|null
     */
    public static function getAsset($module, $type)
    {
        $manifestPath = SystemConfig::distPath() . 'manifest.json';

        if (!file_exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $key = $module . '_' . $type;

        if (isset($manifest[$key])) {
            return SystemConfig::distUrl() . $manifest[$key];
        }

        return null;
    }

    /**
     * Get CSS asset URL for a module
     *
     * @param string $module Module name ('graph' or 'filter')
     * @return string|null
     */
    public static function getCss($module = 'graph')
    {
        return self::getAsset($module, 'css');
    }

    /**
     * Get JS asset URL for a module
     *
     * @param string $module Module name ('graph' or 'filter')
     * @return string|null
     */
    public static function getJs($module = 'graph')
    {
        return self::getAsset($module, 'js');
    }

    /**
     * Add CSS from dist folder to ThemeRegistry
     * Helper function to simplify adding module CSS
     *
     * @param string $module Module name ('graph', 'filter', 'dashboard', 'common')
     * @param int $weight Load order (lower = earlier)
     *
     * Usage in .inc.php:
     *   Utility::addModuleCss('common');
     *   Utility::addModuleCss('graph');
     *
     * TODO: When migrating to rapidkart structure, change to:
     *   $theme->addCss(SystemConfig::stylesUrl() . 'graph/graph.css');
     */
    public static function addModuleCss($module, $weight = 10)
    {
        $theme = Rapidkart::getInstance()->getThemeRegistry();
        $css = self::getCss($module);
        if ($css) {
            $theme->addCss($css, $weight);
        }
    }

    /**
     * Add JS from dist folder to ThemeRegistry
     * Helper function to simplify adding module JS
     *
     * @param string $module Module name ('graph', 'filter', 'dashboard', 'common')
     * @param int $weight Load order (lower = earlier)
     *
     * Usage in .inc.php:
     *   Utility::addModuleJs('common');
     *   Utility::addModuleJs('graph');
     *
     * TODO: When migrating to rapidkart structure, change to:
     *   $theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph.js');
     */
    public static function addModuleJs($module, $weight = 10)
    {
        $theme = Rapidkart::getInstance()->getThemeRegistry();
        $js = self::getJs($module);
        if ($js) {
            $theme->addScript($js, $weight);
        }
    }

    /**
     * Parse URL into segments
     * @return array
     */
    public static function parseUrl()
    {
        $url = isset($_GET['urlq']) ? $_GET['urlq'] : '';
        $url = trim($url, '/');
        $segments = explode('/', $url);

        return array_filter($segments, function ($segment) {
            return $segment !== '';
        });
    }

    /**
     * Redirect to a URL
     *
     * @param string $url
     */
    public static function redirect($url)
    {
        header('Location: ' . SystemConfig::baseUrl() . '?urlq=' . $url);
        exit();
    }

    /**
     * Sanitize input string
     *
     * @param string $input
     * @return string
     */
    public static function sanitize($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if request is AJAX
     * @return bool
     */
    public static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
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
     * @return string HTML markup for the dashboard cell empty state
     */
    public static function renderDashboardCellEmpty($icon = 'fa-plus-circle', $message = 'Add content here')
    {
        $html = '<div class="dashboard-cell-empty" tabindex="0" role="button">';
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
}
