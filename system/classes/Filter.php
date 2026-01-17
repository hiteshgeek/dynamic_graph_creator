<?php

/**
 * Filter model - Independent/Reusable filter definition
 * Filters define how to get data (static options or SQL query)
 * and can be linked to multiple graphs
 *
 * @author Dynamic Graph Creator
 */
class Filter implements DatabaseObject
{
    private $fid;
    private $filter_key;
    private $filter_label;
    private $filter_type;
    private $data_source;
    private $data_query;
    private $static_options;
    private $filter_config;
    private $default_value;
    private $is_required;
    private $fsid;
    private $created_ts;
    private $updated_ts;

    /**
     * Constructor
     *
     * @param int $id Filter ID to load
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->fid = intval($id);
            $this->load();
        }
    }

    /**
     * Check if filter exists
     *
     * @param int $id
     * @return bool
     */
    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT fid FROM " . SystemTables::DB_TBL_FILTER . " WHERE fid = '::fid' AND fsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::fid' => intval($id)));
        return $db->numRows($res) > 0;
    }

    /**
     * Get all active filters
     *
     * @return array
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_FILTER . " WHERE fsid != 3 ORDER BY filter_label";
        $res = $db->query($sql);

        $filters = array();
        while ($row = $db->fetchAssoc($res)) {
            $filters[] = $row;
        }
        return $filters;
    }

    /**
     * Get filter ID
     * @return int
     */
    public function getId()
    {
        return $this->fid;
    }

    /**
     * Check mandatory data
     * @return bool
     */
    public function hasMandatoryData()
    {
        return !empty($this->filter_key) && !empty($this->filter_label);
    }

    /**
     * Insert new filter
     * @return bool
     */
    public function insert()
    {
        if (!$this->hasMandatoryData()) {
            return false;
        }

        $db = Rapidkart::getInstance()->getDB();

        $sql = "INSERT INTO " . SystemTables::DB_TBL_FILTER . " (
            filter_key,
            filter_label,
            filter_type,
            data_source,
            data_query,
            static_options,
            filter_config,
            default_value,
            is_required
        ) VALUES (
            '::filter_key',
            '::filter_label',
            '::filter_type',
            '::data_source',
            '::data_query',
            '::static_options',
            '::filter_config',
            '::default_value',
            '::is_required'
        )";

        $args = array(
            '::filter_key' => $this->filter_key,
            '::filter_label' => $this->filter_label,
            '::filter_type' => $this->filter_type ? $this->filter_type : 'text',
            '::data_source' => $this->data_source ? $this->data_source : 'static',
            '::data_query' => $this->data_query ? $this->data_query : '',
            '::static_options' => $this->static_options ? $this->static_options : '',
            '::filter_config' => $this->filter_config ? $this->filter_config : '',
            '::default_value' => $this->default_value ? $this->default_value : '',
            '::is_required' => $this->is_required ? 1 : 0
        );

        $res = $db->query($sql, $args);
        if ($res) {
            $this->fid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Update existing filter
     * @return bool
     */
    public function update()
    {
        if (!$this->fid) {
            return false;
        }

        $db = Rapidkart::getInstance()->getDB();

        $sql = "UPDATE " . SystemTables::DB_TBL_FILTER . " SET
            filter_key = '::filter_key',
            filter_label = '::filter_label',
            filter_type = '::filter_type',
            data_source = '::data_source',
            data_query = '::data_query',
            static_options = '::static_options',
            filter_config = '::filter_config',
            default_value = '::default_value',
            is_required = '::is_required'
        WHERE fid = '::fid'";

        $args = array(
            '::filter_key' => $this->filter_key,
            '::filter_label' => $this->filter_label,
            '::filter_type' => $this->filter_type,
            '::data_source' => $this->data_source ? $this->data_source : 'static',
            '::data_query' => $this->data_query ? $this->data_query : '',
            '::static_options' => $this->static_options ? $this->static_options : '',
            '::filter_config' => $this->filter_config ? $this->filter_config : '',
            '::default_value' => $this->default_value ? $this->default_value : '',
            '::is_required' => $this->is_required ? 1 : 0,
            '::fid' => $this->fid
        );

        return $db->query($sql, $args) ? true : false;
    }

    /**
     * Soft delete filter
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_FILTER . " SET fsid = 3 WHERE fid = '::fid'";
        return $db->query($sql, array('::fid' => intval($id))) ? true : false;
    }

    /**
     * Hard delete filter
     *
     * @param int $id
     * @return bool
     */
    public static function hardDelete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_FILTER . " WHERE fid = '::fid'";
        return $db->query($sql, array('::fid' => intval($id))) ? true : false;
    }

    /**
     * Get filters by their keys (for matching placeholders in queries)
     *
     * @param array $keys Array of filter keys like [':year', ':date_from']
     * @return array Array of Filter objects indexed by filter_key
     */
    public static function getByKeys($keys)
    {
        if (empty($keys)) {
            return array();
        }

        $db = Rapidkart::getInstance()->getDB();

        // Build placeholders for IN clause
        $placeholders = array();
        $args = array();
        foreach ($keys as $i => $key) {
            $placeholders[] = "'::key{$i}'";
            $args["::key{$i}"] = $key;
        }

        $sql = "SELECT * FROM " . SystemTables::DB_TBL_FILTER . "
                WHERE filter_key IN (" . implode(',', $placeholders) . ") AND fsid != 3";
        $res = $db->query($sql, $args);

        $filters = array();
        while ($row = $db->fetchAssoc($res)) {
            $filter = new Filter();
            $filter->parse((object)$row);
            $filters[$row['filter_key']] = $filter;
        }
        return $filters;
    }

    /**
     * Extract placeholders from a SQL query
     * Looks for :word patterns that match filter syntax
     *
     * @param string $query SQL query string
     * @return array Array of placeholder keys found
     */
    public static function extractPlaceholders($query)
    {
        $placeholders = array();
        // Match ::key format (double colon)
        if (preg_match_all('/::([a-zA-Z_][a-zA-Z0-9_]*)/', $query, $matches)) {
            // Return with :: prefix to match how filter keys are stored
            foreach ($matches[0] as $match) {
                if (!in_array($match, $placeholders)) {
                    $placeholders[] = $match;
                }
            }
        }
        return $placeholders;
    }

    /**
     * Load filter from database
     * @return bool
     */
    public function load()
    {
        if (!$this->fid) {
            return false;
        }

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_FILTER . " WHERE fid = '::fid' AND fsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::fid' => $this->fid));

        if (!$res || $db->numRows($res) < 1) {
            return false;
        }

        $row = $db->fetchObject($res);
        return $this->parse($row);
    }

    /**
     * Parse database row into object
     *
     * @param object $obj
     * @return bool
     */
    public function parse($obj)
    {
        if (!$obj) {
            return false;
        }

        foreach ($obj as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return true;
    }

    /**
     * Get filter options (executes query if data_source is 'query')
     *
     * @return array
     */
    public function getOptions()
    {
        if ($this->data_source === 'query' && !empty($this->data_query)) {
            // Execute query to get options
            $db = Rapidkart::getInstance()->getDB();
            $res = $db->query($this->data_query);
            $options = array();
            if ($res) {
                while ($row = $db->fetchAssoc($res)) {
                    $options[] = array(
                        'value' => isset($row['value']) ? $row['value'] : '',
                        'label' => isset($row['label']) ? $row['label'] : (isset($row['value']) ? $row['value'] : '')
                    );
                }
            }
            return $options;
        } else {
            // Return static options
            if (!empty($this->static_options)) {
                $options = json_decode($this->static_options, true);
                return is_array($options) ? $options : array();
            }
            return array();
        }
    }

    /**
     * Convert to string
     * @return string
     */
    public function __toString()
    {
        return $this->filter_label ? $this->filter_label : '';
    }

    /**
     * Convert to array
     * @return array
     */
    public function toArray()
    {
        return array(
            'fid' => $this->fid,
            'filter_key' => $this->filter_key,
            'filter_label' => $this->filter_label,
            'filter_type' => $this->filter_type,
            'data_source' => $this->data_source,
            'data_query' => $this->data_query,
            'static_options' => $this->static_options,
            'filter_config' => $this->filter_config,
            'default_value' => $this->default_value,
            'is_required' => $this->is_required,
            'options' => $this->getOptions()
        );
    }

    // Getters and Setters

    public function getFilterKey() { return $this->filter_key; }
    public function setFilterKey($value) { $this->filter_key = $value; }

    public function getFilterLabel() { return $this->filter_label; }
    public function setFilterLabel($value) { $this->filter_label = $value; }

    public function getFilterType() { return $this->filter_type; }
    public function setFilterType($value) { $this->filter_type = $value; }

    public function getDataSource() { return $this->data_source; }
    public function setDataSource($value) { $this->data_source = $value; }

    public function getDataQuery() { return $this->data_query; }
    public function setDataQuery($value) { $this->data_query = $value; }

    public function getStaticOptions() { return $this->static_options; }
    public function setStaticOptions($value) { $this->static_options = $value; }

    public function getFilterConfig() { return $this->filter_config; }
    public function setFilterConfig($value) { $this->filter_config = $value; }

    public function getDefaultValue() { return $this->default_value; }
    public function setDefaultValue($value) { $this->default_value = $value; }

    public function getIsRequired() { return $this->is_required; }
    public function setIsRequired($value) { $this->is_required = $value ? 1 : 0; }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }
}
