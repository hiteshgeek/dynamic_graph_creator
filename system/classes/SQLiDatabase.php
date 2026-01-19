<?php

/**
 * A Database class implementation for SQLi
 * Simplified version for Dynamic Graph Creator standalone use.
 *
 * @author Dynamic Graph Creator
 */
class SQLiDatabase
{

      private $connection;
      public $resultset, $last_query, $current_row, $mysql_error, $mysql_errorno;

      /**
       * Automatically connect to the database in the constructor
       */
      public function __construct($connect = true)
      {
            if ($connect) {
                  return $this->connect();
            }
      }

      public function getLastQuery()
      {
            return $this->last_query;
      }

      /**
       * Connect to the database
       */
      public function connect()
      {
            $this->connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS);
            if ($this->connection) {
                  $db_select = mysqli_select_db($this->connection, DB_NAME);
                  if ($db_select) {
                        mysqli_set_charset($this->connection, "utf8");
                        return true;
                  }
            }
            return false;
      }

      /**
       * Queries the database to produce a result
       *
       * @param $query The SQL statement to be executed
       * @param $variables An array of variables to replace in the query
       */
      public function query($query, $variables = array())
      {
            foreach ((array) $variables as $key => $value) {
                  $value = mysqli_real_escape_string($this->connection, $value);
                  if (strpos($key, ":::") === 0) {
                        $value = "'" . $value . "'";
                  }
                  $query = str_replace($key, $value, $query);
            }

            $this->last_query = $query;
            $this->resultset = mysqli_query($this->connection, $query);

            $this->mysql_error = mysqli_error($this->connection);
            $this->mysql_errorno = mysqli_errno($this->connection);

            return $this->resultset;
      }

      /**
       * Method to fetch a row from the resultset in the form of an object
       *
       * @param $resultset The result set from which to fetch the row
       */
      public function fetchObject($resultset = null)
      {
            if (!$resultset) {
                  return false;
            }
            $this->current_row = mysqli_fetch_object($resultset);
            return $this->current_row;
      }

      /**
       * Method to fetch a row from the resultset as associative array
       *
       * @param $resultset The result set from which to fetch the row
       */
      public function fetchAssocArray($resultset = null)
      {
            if (!$resultset) {
                  return false;
            }
            return mysqli_fetch_assoc($resultset);
      }

      /**
       * Fetch all rows as associative array
       *
       * @param $resultset The result set
       * @return array
       */
      public function fetchAllAssoc($resultset = null)
      {
            if (!$resultset) {
                  return array();
            }
            $rows = array();
            while ($row = mysqli_fetch_assoc($resultset)) {
                  $rows[] = $row;
            }
            return $rows;
      }

      /**
       * Get number of rows in resultset
       *
       * @param $resultset The result set
       * @return int
       */
      public function numRows($resultset = null)
      {
            if (!$resultset) {
                  $resultset = $this->resultset;
            }
            return mysqli_num_rows($resultset);
      }

      /**
       * Get last insert ID
       * @return int
       */
      public function lastInsertId()
      {
            return mysqli_insert_id($this->connection);
      }

      /**
       * Escape a string for safe SQL use
       *
       * @param $value The value to escape
       * @return string
       */
      public function escapeString($value)
      {
            $value = stripslashes($value);
            return mysqli_real_escape_string($this->connection, $value);
      }

      /**
       * Get mysql error description
       *
       * @return string
       */
      public function getError()
      {
            return $this->mysql_error;
      }

      /**
       * Get mysql error number
       *
       * @return int
       */
      public function getErrorNo()
      {
            return $this->mysql_errorno;
      }
}
