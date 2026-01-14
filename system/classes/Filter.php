<?php

/**
 * Filter model - Generic filter for any entity
 * Can be used for graphs, reports, dashboards, etc.
 *
 * @author Dynamic Graph Creator
 */
class Filter implements DatabaseObject
{
    private $fid;
    private $entity_type;
    private $entity_id;
    private $filter_key;
    private $filter_label;
    private $filter_type;
    private $filter_options;
    private $default_value;
    private $is_required;
    private $sequence;
    private $fsid;
    private $created_ts;
    private $updated_ts;

    const TABLE_NAME = 'filter';

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
        $db = GraphDatabase::getInstance();
        $sql = "SELECT fid FROM " . self::TABLE_NAME . " WHERE fid = '::fid' AND fsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::fid' => intval($id)));
        return $db->numRows($res) > 0;
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
        return !empty($this->entity_type) &&
               !empty($this->entity_id) &&
               !empty($this->filter_key) &&
               !empty($this->filter_label);
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

        $db = GraphDatabase::getInstance();

        $sql = "INSERT INTO " . self::TABLE_NAME . " (
            entity_type,
            entity_id,
            filter_key,
            filter_label,
            filter_type,
            filter_options,
            default_value,
            is_required,
            sequence
        ) VALUES (
            '::entity_type',
            '::entity_id',
            '::filter_key',
            '::filter_label',
            '::filter_type',
            '::filter_options',
            '::default_value',
            '::is_required',
            '::sequence'
        )";

        $args = array(
            '::entity_type' => $this->entity_type,
            '::entity_id' => $this->entity_id,
            '::filter_key' => $this->filter_key,
            '::filter_label' => $this->filter_label,
            '::filter_type' => $this->filter_type ? $this->filter_type : 'text',
            '::filter_options' => $this->filter_options ? $this->filter_options : '',
            '::default_value' => $this->default_value ? $this->default_value : '',
            '::is_required' => $this->is_required ? 1 : 0,
            '::sequence' => $this->sequence ? $this->sequence : 0
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

        $db = GraphDatabase::getInstance();

        $sql = "UPDATE " . self::TABLE_NAME . " SET
            filter_key = '::filter_key',
            filter_label = '::filter_label',
            filter_type = '::filter_type',
            filter_options = '::filter_options',
            default_value = '::default_value',
            is_required = '::is_required',
            sequence = '::sequence'
        WHERE fid = '::fid'";

        $args = array(
            '::filter_key' => $this->filter_key,
            '::filter_label' => $this->filter_label,
            '::filter_type' => $this->filter_type,
            '::filter_options' => $this->filter_options ? $this->filter_options : '',
            '::default_value' => $this->default_value ? $this->default_value : '',
            '::is_required' => $this->is_required ? 1 : 0,
            '::sequence' => $this->sequence ? $this->sequence : 0,
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
        $db = GraphDatabase::getInstance();
        $sql = "UPDATE " . self::TABLE_NAME . " SET fsid = 3 WHERE fid = '::fid'";
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
        $db = GraphDatabase::getInstance();
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE fid = '::fid'";
        return $db->query($sql, array('::fid' => intval($id))) ? true : false;
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

        $db = GraphDatabase::getInstance();
        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE fid = '::fid' AND fsid != 3 LIMIT 1";
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
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'filter_key' => $this->filter_key,
            'filter_label' => $this->filter_label,
            'filter_type' => $this->filter_type,
            'filter_options' => $this->filter_options,
            'default_value' => $this->default_value,
            'is_required' => $this->is_required,
            'sequence' => $this->sequence
        );
    }

    // Getters and Setters

    public function getEntityType() { return $this->entity_type; }
    public function setEntityType($value) { $this->entity_type = $value; }

    public function getEntityId() { return $this->entity_id; }
    public function setEntityId($value) { $this->entity_id = intval($value); }

    public function getFilterKey() { return $this->filter_key; }
    public function setFilterKey($value) { $this->filter_key = $value; }

    public function getFilterLabel() { return $this->filter_label; }
    public function setFilterLabel($value) { $this->filter_label = $value; }

    public function getFilterType() { return $this->filter_type; }
    public function setFilterType($value) { $this->filter_type = $value; }

    public function getFilterOptions() { return $this->filter_options; }
    public function setFilterOptions($value) { $this->filter_options = $value; }

    public function getDefaultValue() { return $this->default_value; }
    public function setDefaultValue($value) { $this->default_value = $value; }

    public function getIsRequired() { return $this->is_required; }
    public function setIsRequired($value) { $this->is_required = $value ? 1 : 0; }

    public function getSequence() { return $this->sequence; }
    public function setSequence($value) { $this->sequence = intval($value); }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }
}
