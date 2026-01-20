<?php

/**
 * Base configuration class for database credentials
 * Reads from SystemConfig which loads from .env file
 *
 * @author Dynamic Graph Creator
 */
class BaseConfig
{
      // Static properties required for Session management (same as live project)
      public static $company_id = 0;
      public static $licence_id = 0;
      public static $company_start_date = "";

      // Notification server (placeholder - not used in DGC but required by Session class)
      const NOTIFICATION_SERVER = "";

      /**
       * Get database server/host
       * @return string
       */
      public static function getDbServer()
      {
            return SystemConfig::getDbHost();
      }

      /**
       * Get database user
       * @return string
       */
      public static function getDbUser()
      {
            return SystemConfig::getDbUser();
      }

      /**
       * Get database password
       * @return string
       */
      public static function getDbPass()
      {
            return SystemConfig::getDbPass();
      }

      /**
       * Get database name
       * @return string
       */
      public static function getDbName()
      {
            return SystemConfig::getDbName();
      }
}

// Define constants for backward compatibility with framework pattern
define('DB_SERVER', BaseConfig::getDbServer());
define('DB_USER', BaseConfig::getDbUser());
define('DB_PASS', BaseConfig::getDbPass());
define('DB_NAME', BaseConfig::getDbName());
