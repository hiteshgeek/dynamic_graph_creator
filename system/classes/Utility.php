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
     * @param string $description The description text
     * @param string $buttonText The button label
     * @param string $buttonUrl The button URL
     * @param string $color Color theme: 'blue' (default), 'green', 'orange', 'purple'
     * @return string HTML markup for the empty state
     */
    public static function renderEmptyState($icon, $title, $description, $buttonText, $buttonUrl, $color = 'blue')
    {
        $colorClass = ' empty-state-' . htmlspecialchars($color);
        $html = '<div class="empty-state' . $colorClass . '">';
        $html .= '<div class="empty-state-content">';
        $html .= '<div class="empty-state-icon">';
        $html .= '<i class="fas ' . htmlspecialchars($icon) . '"></i>';
        $html .= '</div>';
        $html .= '<h3>' . htmlspecialchars($title) . '</h3>';
        $html .= '<p>' . htmlspecialchars($description) . '</p>';
        $html .= '<a href="' . htmlspecialchars($buttonUrl) . '" class="btn btn-primary">';
        $html .= '<i class="fas fa-plus"></i> ' . htmlspecialchars($buttonText);
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
