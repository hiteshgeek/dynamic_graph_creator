<?php

/**
 * WidgetTableCategoryMapping model - Maps tables to widget categories
 *
 * @author Dynamic Graph Creator
 */
class WidgetTableCategoryMapping implements DatabaseObject
{
    private $twcmid;
    private $tid;
    private $wcid;
    private $created_ts;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->twcmid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT twcmid FROM " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " WHERE twcmid = '::twcmid' LIMIT 1";
        $res = $db->query($sql, array('::twcmid' => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    public function getId() { return $this->twcmid; }

    public function hasMandatoryData()
    {
        return !empty($this->tid) && !empty($this->wcid);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "INSERT INTO " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " (
            tid, wcid
        ) VALUES (
            '::tid', '::wcid'
        )";

        $args = array(
            '::tid' => intval($this->tid),
            '::wcid' => intval($this->wcid)
        );

        if ($db->query($sql, $args)) {
            $this->twcmid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->twcmid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " SET
            tid = '::tid',
            wcid = '::wcid'
        WHERE twcmid = '::twcmid'";

        $args = array(
            '::tid' => intval($this->tid),
            '::wcid' => intval($this->wcid),
            '::twcmid' => $this->twcmid
        );

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " WHERE twcmid = '::twcmid'";
        $result = $db->query($sql, array('::twcmid' => intval($id)));
        return $result ? true : false;
    }

    public function load()
    {
        if (!$this->twcmid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " WHERE twcmid = '::twcmid' LIMIT 1";
        $res = $db->query($sql, array('::twcmid' => $this->twcmid));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->twcmid = null;
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

    public function __toString() { return $this->twcmid ? "Mapping {$this->twcmid}" : ''; }

    public function toArray()
    {
        return array(
            'twcmid' => $this->twcmid,
            'tid' => $this->tid,
            'wcid' => $this->wcid,
            'created_ts' => $this->created_ts
        );
    }

    // Getters and Setters
    public function getTid() { return $this->tid; }
    public function setTid($value) { $this->tid = intval($value); }

    public function getWcid() { return $this->wcid; }
    public function setWcid($value) { $this->wcid = intval($value); }

    public function getCreatedTs() { return $this->created_ts; }
}
