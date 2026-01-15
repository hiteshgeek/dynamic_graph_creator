<?php

/**
 * Dashboard Template Category Class
 * Manages template categories with ordering, icons, and colors
 */

class DashboardTemplateCategory implements DatabaseObject
{
    private $ltcid;
    private $slug;
    private $name;
    private $description;
    private $icon;
    private $color;
    private $display_order;
    private $is_system;
    private $ltcsid;
    private $created_ts;
    private $updated_ts;

    public function __construct($ltcid = 0)
    {
        $this->ltcid = intval($ltcid);
        if ($this->ltcid > 0) {
            $this->load();
        }
    }

    // Getters
    public function getId() { return $this->ltcid; }
    public function getSlug() { return $this->slug; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getIcon() { return $this->icon; }
    public function getColor() { return $this->color; }
    public function getDisplayOrder() { return $this->display_order; }
    public function getIsSystem() { return $this->is_system; }
    public function getLtcsid() { return $this->ltcsid; }
    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }

    // Setters
    public function setSlug($slug) { $this->slug = $slug; }
    public function setName($name) { $this->name = $name; }
    public function setDescription($description) { $this->description = $description; }
    public function setIcon($icon) { $this->icon = $icon; }
    public function setColor($color) { $this->color = $color; }
    public function setDisplayOrder($order) { $this->display_order = intval($order); }
    public function setIsSystem($is_system) { $this->is_system = intval($is_system); }

    /**
     * Check if category exists
     */
    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT ltcid FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . " WHERE ltcid = '::ltcid' AND ltcsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::ltcid' => intval($id)));
        return $db->numRows($res) > 0;
    }

    /**
     * Check if slug exists
     */
    public static function slugExists($slug, $excludeId = null)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT ltcid FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . "
                WHERE slug = '::slug' AND ltcsid != 3";
        $args = array('::slug' => $slug);

        if ($excludeId) {
            $sql .= " AND ltcid != '::ltcid'";
            $args['::ltcid'] = intval($excludeId);
        }

        $res = $db->query($sql, $args);
        return $db->numRows($res) > 0;
    }

    /**
     * Insert new category
     */
    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();

        $sql = "INSERT INTO " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . "
                (slug, name, description, icon, color, display_order, is_system, ltcsid)
                VALUES ('::slug', '::name', '::description', '::icon', '::color', '::display_order', '::is_system', 1)";

        $args = array(
            '::slug' => $this->slug,
            '::name' => $this->name,
            '::description' => $this->description ?: null,
            '::icon' => $this->icon ?: null,
            '::color' => $this->color ?: null,
            '::display_order' => $this->display_order ?: 0,
            '::is_system' => $this->is_system ?: 0
        );

        if ($db->query($sql, $args)) {
            $this->ltcid = $db->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Update existing category
     */
    public function update()
    {
        if (!$this->ltcid || !$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();

        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . " SET
                slug = '::slug',
                name = '::name',
                description = '::description',
                icon = '::icon',
                color = '::color',
                display_order = '::display_order',
                is_system = '::is_system'
                WHERE ltcid = '::ltcid'";

        $args = array(
            '::ltcid' => $this->ltcid,
            '::slug' => $this->slug,
            '::name' => $this->name,
            '::description' => $this->description ?: null,
            '::icon' => $this->icon ?: null,
            '::color' => $this->color ?: null,
            '::display_order' => $this->display_order ?: 0,
            '::is_system' => $this->is_system ?: 0
        );

        return $db->query($sql, $args) ? true : false;
    }

    /**
     * Delete category (soft delete)
     */
    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . " SET ltcsid = 3 WHERE ltcid = '::ltcid'";
        return $db->query($sql, array('::ltcid' => intval($id))) ? true : false;
    }

    /**
     * Load category data
     */
    public function load()
    {
        if (!$this->ltcid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . " WHERE ltcid = '::ltcid' AND ltcsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::ltcid' => $this->ltcid));

        if ($db->numRows($res) > 0) {
            $row = $db->fetchAssoc($res);
            $this->populate($row);
            return true;
        }

        return false;
    }

    /**
     * Populate from array
     */
    public function populate($row)
    {
        $this->ltcid = $row['ltcid'] ?? null;
        $this->slug = $row['slug'] ?? null;
        $this->name = $row['name'] ?? null;
        $this->description = $row['description'] ?? null;
        $this->icon = $row['icon'] ?? null;
        $this->color = $row['color'] ?? null;
        $this->display_order = $row['display_order'] ?? 0;
        $this->is_system = $row['is_system'] ?? 0;
        $this->ltcsid = $row['ltcsid'] ?? 1;
        $this->created_ts = $row['created_ts'] ?? null;
        $this->updated_ts = $row['updated_ts'] ?? null;
    }

    /**
     * Check if has mandatory data
     */
    public function hasMandatoryData()
    {
        return !empty($this->slug) && !empty($this->name);
    }

    /**
     * Get all active categories ordered by display_order
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . "
                WHERE ltcsid != 3
                ORDER BY display_order ASC, name ASC";
        $res = $db->query($sql);

        $categories = array();
        while ($row = $db->fetchAssoc($res)) {
            $category = new DashboardTemplateCategory();
            $category->populate($row);
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Get all categories as associative array (for dropdowns)
     */
    public static function getAllAsArray()
    {
        $categories = self::getAll();
        $result = array();

        foreach ($categories as $category) {
            $result[] = array(
                'ltcid' => $category->getId(),
                'slug' => $category->getSlug(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
                'icon' => $category->getIcon(),
                'color' => $category->getColor(),
                'display_order' => $category->getDisplayOrder(),
                'is_system' => $category->getIsSystem()
            );
        }

        return $result;
    }

    /**
     * Get category by slug
     */
    public static function getBySlug($slug)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT ltcid FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . "
                WHERE slug = '::slug' AND ltcsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::slug' => $slug));

        if ($db->numRows($res) > 0) {
            $row = $db->fetchAssoc($res);
            return new DashboardTemplateCategory($row['ltcid']);
        }

        return null;
    }

    /**
     * Parse object from database result (required by DatabaseObject interface)
     */
    public function parse($obj)
    {
        if (!$obj) return false;

        $row = is_array($obj) ? $obj : (array)$obj;
        $this->populate($row);
        return true;
    }

    /**
     * String representation (required by DatabaseObject interface)
     */
    public function __toString()
    {
        return $this->name ?: '';
    }
}
