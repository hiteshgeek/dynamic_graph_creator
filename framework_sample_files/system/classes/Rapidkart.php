<?php

/**
 * Rapidkart  is the Registry class for all system objects.
 * 
 * @author Sohil Gupta
 * @since 20121214
 * @updated 20140616
 */
class Rapidkart
{

      private static $rapidkart = null;

      /* Database Object */
      private $DB;
      private $ApiDB;
      private $ReportDB;
      private $ReportQueryDB;
      private $ServerDB;
      private $URL;
      private $themeRegistry;
      private $user;

      /**
       * Main class constructor private
       */
      private function __construct()
      {
            $this->DB = new SQLiDatabase();
            $this->URL = JPath::urlArgs();
            $this->themeRegistry = new ThemeRegistry();
            $this->user = null;
      }

      /**
       * @return Rapidkart - an instance of Rapidkart
       */
      public static function getInstance()
      {
            if (self::$rapidkart == null) {
                  self::$rapidkart = new Rapidkart();
            }

            return self::$rapidkart;
      }

      /**
       * Run necessary bootstrap operations in the entire system
       */
      public function bootstrap()
      {
            Theme::init();          // Initialize the theme
            Session::init();        // Initialize the session
      }

      /**
       * Get the instance of the Database and return it
       * 
       * @return Database Instance of the Database
       */
      public function getDB()
      {
            return $this->DB;
      }

      /**
       * @return The URL object[] with the different arguments of the URL
       */
      public function getURL()
      {
            return $this->URL;
      }

      /**
       * @return ThemeRegistry - The Theme Registry
       */
      public function getThemeRegistry()
      {
            return $this->themeRegistry;
      }

      /**
       * @return User - The user object of the logged in system user
       */
      public function getUser()
      {
            return $this->user;
      }

      public function getApiDB()
      {
            if (!$this->ApiDB) {
                  $this->ApiDB = new SQLiApiDatabase();
            }
            return $this->ApiDB;
      }

      public function getReportDB()
      {
            if (!$this->ReportDB) {
                  $this->ReportDB = new SQLiReportDatabase();
            }
            return $this->ReportDB;
      }

      public function getReportQueryDB()
      {
            if (!$this->ReportQueryDB) {
                  $this->ReportQueryDB = new ReportDBQuery();
            }
            return $this->ReportQueryDB;
      }
}
