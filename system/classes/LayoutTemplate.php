<?php

/**
 * LayoutTemplate - Pre-defined layout templates
 *
 * @author Dynamic Graph Creator
 */
class LayoutTemplate implements DatabaseObject
{
    private $ltid;
    private $name;
    private $description;
    private $ltcid; // Foreign key to layout_template_category
    private $thumbnail;
    private $structure; // JSON
    private $is_system;
    private $ltsid;
    private $created_ts;
    private $updated_ts;
    private $created_uid;
    private $updated_uid;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->ltid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT ltid FROM " . SystemTables::DB_TBL_LAYOUT_TEMPLATE . " WHERE ltid = '::ltid' AND ltsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::ltid' => intval($id)));
        return $db->numRows($res) > 0;
    }

    public function getId() { return $this->ltid; }

    public function hasMandatoryData()
    {
        return !empty($this->name) && !empty($this->structure);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();

        // Build dynamic SQL to handle NULL values properly
        $fields = array('name', 'description', 'ltcid', 'structure', 'is_system');
        $values = array('::name', '::description', '::ltcid', '::structure', '::is_system');
        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::ltcid' => $this->ltcid ? $this->ltcid : null,
            '::structure' => $this->structure,
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

        $sql = "INSERT INTO " . SystemTables::DB_TBL_LAYOUT_TEMPLATE . " (
            " . implode(', ', $fields) . "
        ) VALUES (
            '" . implode("', '", $values) . "'
        )";

        if ($db->query($sql, $args)) {
            $this->ltid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->ltid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_LAYOUT_TEMPLATE . " SET
            name = '::name',
            description = '::description',
            ltcid = '::ltcid',
            thumbnail = '::thumbnail',
            structure = '::structure',
            is_system = '::is_system',
            updated_uid = '::updated_uid'
        WHERE ltid = '::ltid'";

        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::ltcid' => $this->ltcid ? $this->ltcid : null,
            '::thumbnail' => $this->thumbnail ? $this->thumbnail : '',
            '::structure' => $this->structure,
            '::is_system' => $this->is_system ? 1 : 0,
            '::updated_uid' => $this->updated_uid ? $this->updated_uid : 0,
            '::ltid' => $this->ltid
        );

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();

        $sql = "UPDATE " . SystemTables::DB_TBL_LAYOUT_TEMPLATE . " SET ltsid = 3 WHERE ltid = '::ltid'";
        return $db->query($sql, array('::ltid' => intval($id))) ? true : false;
    }

    public function load()
    {
        if (!$this->ltid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_LAYOUT_TEMPLATE . " WHERE ltid = '::ltid' AND ltsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::ltid' => $this->ltid));

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
            'ltid' => $this->ltid,
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
        $sql = "SELECT lt.* FROM " . SystemTables::DB_TBL_LAYOUT_TEMPLATE . " lt
                LEFT JOIN " . SystemTables::DB_TBL_LAYOUT_TEMPLATE_CATEGORY . " ltc ON lt.ltcid = ltc.ltcid
                WHERE lt.ltsid != 3
                ORDER BY ltc.display_order ASC, lt.name ASC";
        $res = $db->query($sql);

        $templates = array();
        while ($row = $db->fetchObject($res)) {
            $template = new LayoutTemplate();
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
                FROM " . SystemTables::DB_TBL_LAYOUT_TEMPLATE . " lt
                LEFT JOIN " . SystemTables::DB_TBL_LAYOUT_TEMPLATE_CATEGORY . " ltc ON lt.ltcid = ltc.ltcid
                WHERE lt.ltsid != 3 AND (ltc.ltcsid != 3 OR lt.ltcid IS NULL)
                ORDER BY ltc.display_order ASC, ltc.name ASC, lt.name ASC";
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
        $instance = new LayoutInstance();
        $instance->setLtid($this->ltid);
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

    public function getLtcid() { return $this->ltcid; }
    public function setLtcid($value) { $this->ltcid = intval($value); }

    // Helper method to get category object
    public function getCategory()
    {
        if ($this->ltcid) {
            return new LayoutTemplateCategory($this->ltcid);
        }
        return null;
    }

    public function getThumbnail() { return $this->thumbnail; }
    public function setThumbnail($value) { $this->thumbnail = $value; }

    public function getStructure() { return $this->structure; }
    public function setStructure($value) {
        $this->structure = is_string($value) ? $value : json_encode($value);
    }

    public function getIsSystem() { return $this->is_system; }
    public function setIsSystem($value) { $this->is_system = $value ? 1 : 0; }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }

    public function setCreatedUid($value) { $this->created_uid = intval($value); }
    public function setUpdatedUid($value) { $this->updated_uid = intval($value); }
}
