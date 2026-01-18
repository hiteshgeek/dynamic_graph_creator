<?php

/**
 * Specifies the configuration of the System
 * Reads database credentials from .env file
 *
 * @author Dynamic Graph Creator
 */
class SystemConfig
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
     * @return String The protocol used by the current URL. Whether http or https
     */
    public static function protocol()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    }

    /**
     * @return String The HTTP_HOST
     */
    public static function host()
    {
        return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
    }

    /**
     * @return String The Base URL of the website
     */
    public static function baseUrl()
    {
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        return rtrim(self::protocol() . self::host() . $scriptPath, '/') . '/';
    }

    /**
     * @return String The base Path of the website
     */
    public static function basePath()
    {
        return dirname(dirname(__DIR__)) . '/';
    }

    /**
     * @return String The path of the directory containing system files
     */
    public static function systemsDirPath()
    {
        return self::basePath() . "system/";
    }

    /**
     * @return String The url of the directory containing system files
     */
    public static function systemsDirUrl()
    {
        return self::baseUrl() . "system/";
    }

    /**
     * @return String The Path of the directory containing include files
     */
    public static function includesPath()
    {
        return self::systemsDirPath() . 'includes/';
    }

    /**
     * @return String The URL of the directory containing include files
     */
    public static function includesUrl()
    {
        return self::systemsDirUrl() . 'includes/';
    }

    /**
     * @return String The Path of the directory containing utility files
     */
    public static function utilitiesPath()
    {
        return self::systemsDirPath() . 'utilities/';
    }

    /**
     * @return String The Path of the directory containing class files
     */
    public static function classesPath()
    {
        return self::systemsDirPath() . "classes/";
    }

    /**
     * @return String The Path of the directory containing interface files
     */
    public static function interfacesPath()
    {
        return self::systemsDirPath() . "interfaces/";
    }

    /**
     * @return String The path of the directory containing templates
     */
    public static function templatesPath()
    {
        return self::systemsDirPath() . "templates/";
    }

    /**
     * @return String The URL of the directory containing scripts
     */
    public static function scriptsUrl()
    {
        return self::systemsDirUrl() . "scripts/";
    }

    /**
     * @return String The URL of the directory containing styles
     */
    public static function stylesUrl()
    {
        return self::systemsDirUrl() . "styles/";
    }

    /**
     * @return String The path of the dist directory
     */
    public static function distPath()
    {
        return self::basePath() . 'dist/';
    }

    /**
     * @return String The URL of the dist directory
     */
    public static function distUrl()
    {
        return self::baseUrl() . 'dist/';
    }

    /**
     * @return String The Path of the directory containing config files
     */
    public static function configPath()
    {
        return self::systemsDirPath() . "config/";
    }

    /**
     * @return String The URL of the themes directory
     */
    public static function themesUrl()
    {
        return self::baseUrl() . "themes/";
    }

    /**
     * @return String The path of the themes directory
     */
    public static function themesPath()
    {
        return self::basePath() . "themes/";
    }
}
