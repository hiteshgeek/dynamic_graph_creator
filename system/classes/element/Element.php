<?php

/**
 * Element - Abstract base class for all dashboard elements
 * Provides common functionality for Graph, Table, List, Counter types
 *
 * @author Dynamic Graph Creator
 */
abstract class Element implements DatabaseObject
{
    // Common properties shared by all element types
    protected $id;
    protected $name;
    protected $description;
    protected $config;
    protected $query;
    protected $data_mapping;
    protected $placeholder_settings;
    protected $snapshot;
    protected $status_id;
    protected $created_ts;
    protected $updated_ts;
    protected $created_uid;
    protected $updated_uid;

    /**
     * Abstract methods that each element type must implement
     */

    /**
     * Get the database table name for this element type
     * @return string Table name constant
     */
    abstract public static function getTableName();

    /**
     * Get the primary key column name
     * @return string Primary key column name (e.g., 'gid', 'tid')
     */
    abstract public static function getPrimaryKeyName();

    /**
     * Get the status column name
     * @return string Status column name (e.g., 'gsid', 'tsid')
     */
    abstract public static function getStatusColumnName();

    /**
     * Get the element type identifier
     * @return string Element type slug (e.g., 'graph', 'table', 'list', 'counter')
     */
    abstract public static function getElementType();

    /**
     * Format query results into element-specific data structure
     * @param array $rows Query result rows
     * @param array $mapping Data mapping configuration
     * @return array Formatted data
     */
    abstract protected function formatData($rows, $mapping);

    /**
     * Get empty data structure for this element type
     * @param string|null $error Error message to include
     * @return array Empty data structure
     */
    abstract protected function getEmptyData($error = null);

    /**
     * Get element-specific insert columns and values
     * @return array ['columns' => [], 'values' => [], 'args' => []]
     */
    abstract protected function getTypeSpecificInsertData();

    /**
     * Get element-specific update columns and values
     * @return array ['set' => '', 'args' => []]
     */
    abstract protected function getTypeSpecificUpdateData();

    /**
     * Check if element has all mandatory data for saving
     * Subclasses should call parent and add type-specific checks
     * @return bool
     */
    public function hasMandatoryData()
    {
        return !empty($this->name) && !empty($this->query);
    }

    /**
     * Constructor
     * @param int|null $id Element ID to load
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->id = intval($id);
            $this->load();
        }
    }

    /**
     * Check if element exists in database
     * @param int $id Element ID
     * @return bool
     */
    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $pk = static::getPrimaryKeyName();
        $table = static::getTableName();
        $statusCol = static::getStatusColumnName();

        $sql = "SELECT {$pk} FROM {$table} WHERE {$pk} = '::{$pk}' AND {$statusCol} != 3 LIMIT 1";
        $res = $db->query($sql, array("::{$pk}" => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    /**
     * Get element ID
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Insert element into database
     * @return bool Success
     */
    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();
        $table = static::getTableName();

        // Base columns
        $columns = array('name', 'description', 'config', 'query', 'data_mapping', 'placeholder_settings', 'created_uid');
        $values = array("'::name'", "'::description'", "'::config'", "'::query'", "'::data_mapping'", "'::placeholder_settings'", "'::created_uid'");
        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::config' => $this->config ? $this->config : '{}',
            '::query' => $this->query,
            '::data_mapping' => $this->data_mapping ? $this->data_mapping : '{}',
            '::placeholder_settings' => $this->placeholder_settings ? $this->placeholder_settings : '{}',
            '::created_uid' => $this->created_uid ? $this->created_uid : 0
        );

        // Add type-specific data
        $typeData = $this->getTypeSpecificInsertData();
        $columns = array_merge($columns, $typeData['columns']);
        $values = array_merge($values, $typeData['values']);
        $args = array_merge($args, $typeData['args']);

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";

        if ($db->query($sql, $args)) {
            $this->id = $db->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Update element in database
     * @return bool Success
     */
    public function update()
    {
        if (!$this->id) return false;

        $db = Rapidkart::getInstance()->getDB();
        $table = static::getTableName();
        $pk = static::getPrimaryKeyName();

        // Base update SET clause
        $setClause = "name = '::name',
            description = '::description',
            config = '::config',
            query = '::query',
            data_mapping = '::data_mapping',
            placeholder_settings = '::placeholder_settings',
            updated_uid = '::updated_uid'";

        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::config' => $this->config ? $this->config : '{}',
            '::query' => $this->query,
            '::data_mapping' => $this->data_mapping ? $this->data_mapping : '{}',
            '::placeholder_settings' => $this->placeholder_settings ? $this->placeholder_settings : '{}',
            '::updated_uid' => $this->updated_uid ? $this->updated_uid : 0,
            "::{$pk}" => $this->id
        );

        // Add type-specific data
        $typeData = $this->getTypeSpecificUpdateData();
        if (!empty($typeData['set'])) {
            $setClause .= ", " . $typeData['set'];
        }
        $args = array_merge($args, $typeData['args']);

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$pk} = '::{$pk}'";

        return $db->query($sql, $args) ? true : false;
    }

    /**
     * Soft delete element
     * @param int $id Element ID
     * @return bool Success
     */
    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $table = static::getTableName();
        $pk = static::getPrimaryKeyName();
        $statusCol = static::getStatusColumnName();

