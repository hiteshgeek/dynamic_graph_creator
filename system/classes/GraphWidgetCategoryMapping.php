<?php

/**
 * GraphWidgetCategoryMapping model - Maps graphs to widget categories
 *
 * @author Dynamic Graph Creator
 */
class GraphWidgetCategoryMapping implements DatabaseObject
{
    private $gwcmid;
    private $gid;
    private $wcid;
    private $created_ts;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->gwcmid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT gwcmid FROM " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " WHERE gwcmid = '::gwcmid' LIMIT 1";
        $res = $db->query($sql, array('::gwcmid' => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    public function getId() { return $this->gwcmid; }

    public function hasMandatoryData()
    {
        return !empty($this->gid) && !empty($this->wcid);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "INSERT INTO " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " (
            gid, wcid
        ) VALUES (
            '::gid', '::wcid'
        )";

        $args = array(
            '::gid' => intval($this->gid),
            '::wcid' => intval($this->wcid)
        );

        if ($db->query($sql, $args)) {
            $this->gwcmid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->gwcmid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " SET
            gid = '::gid',
            wcid = '::wcid'
        WHERE gwcmid = '::gwcmid'";

        $args = array(
            '::gid' => intval($this->gid),
            '::wcid' => intval($this->wcid),
            '::gwcmid' => $this->gwcmid
        );

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " WHERE gwcmid = '::gwcmid'";
        $result = $db->query($sql, array('::gwcmid' => intval($id)));
        return $result ? true : false;
    }

    public function load()
    {
        if (!$this->gwcmid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " WHERE gwcmid = '::gwcmid' LIMIT 1";
        $res = $db->query($sql, array('::gwcmid' => $this->gwcmid));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->gwcmid = null;
            return false;
        }

        return $this->parse($db->fetchObject($res));
    }

    public function parse($obj)
    {
        if (!$obj) return false;
        foreach ($obj as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return true;
    }

    public function __toString() { return $this->gwcmid ? "Mapping {$this->gwcmid}" : ''; }

    public function toArray()
    {
        return array(
            'gwcmid' => $this->gwcmid,
            'gid' => $this->gid,
            'wcid' => $this->wcid,
            'created_ts' => $this->created_ts
        );
    }

    // Getters and Setters
    public function getGid() { return $this->gid; }
    public function setGid($value) { $this->gid = intval($value); }

    public function getWcid() { return $this->wcid; }
    public function setWcid($value) { $this->wcid = intval($value); }

    public function getCreatedTs() { return $this->created_ts; }
}
