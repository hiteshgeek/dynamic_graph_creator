<?php

/**
 * A Database class implementation for SQLi
 * 
 * @author Sohil Gupta
 * @since 20150228
 */
class SQLiDatabase implements Database
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
       * 
       * @return Boolean - Whether the connection was successful
       */
      public function tryConnect()
      {
            $conn = mysqli_connect(BaseConfig::DB_SERVER, BaseConfig::DB_USER, BaseConfig::DB_PASS);
            if ($conn) {
                  $db_select = mysqli_select_db($conn, BaseConfig::DB_NAME);
                  if ($db_select) {
                        return true;
                  }
            }
            return false;
      }

      /**
       * Connect to the database
       */
      public function connect()
      {
            $this->connection = mysqli_connect(BaseConfig::DB_SERVER, BaseConfig::DB_USER, BaseConfig::DB_PASS);
            if ($this->connection) {
                  $db_select = mysqli_select_db($this->connection, BaseConfig::DB_NAME);
                  if ($db_select) {
                        mysqli_set_charset($this->connection, "utf8");
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
                  /* Select the specified database */
                  mysqli_select_db($this->connection, $database);
            } else {
                  /* If no database specified, select the default database */
                  mysqli_select_db($this->connection, BaseConfig::DB_NAME);
            }
      }

      /**
       * Queries the database to produce a result
       * 
       * @param $query The SQL statement to be executed
       * @param $variables An array of variables to replace in the query, these are passed in an array so that they can be escaped
       * 
       * @example query("SELECT * FROM user WHERE name LIKE ':name'", array(":name" => "John Smith"))
       */
      public function query($query, $variables = array(), $log_query = false)
      {
            foreach ((array) $variables as $key => $value) {
                  $value = mysqli_real_escape_string($this->connection, $value);
                  /*
                 *  If String Value is Null by default else append quotes in string
                 */
                  if (strpos($key, ":::") === 0) {
                        $value = "'" . $value . "'";
                  }

                  // preg_quote to escape special chars in $key
                  //                $pattern = '/' . preg_quote($key, '/') . '\b/';
                  //                $query = preg_replace($pattern, $value, $query);
                  $query = str_replace($key, $value, $query);
            }

            $this->last_query = $query;
            //            hprint($this->last_query);
            //            die;
            $this->resultset = mysqli_query($this->connection, $query);

            $this->mysql_error = mysqli_error($this->connection);
            $this->mysql_errorno = mysqli_errno($this->connection);
            if (!$this->resultset) {
                  /* If we had an error while making a query, log it into the database */

                  $backtrace = debug_backtrace();
                  unset($backtrace[0]);

                  //                $type = "ADMIN";
                  //                $error_type = "MYSQL_ERROR";
                  //                $message = $this->escapeString("ERROR: " . mysqli_error($this->connection) . " LAST_QUERY: $this->last_query");
                  //                $url = htmlentities(urlencode("//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"), ENT_QUOTES, 'UTF-8');
                  //                $data = json_encode($backtrace);
                  //                $user_agent_string = $_SERVER['HTTP_USER_AGENT'];
                  //                $user_id = (session::loggedInUid()) ? session::loggedInUid() : NULL;
                  //                $ip = $_SERVER['REMOTE_ADDR'];
                  //                $log_status = 1;
                  //                $error_query = "INSERT INTO system_log (type,error_type,message,url,data,user_agent_string,user_id,ip,log_status) "
                  //                        . " VALUES ('$type', '$error_type','$message','$url','$data','$user_agent_string','$user_id','$ip',$log_status)";
                  //                $res = mysqli_query($this->connection, $error_query);
            }
            if ($log_query) {
                  $message = $this->escapeString(htmlentities($this->last_query));
                  $res = mysqli_query($this->connection, "INSERT INTO system_log (type, message,ip) VALUES ('mysqli_query', '$message','" . $_SERVER['REMOTE_ADDR'] . "')");
            }
            return $this->resultset;
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
            $res = $this->query($sql, $values, true);
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

      public function fetchAssocArray($resultset = null)
      {
            if (!$resultset) {
                  return false;
            }
            $this->current_row = mysqli_fetch_assoc($resultset);
            return $this->current_row;
      }

      /**
       * Method to fetch a row from the resultset in the form of an object
       * 
       * @param $resultset The result set from which to fetch the row
       */
      public function fetchObject($resultset = null)
      {
            ini_set('max_execution_time', 0);
            if (!$resultset) {
                  return false;
            }
            $this->current_row = mysqli_fetch_object($resultset);
            return $this->current_row;
      }

      public function resultNumRows($resultset = null)
      {
            if (!$resultset) {
                  $resultset = $this->resultset;
            }
            return mysqli_num_rows($resultset);
      }

      public function lastInsertId()
      {
            return mysqli_insert_id($this->connection);
      }

      public function escapeString($value)
      {
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                  /* undo any magic quote effects so mysqli_real_escape_string can do the work */
                  $value = stripslashes($value);
            } else {
                  $value = stripslashes($value);
            }
            return mysqli_real_escape_string($this->connection, $value);
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
            /* If we had an error while making a query, log it into the database */
            $backtrace = debug_backtrace();
            unset($backtrace[0]);

            //            $type = "ADMIN";
            //            $error_type = "MYSQL_ERROR";
            //            $message = $this->escapeString("ERROR: " . mysqli_error($this->connection) . " LAST_QUERY: $this->last_query");
            //            $url = htmlspecialchars("//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}", ENT_QUOTES, 'UTF-8');
            //            $data = json_encode($backtrace);
            //            $user_agent_string = $_SERVER['HTTP_USER_AGENT'];
            //            $user_id = (session::loggedInUid()) ? session::loggedInUid() : NULL;
            //            $ip = $_SERVER['REMOTE_ADDR'];
            //            $log_status = 1;
            //            $error_query = "INSERT INTO system_log (type,error_type,message,url,data,user_agent_string,user_id,ip,log_status) "
            //                    . " VALUES ('$type', '$error_type','$message','$url','$data','$user_agent_string','$user_id','$ip',$log_status)";
            //            $res = mysqli_query($this->connection, $error_query);
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

      /**
       * getting mysql error description of last sql operation
       * 
       * @return String Mysql error string description of last mysql operation
       */
      public function getMysqlError()
      {
            return $this->mysql_error;
      }

      public function getMysqlErrorNo()
      {
            return $this->mysql_errorno;
      }
}
