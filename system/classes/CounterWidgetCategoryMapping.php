<?php

/**
 * CounterWidgetCategoryMapping model - Maps counters to widget categories
 *
 * @author Dynamic Graph Creator
 */
class CounterWidgetCategoryMapping implements DatabaseObject
{
    private $cwcmid;
    private $cid;
    private $wcid;
    private $created_ts;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->cwcmid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT cwcmid FROM " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " WHERE cwcmid = '::cwcmid' LIMIT 1";
        $res = $db->query($sql, array('::cwcmid' => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    public function getId() { return $this->cwcmid; }

    public function hasMandatoryData()
    {
        return !empty($this->cid) && !empty($this->wcid);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "INSERT INTO " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " (
            cid, wcid
        ) VALUES (
            '::cid', '::wcid'
        )";

        $args = array(
            '::cid' => intval($this->cid),
            '::wcid' => intval($this->wcid)
        );

        if ($db->query($sql, $args)) {
            $this->cwcmid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->cwcmid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " SET
            cid = '::cid',
            wcid = '::wcid'
        WHERE cwcmid = '::cwcmid'";

        $args = array(
            '::cid' => intval($this->cid),
            '::wcid' => intval($this->wcid),
            '::cwcmid' => $this->cwcmid
        );

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " WHERE cwcmid = '::cwcmid'";
        $result = $db->query($sql, array('::cwcmid' => intval($id)));
        return $result ? true : false;
    }

    public function load()
    {
        if (!$this->cwcmid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " WHERE cwcmid = '::cwcmid' LIMIT 1";
        $res = $db->query($sql, array('::cwcmid' => $this->cwcmid));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->cwcmid = null;
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

    public function __toString() { return $this->cwcmid ? "Mapping {$this->cwcmid}" : ''; }

    public function toArray()
    {
        return array(
            'cwcmid' => $this->cwcmid,
            'cid' => $this->cid,
            'wcid' => $this->wcid,
            'created_ts' => $this->created_ts
        );
    }

    // Getters and Setters
    public function getCid() { return $this->cid; }
    public function setCid($value) { $this->cid = intval($value); }

    public function getWcid() { return $this->wcid; }
    public function setWcid($value) { $this->wcid = intval($value); }

    public function getCreatedTs() { return $this->created_ts; }
}
