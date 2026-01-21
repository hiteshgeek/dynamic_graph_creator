<?php

/**
 * Local project configuration
 * Contains functions specific to Dynamic Graph Creator dev environment that are not in the live project
 *
 * @author Dynamic Graph Creator
 */
class LocalProjectConfig
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
        $envFile = SystemConfig::basePath() . '.env';

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
     * Get a config value from .env
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
     * @return String The path to dist folder
     */
    public static function distPath()
    {
        return SystemConfig::basePath() . "dist/";
    }

    /**
     * @return String The URL to dist folder
     */
    public static function distUrl()
    {
        return SystemConfig::baseUrl() . "dist/";
    }
}
