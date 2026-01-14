<?php

/**
 * Rapidkart is the Registry class for all system objects.
 * Simplified version for Dynamic Graph Creator standalone use.
 *
 * @author Dynamic Graph Creator
 */
class Rapidkart
{

      private static $rapidkart = null;

      /* Database Object */
      private $DB;

      /**
       * Main class constructor private
       */
      private function __construct()
      {
            $this->DB = new SQLiDatabase();
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
       * Get the instance of the Database and return it
       *
       * @return Database Instance of the Database
       */
      public function getDB()
      {
            return $this->DB;
      }
}
