<?php

/**
 * Configuration class for the Graph Creator library
 * Reads database credentials from .env file
 *
 * @author Dynamic Graph Creator
 */
class GraphConfig
{
    private static $config = null;

    /**
     * Load configuration from .env file
     */
    private static function loadEnv()
    {
        if (self::$config !== null) {
            return;
        }

        self::$config = array();
        $envFile = self::basePath() . '.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    self::$config[trim($key)] = trim($value);
                }
            }
        }
    }

    /**
     * Get a config value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        self::loadEnv();
        return isset(self::$config[$key]) ? self::$config[$key] : $default;
    }

    /**
     * Get database host
     * @return string
     */
    public static function getDbHost()
    {
        return self::get('DB_HOST', 'localhost');
    }

    /**
     * Get database user
     * @return string
     */
    public static function getDbUser()
    {
        return self::get('DB_USER', 'root');
    }

    /**
     * Get database password
     * @return string
     */
    public static function getDbPass()
    {
        return self::get('DB_PASS', '');
    }

    /**
     * Get database name
     * @return string
     */
    public static function getDbName()
    {
        return self::get('DB_NAME', 'graph_creator');
    }

    /**
     * Get base path of the application
     * @return string
     */
    public static function basePath()
    {
        return dirname(dirname(__DIR__)) . '/';
    }

    /**
     * Get base URL of the application
     * @return string
     */
    public static function baseUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        return rtrim($protocol . $host . $scriptPath, '/') . '/';
    }

    /**
     * Get system directory path
     * @return string
     */
    public static function systemPath()
    {
        return self::basePath() . 'system/';
    }

    /**
     * Get system directory URL
     * @return string
     */
    public static function systemUrl()
    {
        return self::baseUrl() . 'system/';
    }

    /**
     * Get classes directory path
     * @return string
     */
    public static function classesPath()
    {
        return self::systemPath() . 'classes/';
    }

    /**
     * Get includes directory path
     * @return string
     */
    public static function includesPath()
    {
        return self::systemPath() . 'includes/';
    }

    /**
     * Get templates directory path
     * @return string
     */
    public static function templatesPath()
    {
        return self::systemPath() . 'templates/';
    }

    /**
     * Get dist directory path
     * @return string
     */
    public static function distPath()
    {
        return self::basePath() . 'dist/';
    }

    /**
     * Get dist directory URL
     * @return string
     */
    public static function distUrl()
    {
        return self::baseUrl() . 'dist/';
    }
}
