<?php

/**
 * Base configuration class for database credentials
 * Reads from GraphConfig which loads from .env file
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
            return GraphConfig::getDbHost();
      }

      /**
       * Get database user
       * @return string
       */
      public static function getDbUser()
      {
            return GraphConfig::getDbUser();
      }

      /**
       * Get database password
       * @return string
       */
      public static function getDbPass()
      {
            return GraphConfig::getDbPass();
      }

      /**
       * Get database name
       * @return string
       */
      public static function getDbName()
      {
            return GraphConfig::getDbName();
      }
}

// Define constants for backward compatibility with framework pattern
define('DB_SERVER', BaseConfig::getDbServer());
define('DB_USER', BaseConfig::getDbUser());
define('DB_PASS', BaseConfig::getDbPass());
define('DB_NAME', BaseConfig::getDbName());
