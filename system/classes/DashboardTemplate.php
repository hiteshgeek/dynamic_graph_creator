<?php

/**
 * DashboardTemplate - Pre-defined dashboard templates
 *
 * @author Dynamic Graph Creator
 */
class DashboardTemplate implements DatabaseObject
{
    private $dtid;
    private $name;
    private $description;
    private $dtcid; // Foreign key to dashboard_template_category
    private $thumbnail;
    private $structure; // JSON
    private $display_order;
    private $is_system;
    private $dtsid;
    private $created_ts;
    private $updated_ts;
    private $created_uid;
    private $updated_uid;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->dtid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT dtid FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE . " WHERE dtid = '::dtid' AND dtsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::dtid' => intval($id)));
        return $db->numRows($res) > 0;
    }

    public function getId() { return $this->dtid; }

    public function hasMandatoryData()
    {
        return !empty($this->name) && !empty($this->structure);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();

        // Build dynamic SQL to handle NULL values properly
        $fields = array('name', 'description', 'dtcid', 'structure', 'display_order', 'is_system');
        $values = array('::name', '::description', '::dtcid', '::structure', '::display_order', '::is_system');
        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::dtcid' => $this->dtcid ? $this->dtcid : 0,
            '::structure' => $this->structure,
            '::display_order' => $this->display_order ? $this->display_order : 0,
            '::is_system' => $this->is_system ? 1 : 0
        );

        if ($this->thumbnail) {
            $fields[] = 'thumbnail';
            $values[] = '::thumbnail';
            $args['::thumbnail'] = $this->thumbnail;
        }

        if ($this->created_uid) {
            $fields[] = 'created_uid';
            $values[] = '::created_uid';
            $args['::created_uid'] = $this->created_uid;
        }

        $sql = "INSERT INTO " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE . " (
            " . implode(', ', $fields) . "
        ) VALUES (
            '" . implode("', '", $values) . "'
        )";

        if ($db->query($sql, $args)) {
            $this->dtid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->dtid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE . " SET
            name = '::name',
            description = '::description',
            dtcid = '::dtcid',
            thumbnail = '::thumbnail',
            structure = '::structure',
            display_order = '::display_order',
            is_system = '::is_system',
            updated_uid = '::updated_uid'
        WHERE dtid = '::dtid'";

        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::dtcid' => $this->dtcid ? $this->dtcid : 0,
            '::thumbnail' => $this->thumbnail ? $this->thumbnail : '',
            '::structure' => $this->structure,
            '::display_order' => $this->display_order ? $this->display_order : 0,
            '::is_system' => $this->is_system ? 1 : 0,
            '::updated_uid' => $this->updated_uid ? $this->updated_uid : 0,
            '::dtid' => $this->dtid
        );

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();

        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE . " SET dtsid = 3 WHERE dtid = '::dtid'";
        return $db->query($sql, array('::dtid' => intval($id))) ? true : false;
    }

    public function load()
    {
        if (!$this->dtid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE . " WHERE dtid = '::dtid' AND dtsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::dtid' => $this->dtid));

        if (!$res || $db->numRows($res) < 1) return false;

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
            'dtid' => $this->dtid,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'thumbnail' => $this->thumbnail,
            'structure' => $this->structure,
            'is_system' => $this->is_system,
            'created_ts' => $this->created_ts,
            'updated_ts' => $this->updated_ts
        );
    }

    /**
     * Get all templates
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT lt.* FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE . " lt
                LEFT JOIN " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . " ltc ON lt.dtcid = ltc.dtcid
                WHERE lt.dtsid != 3
                ORDER BY ltc.display_order ASC, lt.name ASC";
        $res = $db->query($sql);

        $templates = array();
        while ($row = $db->fetchObject($res)) {
            $template = new DashboardTemplate();
            $template->parse($row);
            $templates[] = $template;
        }
        return $templates;
    }

    /**
     * Get all templates grouped by category with category metadata
     */
    public static function getAllGrouped()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT
                    lt.*,
                    ltc.slug as category_slug,
                    ltc.name as category_name,
                    ltc.description as category_description,
                    ltc.icon as category_icon,
                    ltc.color as category_color,
                    ltc.display_order as category_order
                FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE . " lt
                LEFT JOIN " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . " ltc ON lt.dtcid = ltc.dtcid
                WHERE lt.dtsid != 3 AND (ltc.dtcsid != 3 OR lt.dtcid IS NULL)
                ORDER BY ltc.display_order ASC, ltc.name ASC, lt.display_order ASC, lt.name ASC";
        $res = $db->query($sql);

        $grouped = array();

        while ($row = $db->fetchAssoc($res)) {
            // Use category slug as key, or 'uncategorized' for NULL categories
            $catKey = $row['category_slug'] ? $row['category_slug'] : 'uncategorized';

            if (!isset($grouped[$catKey])) {
                $grouped[$catKey] = array(
                    'category' => array(
                        'slug' => $row['category_slug'],
                        'name' => $row['category_name'] ? $row['category_name'] : 'Uncategorized',
                        'description' => $row['category_description'],
                        'icon' => $row['category_icon'] ? $row['category_icon'] : 'fa-folder',
                        'color' => $row['category_color'] ? $row['category_color'] : '#6c757d',
                        'display_order' => $row['category_order'] ? $row['category_order'] : 999
                    ),
                    'templates' => array()
                );
            }
            $grouped[$catKey]['templates'][] = $row;
        }

        // Filter out empty categories and return only categories with templates
        return array_filter($grouped, function($categoryData) {
            return !empty($categoryData['templates']);
        });
    }

    /**
     * Get template structure as array
     */
    public function getStructureArray()
    {
        if (empty($this->structure)) {
            return ['sections' => []];
        }
        return json_decode($this->structure, true) ?? ['sections' => []];
    }

    /**
     * Create instance from template
     */
    public function createInstance($userId, $name = null)
    {
        $instance = new DashboardInstance();
        $instance->setDtid($this->dtid);
        $instance->setName($name ? $name : $this->name . ' (Copy)');
        $instance->setStructure($this->structure);
        $instance->setUserId($userId);
        $instance->setCreatedUid($userId);
        return $instance;
    }

    // Getters and Setters
    public function getName() { return $this->name; }
    public function setName($value) { $this->name = $value; }

    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; }

    public function getDtcid() { return $this->dtcid; }
    public function setDtcid($value) { $this->dtcid = intval($value); }

    // Helper method to get category object
    public function getCategory()
    {
        if ($this->dtcid) {
            return new DashboardTemplateCategory($this->dtcid);
        }
        return null;
    }

    public function getThumbnail() { return $this->thumbnail; }
    public function setThumbnail($value) { $this->thumbnail = $value; }

    public function getStructure() { return $this->structure; }
    public function setStructure($value) {
        $this->structure = is_string($value) ? $value : json_encode($value);
    }

    public function getDisplayOrder() { return $this->display_order; }
    public function setDisplayOrder($value) { $this->display_order = intval($value); }

    public function getIsSystem() { return $this->is_system; }
    public function setIsSystem($value) { $this->is_system = $value ? 1 : 0; }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }

    public function setCreatedUid($value) { $this->created_uid = intval($value); }
    public function setUpdatedUid($value) { $this->updated_uid = intval($value); }
}
