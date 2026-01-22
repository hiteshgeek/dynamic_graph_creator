<?php

/**
 * WidgetType model - Types of dashboard widgets (graph, link, table, list, counter)
 *
 * @author Dynamic Graph Creator
 */
class WidgetType implements DatabaseObject
{
    private $wtid;
    private $slug;
    private $name;
    private $description;
    private $icon;
    private $display_order;
    private $wtsid;
    private $created_ts;
    private $updated_ts;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->wtid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT wtid FROM " . SystemTables::DB_TBL_WIDGET_TYPE . " WHERE wtid = '::wtid' AND wtsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::wtid' => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    public function getId() { return $this->wtid; }

    public function hasMandatoryData()
    {
        return !empty($this->slug) && !empty($this->name);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "INSERT INTO " . SystemTables::DB_TBL_WIDGET_TYPE . " (
            slug, name, description, icon, display_order
        ) VALUES (
            '::slug', '::name', '::description', '::icon', '::display_order'
        )";

        $args = array(
            '::slug' => $this->slug,
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::icon' => $this->icon ? $this->icon : '',
            '::display_order' => $this->display_order ? intval($this->display_order) : 0
        );

        if ($db->query($sql, $args)) {
            $this->wtid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->wtid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_WIDGET_TYPE . " SET
            slug = '::slug',
            name = '::name',
            description = '::description',
            icon = '::icon',
            display_order = '::display_order'
        WHERE wtid = '::wtid'";

        $args = array(
            '::slug' => $this->slug,
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::icon' => $this->icon ? $this->icon : '',
            '::display_order' => $this->display_order ? intval($this->display_order) : 0,
            '::wtid' => $this->wtid
        );

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_WIDGET_TYPE . " SET wtsid = 3 WHERE wtid = '::wtid'";
        $result = $db->query($sql, array('::wtid' => intval($id)));
        return $result ? true : false;
    }

    public function load()
    {
        if (!$this->wtid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_WIDGET_TYPE . " WHERE wtid = '::wtid' AND wtsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::wtid' => $this->wtid));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->wtid = null;
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
            'wtid' => $this->wtid,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'display_order' => $this->display_order,
            'created_ts' => $this->created_ts,
            'updated_ts' => $this->updated_ts
        );
    }

    // Getters and Setters
    public function getSlug() { return $this->slug; }
    public function setSlug($value) { $this->slug = $value; }

    public function getName() { return $this->name; }
    public function setName($value) { $this->name = $value; }

    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; }

    public function getIcon() { return $this->icon; }
    public function setIcon($value) { $this->icon = $value; }

    public function getDisplayOrder() { return $this->display_order; }
    public function setDisplayOrder($value) { $this->display_order = intval($value); }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }
}