        $sql = "UPDATE {$table} SET {$statusCol} = 3 WHERE {$pk} = '::{$pk}'";
        $result = $db->query($sql, array("::{$pk}" => intval($id)));

        // Note: Filters are linked dynamically via query placeholders, not stored separately
        // No separate cleanup needed

        return $result ? true : false;
    }

    /**
     * Update only the snapshot field
     * @return bool Success
     */
    public function updateSnapshot()
    {
        if (!$this->id) return false;

        $db = Rapidkart::getInstance()->getDB();
        $table = static::getTableName();
        $pk = static::getPrimaryKeyName();

        $sql = "UPDATE {$table} SET
            snapshot = '::snapshot',
            updated_uid = '::updated_uid'
        WHERE {$pk} = '::{$pk}'";

        $args = array(
            '::snapshot' => $this->snapshot ? $this->snapshot : '',
            '::updated_uid' => $this->updated_uid ? $this->updated_uid : 0,
            "::{$pk}" => $this->id
        );

        return $db->query($sql, $args) ? true : false;
    }

    /**
     * Load element from database
     * @return bool Success
     */
    public function load()
    {
        if (!$this->id) return false;

        $db = Rapidkart::getInstance()->getDB();
        $table = static::getTableName();
        $pk = static::getPrimaryKeyName();
        $statusCol = static::getStatusColumnName();

        $sql = "SELECT * FROM {$table} WHERE {$pk} = '::{$pk}' AND {$statusCol} != 3 LIMIT 1";
        $res = $db->query($sql, array("::{$pk}" => $this->id));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->id = null;
            return false;
        }

        return $this->parse($db->fetchObject($res));
    }

    /**
     * Parse database row into object properties
     * @param object $obj Database row object
     * @return bool Success
     */
    public function parse($obj)
    {
        if (!$obj) return false;

        // Map primary key to id
        $pk = static::getPrimaryKeyName();
        if (isset($obj->$pk)) {
            $this->id = $obj->$pk;
        }

        // Map status column to status_id
        $statusCol = static::getStatusColumnName();
        if (isset($obj->$statusCol)) {
            $this->status_id = $obj->$statusCol;
        }

        // Map all other properties
        foreach ($obj as $key => $value) {
            if (property_exists($this, $key) && $key !== $pk && $key !== $statusCol) {
                $this->$key = $value;
            }
        }

        return true;
    }

    /**
     * String representation
     * @return string
     */
    public function __toString()
    {
        return $this->name ? $this->name : '';
    }

    /**
     * Convert to array (for JSON serialization)
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'element_type' => static::getElementType(),
            'name' => $this->name,
            'description' => $this->description,
            'config' => $this->config,
            'query' => $this->query,
            'data_mapping' => $this->data_mapping,
            'placeholder_settings' => $this->placeholder_settings,
            'snapshot' => $this->snapshot,
            'created_ts' => $this->created_ts,
            'updated_ts' => $this->updated_ts
        );
    }

    /**
     * Execute query and return formatted data
     * @param array $filter_values Filter values to apply
     * @return array Formatted data or error
     */
    public function execute($filter_values = array())
    {
        $db = Rapidkart::getInstance()->getDB();

        // Use DataFilterSet with element type and ID
        $filterSet = new DataFilterSet(static::getElementType(), $this->id);
        $filterSet->loadFilters();

        // Get placeholder settings
        $placeholderSettings = $this->placeholder_settings ? json_decode($this->placeholder_settings, true) : array();
        if (!is_array($placeholderSettings)) {
            $placeholderSettings = array();
        }

        // Validate required placeholders
        $missingRequired = DataFilterManager::validateRequiredPlaceholders($this->query, $filter_values, $placeholderSettings);
        if (!empty($missingRequired)) {
            $filterNames = array_map(function($p) { return ltrim($p, ':'); }, $missingRequired);
            return $this->getEmptyData('Required filter(s) missing value: ' . implode(', ', $filterNames));
        }

        // Apply filters and execute query
        $query = $filterSet->applyToQuery($this->query, $filter_values, $placeholderSettings);
        $res = $db->query($query);

        if (!$res) {
            return array('error' => $db->getMysqlError());
        }

        // Fetch rows
        $rows = array();
        while ($row = $db->fetchAssocArray($res)) {
            $rows[] = $row;
        }

        // Format data using type-specific method
        $mapping = json_decode($this->data_mapping, true);
        return $this->formatData($rows, $mapping);
    }

    // Common Getters and Setters

    public function getName() { return $this->name; }
    public function setName($value) { $this->name = $value; }

    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; }

    public function getConfig() { return $this->config; }
    public function setConfig($value) { $this->config = is_string($value) ? $value : json_encode($value); }

    public function getQuery() { return $this->query; }
    public function setQuery($value) { $this->query = $value; }

    public function getDataMapping() { return $this->data_mapping; }
    public function setDataMapping($value) { $this->data_mapping = is_string($value) ? $value : json_encode($value); }

    public function getPlaceholderSettings() { return $this->placeholder_settings; }
    public function setPlaceholderSettings($value) { $this->placeholder_settings = is_string($value) ? $value : json_encode($value); }

    public function getSnapshot() { return $this->snapshot; }
    public function setSnapshot($value) { $this->snapshot = $value; }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }

    public function setCreatedUid($value) { $this->created_uid = intval($value); }
    public function setUpdatedUid($value) { $this->updated_uid = intval($value); }
}
