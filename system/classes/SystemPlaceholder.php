<?php

/**
 * SystemPlaceholder model - System-level placeholders resolved at runtime
 * These placeholders can be used in filter queries to access system values
 * like logged-in user ID, company ID, etc.
 *
 * @author Dynamic Graph Creator
 */
class SystemPlaceholder implements DatabaseObject
{
    private $spid;
    private $placeholder_key;
    private $placeholder_label;
    private $description;
    private $resolver_method;
    private $spsid;
    private $created_ts;

    /**
     * Constructor
     *
     * @param int $id SystemPlaceholder ID to load
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->spid = intval($id);
            $this->load();
        }
    }

    /**
     * Check if placeholder exists
     *
     * @param int $id
     * @return bool
     */
    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT spid FROM " . SystemTables::DB_TBL_SYSTEM_PLACEHOLDER . " WHERE spid = '::spid' AND spsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::spid' => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    /**
     * Get placeholder ID
     * @return int
     */
    public function getId()
    {
        return $this->spid;
    }

    /**
     * Check mandatory data
     * @return bool
     */
    public function hasMandatoryData()
    {
        return !empty($this->placeholder_key) && !empty($this->placeholder_label) && !empty($this->resolver_method);
    }

    /**
     * Insert new placeholder
     * @return bool
     */
    public function insert()
    {
        if (!$this->hasMandatoryData()) {
            return false;
        }

        $db = Rapidkart::getInstance()->getDB();

        $sql = "INSERT INTO " . SystemTables::DB_TBL_SYSTEM_PLACEHOLDER . " (
            placeholder_key,
            placeholder_label,
            description,
            resolver_method
        ) VALUES (
            '::placeholder_key',
            '::placeholder_label',
            '::description',
            '::resolver_method'
        )";

        $args = array(
            '::placeholder_key' => $this->placeholder_key,
            '::placeholder_label' => $this->placeholder_label,
            '::description' => $this->description ? $this->description : '',
            '::resolver_method' => $this->resolver_method
        );

        $res = $db->query($sql, $args);
        if ($res) {
            $this->spid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Update existing placeholder
     * @return bool
     */
    public function update()
    {
        if (!$this->spid) {
            return false;
        }

        $db = Rapidkart::getInstance()->getDB();

        $sql = "UPDATE " . SystemTables::DB_TBL_SYSTEM_PLACEHOLDER . " SET
            placeholder_key = '::placeholder_key',
            placeholder_label = '::placeholder_label',
            description = '::description',
            resolver_method = '::resolver_method'
        WHERE spid = '::spid'";

        $args = array(
            '::placeholder_key' => $this->placeholder_key,
            '::placeholder_label' => $this->placeholder_label,
            '::description' => $this->description ? $this->description : '',
            '::resolver_method' => $this->resolver_method,
            '::spid' => $this->spid
        );

        return $db->query($sql, $args) ? true : false;
    }

    /**
     * Soft delete placeholder
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_SYSTEM_PLACEHOLDER . " SET spsid = 3 WHERE spid = '::spid'";
        return $db->query($sql, array('::spid' => intval($id))) ? true : false;
    }

    /**
     * Load placeholder from database
     * @return bool
     */
    public function load()
    {
        if (!$this->spid) {
            return false;
        }

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PLACEHOLDER . " WHERE spid = '::spid' AND spsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::spid' => $this->spid));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->spid = null;
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
        return $this->placeholder_label ? $this->placeholder_label : '';
    }

    /**
     * Convert to array
     * @return array
     */
    public function toArray()
    {
        return array(
            'spid' => $this->spid,
            'placeholder_key' => $this->placeholder_key,
            'placeholder_label' => $this->placeholder_label,
            'description' => $this->description,
            'resolver_method' => $this->resolver_method
        );
    }

    // Getters and Setters

    public function getPlaceholderKey() { return $this->placeholder_key; }
    public function setPlaceholderKey($value) { $this->placeholder_key = $value; }

    public function getPlaceholderLabel() { return $this->placeholder_label; }
    public function setPlaceholderLabel($value) { $this->placeholder_label = $value; }

    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; }

    public function getResolverMethod() { return $this->resolver_method; }
    public function setResolverMethod($value) { $this->resolver_method = $value; }

    public function getCreatedTs() { return $this->created_ts; }
}
