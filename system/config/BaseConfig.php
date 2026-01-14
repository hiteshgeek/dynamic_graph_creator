<?php

/**
 * Base configuration class for database credentials
 * Reads from SystemConfig which loads from .env file
 *
 * @author Dynamic Graph Creator
 */
class BaseConfig
{
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
