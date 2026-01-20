<?php

/**
 * Specifies the configuration of the System
 *
 * @author Sohil Gupta
 * @since 20140621
 * @updated 20140623
 */
class SystemConfig
{
    private static $config = null;
    private static $user = null;
    public static $lock_date = "";

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
     * Get logged in user
     * @return AdminUser
     */
    public static function getUser()
    {
        if (!self::$user) {

            $domain_name = $_SERVER['HTTP_HOST'];
            $licence = LicenceManager::checkDomainExists($domain_name);
            if (!$licence) {
                header('Location: http://www.sixorbit.com/');
                exit;
            }
            // baseconfig settings
            BaseConfig::$licence_id = $licence->getId();
            LicenceManager::setCustomizedData($licence);
            self::$user = new AdminUser(Session::loggedInUid());
            $session_company_id = Session::getSessionVariable()['company_id'];
            if ($session_company_id <= 0) {
                $session_company_id = self::$user->getCompanyId();
                $_SESSION['company_id'] = $session_company_id;
            }
            BaseConfig::$company_id = $session_company_id;
            $company = new LicenceCompanies(BaseConfig::$company_id);
            SystemConfig::$lock_date = $company->getLockDate();
            BaseConfig::$company_start_date = $company->getStartDate();
            BaseConfig::$company_gstr_date = $company->getGstrDate();

            $variable_mappings = SiteVariableManager::getVariableConfig(BaseConfig::$company_id);
            if ($variable_mappings) {
                global $variable_config;
                $variable_config = $variable_mappings;
            }



            BaseConfig::$if_customized = $licence->getIfCustomized() ? TRUE : FALSE;
            if (BaseConfig::$if_customized) {
                BaseConfig::$customization_box = $licence->getCustomizationBox();
            }

            self::$user->getRoles();
            self::$user->getPermission();
            self::$user->getSubOrdinates();
        }

        return self::$user;
    }

    /**
     * @return String The protocol used by the current URL. Whether http or https
     */
    public static function protocol()
    {
        return $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    }

    /**
     * @return String The HTTP_HOST
     */
    public static function host()
    {
        return $_SERVER['SERVER_NAME'];
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
     * @return String The Base URL of the website
     */
    public static function baseUrl()
    {
        return rtrim(SystemConfig::protocol() . SystemConfig::host() . '/' . BaseConfig::SITE_FOLDER, '/') . '/';
    }

    /**
     * @return String The base Path of the website
     */
    public static function basePath()
    {
        if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') {
            $base_path = $_SERVER['DOCUMENT_ROOT'];
        } else {
            $base_path = BaseConfig::SITE_PATH;
        }
        return rtrim($base_path . '/' . BaseConfig::SITE_FOLDER, '/') . '/';
    }

    /**
     * @return String The path of the directory containing system files
     */
    public static function systemsDirPath()
    {
        return SystemConfig::basePath() . "system/";
    }

    /**
     * @return String The url of the directory containing system files
     */
    public static function systemsDirUrl()
    {
        return SystemConfig::baseUrl() . "system/";
    }

    /**
     * @return String The Path of the directory containing include files of the core system
     */
    public static function includesPath()
    {
        return SystemConfig::systemsDirPath() . 'includes/';
    }

    /**
     * @return String The Path of the directory containing include files of the core system
     */
    public static function includesUrl()
    {
        return SystemConfig::systemsDirUrl() . 'includes/';
    }

    /**
     * @return String The Path of the directory containing include files of the core system
     */
    public static function utilitiesPath()
    {
        return SystemConfig::systemsDirPath() . 'utilities/';
    }

    /**
     * @return String The Path of the directory containing class files of the core system
     */
    public static function classesPath()
    {
        return SystemConfig::systemsDirPath() . "classes/";
    }

    /**
     * @return String The Path of the directory containing interface files of the core system
     */
    public static function interfacesPath()
    {
        return SystemConfig::systemsDirPath() . "interfaces/";
    }

    /**
     * @return String The Path of the directory containing exception class files of the core system
     */
    public static function exceptionsPath()
    {
        return SystemConfig::systemsDirPath() . "exceptions/";
    }

    /**
     * @return String The Path of the directory containing system modules
     */
    public static function modulesPath()
    {
        return SystemConfig::systemsDirPath() . "modules/";
    }

    /**
     * @return String The URL of the directory containing system modules
     */
    public static function modulesUrl()
    {
        return SystemConfig::systemsDirUrl() . "modules/";
    }

    /**
     * @return String The Path of the directory containing all themes
     */
    public static function themesPath()
    {
        return SystemConfig::basePath() . "themes/";
    }

    /**
     * @return String The URL of the directory containing all themes
     */
    public static function themesUrl()
    {
        return SystemConfig::baseUrl() . "themes/";
    }

    /**
     * @return String The URL of the directory containing scripts for the currently in-use theme
     */
    public static function scriptsUrl()
    {
        return SystemConfig::systemsDirUrl() . "scripts/";
    }

    /**
     * @return String The path of the directory containing templates for the currently in-use theme
     */
    public static function templatesPath()
    {
        return SystemConfig::systemsDirPath() . "templates/";
    }

    /**
     * @return String The URL of the directory containing templates for the currently in-use theme
     */
    public static function stylesUrl()
    {
        return SystemConfig::systemsDirUrl() . "styles/";
    }

    /**
     * @return String The URL of the directory containing templates for the currently in-use theme
     */
    public static function servicesPath()
    {
        return SystemConfig::baseUrl() . "services/";
    }

    public static function distPath()
    {
        return SystemConfig::basePath() . "dist/";
    }

    public static function distUrl()
    {
        return SystemConfig::baseUrl() . "dist/";
    }
}
