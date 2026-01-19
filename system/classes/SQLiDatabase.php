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
      public $resultset, $last_query, $current_row, $field_value, $mysql_error, $mysql_errorno;

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
       * Get number of rows in resultset
       *
       * @param $resultset The result set
       * @return int
       */
      public function resultNumRows($resultset = null)
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
      public function getMysqlError()
      {
            return $this->mysql_error;
      }

      /**
       * Get mysql error number
       *
       * @return int
       */
      public function getMysqlErrorNo()
      {
            return $this->mysql_errorno;
      }

      /**
       * Try to connect to the database
       *
       * @return Boolean - Whether the connection was successful
       */
      public function tryConnect()
      {
            $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS);
            if ($conn) {
                  $db_select = mysqli_select_db($conn, DB_NAME);
                  if ($db_select) {
                        return true;
                  }
            }
            return false;
      }

      /**
       * Select a database to use
       *
       * @param $database The name of the database
       */
      public function selectDatabase($database)
      {
            if ($database) {
                  mysqli_select_db($this->connection, $database);
            } else {
                  mysqli_select_db($this->connection, DB_NAME);
            }
      }

      /**
       * Quickly update a field or fields in a table
       *
       * @param $table The table to update
       * @param $fields_values An associative array with the key being the fieldname and the value is the value
       * @param $where The where clause to limit the update
       */
      public function updateFields($table, $fields_values, $where = "1=1")
      {
            $sql = "UPDATE $table SET ";
            $last_element = count($fields_values);
            $count = 0;
            $values = array();
            foreach ($fields_values as $key => $value) {
                  $count++;
                  $s = " $key='::$count::', ";
                  $values["::$count::"] = $value;
                  if ($last_element == $count) {
                        $s = " $key='::$count::'";
                  }
                  $sql .= $s;
            }
            $sql .= " WHERE $where";
            $res = $this->query($sql, $values);
            return $res;
      }

      /**
       * Quickly grab the data from a field from a specified table
       *
       * @param $table The name of the table to update
       * @param $field_name The field which to return
       * @param $where The where clause to limit the resultset
       *
       * @return The field value for the requested field
       */
      public function getFieldValue($table, $field_name, $where = "1=1")
      {
            $sql = "SELECT $field_name FROM $table WHERE $where LIMIT 1";
            $res = $this->fetchObject($this->query($sql));
            if ($res) {
                  $this->field_value = $field_name;
                  return $res->$field_name;
            }
            return false;
      }

      /**
       * Method to fetch a row from the resultset in the form of an array
       *
       * @param $resultset The result set from which to fetch the row
       */
      public function fetchArray($resultset = null)
      {
            if (!$resultset) {
                  return false;
            }
            $this->current_row = mysqli_fetch_array($resultset);
            return $this->current_row;
      }

      /**
       * Set Auto Commit for transactions
       */
      public function autoCommit($status = false)
      {
            mysqli_autocommit($this->connection, $status);
      }

      /**
       * Roll back during transaction
       */
      public function rollBack()
      {
            mysqli_rollback($this->connection);
            mysqli_commit($this->connection);
      }

      /**
       * Commit
       */
      public function commit()
      {
            mysqli_commit($this->connection);
      }

      /**
       * Affected Rows
       */
      public function affectedRows()
      {
            return mysqli_affected_rows($this->connection);
      }

      public function executeTransactions($args)
      {
            $this->autoCommit(false);
            foreach ($args as $value) {
                  if ($value['object']->$value['function']()) {
                        $this->rollBack();
                        $this->autoCommit(true);
                        return false;
                  }
            }
            $this->commit();
            $this->autoCommit(true);
            return true;
      }
}
