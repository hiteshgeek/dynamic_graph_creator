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
     * @param string $type 'css' or 'js'
     * @return string|null
     */
    public static function getAsset($type)
    {
        $manifestPath = GraphConfig::distPath() . 'manifest.json';

        if (!file_exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (isset($manifest[$type])) {
            return GraphConfig::distUrl() . $manifest[$type];
        }

        return null;
    }

    /**
     * Get CSS asset URL
     * @return string|null
     */
    public static function getCss()
    {
        return self::getAsset('css');
    }

    /**
     * Get JS asset URL
     * @return string|null
     */
    public static function getJs()
    {
        return self::getAsset('js');
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
        header('Location: ' . GraphConfig::baseUrl() . '?urlq=' . $url);
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
