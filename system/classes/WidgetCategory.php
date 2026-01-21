<?php

/**
 * WidgetCategory model - Categories for dashboard widgets (graphs, etc.)
 *
 * @author Dynamic Graph Creator
 */
class WidgetCategory implements DatabaseObject
{
    private $wcid;
    private $name;
    private $description;
    private $icon;
    private $color;
    private $display_order;
    private $wcsid;
    private $created_ts;
    private $updated_ts;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->wcid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT wcid FROM " . SystemTables::DB_TBL_WIDGET_CATEGORY . " WHERE wcid = '::wcid' AND wcsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::wcid' => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    public function getId() { return $this->wcid; }

    public function hasMandatoryData()
    {
        return !empty($this->name);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "INSERT INTO " . SystemTables::DB_TBL_WIDGET_CATEGORY . " (
            name, description, icon, color, display_order
        ) VALUES (
            '::name', '::description', '::icon', '::color', '::display_order'
        )";

        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::icon' => $this->icon ? $this->icon : '',
            '::color' => $this->color ? $this->color : '',
            '::display_order' => $this->display_order ? intval($this->display_order) : 0
        );

        if ($db->query($sql, $args)) {
            $this->wcid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->wcid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_WIDGET_CATEGORY . " SET
            name = '::name',
            description = '::description',
            icon = '::icon',
            color = '::color',
            display_order = '::display_order'
        WHERE wcid = '::wcid'";

        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::icon' => $this->icon ? $this->icon : '',
            '::color' => $this->color ? $this->color : '',
            '::display_order' => $this->display_order ? intval($this->display_order) : 0,
            '::wcid' => $this->wcid
        );

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_WIDGET_CATEGORY . " SET wcsid = 3 WHERE wcid = '::wcid'";
        $result = $db->query($sql, array('::wcid' => intval($id)));
        return $result ? true : false;
    }

    public function load()
    {
        if (!$this->wcid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_WIDGET_CATEGORY . " WHERE wcid = '::wcid' AND wcsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::wcid' => $this->wcid));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->wcid = null;
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

    public function __toString() { return $this->name ? $this->name : ''; }

    public function toArray()
    {
        return array(
            'wcid' => $this->wcid,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'display_order' => $this->display_order,
            'created_ts' => $this->created_ts,
            'updated_ts' => $this->updated_ts
        );
    }

    // Getters and Setters
    public function getName() { return $this->name; }
    public function setName($value) { $this->name = $value; }

    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; }

    public function getIcon() { return $this->icon; }
    public function setIcon($value) { $this->icon = $value; }

    public function getColor() { return $this->color; }
    public function setColor($value) { $this->color = $value; }

    public function getDisplayOrder() { return $this->display_order; }
    public function setDisplayOrder($value) { $this->display_order = intval($value); }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }
}
