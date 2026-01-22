<?php

/**
 * Local project utility functions
 * Contains functions specific to Dynamic Graph Creator dev environment that are not in the live project
 *
 * @author Dynamic Graph Creator
 */
class LocalUtility
{
    /**
     * Get asset file with cache busting hash
     *
     * @param string $module Module name ('graph' or 'filter')
     * @param string $type Asset type ('css' or 'js')
     * @return string|null
     */
    public static function getAsset($module, $type)
    {
        $manifestPath = LocalProjectConfig::distPath() . 'manifest.json';

        if (!file_exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $key = $module . '_' . $type;

        if (isset($manifest[$key])) {
            return LocalProjectConfig::distUrl() . $manifest[$key];
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
     *   LocalUtility::addModuleCss('common');
     *   LocalUtility::addModuleCss('graph');
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
     *   LocalUtility::addModuleJs('common');
     *   LocalUtility::addModuleJs('graph');
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
     * Get per-page script URL with cache busting hash
     *
     * @param string $module Module name ('graph', 'dashboard', 'data-filter')
     * @param string $scriptName Script name without extension (e.g., 'graph-list', 'dashboard-preview')
     * @return string|null
     */
    public static function getPageScript($module, $scriptName)
    {
        $manifestPath = LocalProjectConfig::distPath() . 'manifest.json';

        if (!file_exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        // Convert dashes to underscores for manifest key lookup
        $moduleKey = str_replace('-', '_', $module);
        $scriptKey = str_replace('-', '_', $scriptName);
        $key = 'page_' . $moduleKey . '_' . $scriptKey;

        if (isset($manifest[$key])) {
            return LocalProjectConfig::distUrl() . $manifest[$key];
        }

        return null;
    }

    /**
     * Add per-page script from dist folder to ThemeRegistry
     * Helper function to simplify adding page-specific scripts with cache busting
     *
     * @param string $module Module name ('graph', 'dashboard', 'data-filter')
     * @param string $scriptName Script name without extension (e.g., 'graph-list', 'dashboard-preview')
     * @param int $weight Load order (lower = earlier)
     *
     * Usage in .inc.php:
     *   LocalUtility::addPageScript('graph', 'graph-list');
     *   LocalUtility::addPageScript('dashboard', 'dashboard-preview', 15);
     *
     * TODO: When migrating to rapidkart structure, change to:
     *   $theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph-list.js');
     */
    public static function addPageScript($module, $scriptName, $weight = 10)
    {
        $theme = Rapidkart::getInstance()->getThemeRegistry();
        $js = self::getPageScript($module, $scriptName);
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
}
