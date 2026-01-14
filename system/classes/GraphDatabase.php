<?php

/**
 * Database class for Graph Creator
 * Follows the same pattern as SQLiDatabase from framework
 *
 * @author Dynamic Graph Creator
 */
class GraphDatabase
{
    private $connection;
    public $resultset;
    public $last_query;
    public $mysql_error;
    public $mysql_errorno;

    private static $instance = null;

    /**
     * Get singleton instance
     * @return GraphDatabase
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new GraphDatabase();
        }
        return self::$instance;
    }

    /**
     * Constructor - connects to database
     */
    public function __construct()
    {
        $this->connect();
    }

    /**
     * Connect to the database
     * @return bool
     */
    public function connect()
    {
        $this->connection = mysqli_connect(
            GraphConfig::getDbHost(),
            GraphConfig::getDbUser(),
            GraphConfig::getDbPass()
        );

        if ($this->connection) {
            $dbSelect = mysqli_select_db($this->connection, GraphConfig::getDbName());
            if ($dbSelect) {
                mysqli_set_charset($this->connection, 'utf8');
                return true;
            }
        }
        return false;
    }

    /**
     * Execute a query with parameter substitution
     *
     * @param string $query SQL query with ::placeholders
     * @param array $variables Array of placeholder => value pairs
     * @return mysqli_result|bool
     */
    public function query($query, $variables = array())
    {
        foreach ((array) $variables as $key => $value) {
            $value = mysqli_real_escape_string($this->connection, $value);

            // Handle ::: prefix for forced quoting
            if (strpos($key, ':::') === 0) {
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
     * Fetch a row as object
     *
     * @param mysqli_result $resultset
     * @return object|null
     */
    public function fetchObject($resultset = null)
    {
        if (!$resultset) {
            return null;
        }
        return mysqli_fetch_object($resultset);
    }

    /**
     * Fetch a row as associative array
     *
     * @param mysqli_result $resultset
     * @return array|null
     */
    public function fetchAssoc($resultset = null)
    {
        if (!$resultset) {
            return null;
        }
        return mysqli_fetch_assoc($resultset);
    }

    /**
     * Fetch all rows as array of objects
     *
     * @param mysqli_result $resultset
     * @return array
     */
    public function fetchAll($resultset = null)
    {
        $rows = array();
        if (!$resultset) {
            return $rows;
        }
        while ($row = mysqli_fetch_object($resultset)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Fetch all rows as array of associative arrays
     *
     * @param mysqli_result $resultset
     * @return array
     */
    public function fetchAllAssoc($resultset = null)
    {
        $rows = array();
        if (!$resultset) {
            return $rows;
        }
        while ($row = mysqli_fetch_assoc($resultset)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Get number of rows in result
     *
     * @param mysqli_result $resultset
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
     * Get last inserted ID
     * @return int
     */
    public function lastInsertId()
    {
        return mysqli_insert_id($this->connection);
    }

    /**
     * Escape a string for safe SQL use
     *
     * @param string $value
     * @return string
     */
    public function escapeString($value)
    {
        $value = stripslashes($value);
        return mysqli_real_escape_string($this->connection, $value);
    }

    /**
     * Get affected rows from last query
     * @return int
     */
    public function affectedRows()
    {
        return mysqli_affected_rows($this->connection);
    }

    /**
     * Start a transaction
     */
    public function beginTransaction()
    {
        mysqli_autocommit($this->connection, false);
    }

    /**
     * Commit a transaction
     */
    public function commit()
    {
        mysqli_commit($this->connection);
        mysqli_autocommit($this->connection, true);
    }

    /**
     * Rollback a transaction
     */
    public function rollback()
    {
        mysqli_rollback($this->connection);
        mysqli_autocommit($this->connection, true);
    }

    /**
     * Get last MySQL error
     * @return string
     */
    public function getError()
    {
        return $this->mysql_error;
    }

    /**
     * Get last MySQL error number
     * @return int
     */
    public function getErrorNo()
    {
        return $this->mysql_errorno;
    }

    /**
     * Get last executed query
     * @return string
     */
    public function getLastQuery()
    {
        return $this->last_query;
    }
}
