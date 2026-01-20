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
     * Generate outlet options HTML for select dropdown
     * Simplified version of Rapidkart's getViewOutletList
     *
     * @param array $outlets Array of outlet objects from OutletManager::getUserCheckPoint
     * @param int $obj_bit Whether to use object methods (1) or properties (0)
     * @param mixed $filter Reference to store filter info (optional)
     * @return string HTML options string
     */
    public static function getViewOutletList($outlets, $obj_bit = 0, &$filter = null)
    {
        $html = '';

        if (empty($outlets)) {
            return $html;
        }

        foreach ($outlets as $outlet) {
            if ($obj_bit) {
                // Use getter methods
                $id = $outlet->getId();
                $name = $outlet->getName();
            } else {
                // Use properties directly
                $id = $outlet->id;
                $name = $outlet->name;
            }

            $html .= '<option value="' . htmlspecialchars($id) . '">' . htmlspecialchars($name) . '</option>';
        }

        return $html;
    }
}
