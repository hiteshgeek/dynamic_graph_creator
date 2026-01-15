<?php

/**
 * LayoutInstance - User-created layout instances
 *
 * @author Dynamic Graph Creator
 */
class DashboardInstance implements DatabaseObject
{
    private $liid;
    private $ltid; // Source template (nullable)
    private $name;
    private $description;
    private $structure; // JSON (template + content)
    private $config; // JSON (responsive config)
    private $company_id;
    private $user_id;
    private $lisid;
    private $created_ts;
    private $updated_ts;
    private $created_uid;
    private $updated_uid;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->liid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT liid FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " WHERE liid = '::liid' AND lisid != 3 LIMIT 1";
        $res = $db->query($sql, array('::liid' => intval($id)));
        return $db->numRows($res) > 0;
    }

    public function getId() { return $this->liid; }

    public function hasMandatoryData()
    {
        return !empty($this->name) && !empty($this->structure);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();

        // Build dynamic SQL to handle NULL values properly
        $fields = array('name', 'description', 'structure', 'config');
        $values = array('::name', '::description', '::structure', '::config');
        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::structure' => $this->structure,
            '::config' => $this->config ? $this->config : '{}'
        );

        if ($this->ltid) {
            $fields[] = 'ltid';
            $values[] = '::ltid';
            $args['::ltid'] = $this->ltid;
        }

        if ($this->company_id) {
            $fields[] = 'company_id';
            $values[] = '::company_id';
            $args['::company_id'] = $this->company_id;
        }

        if ($this->user_id) {
            $fields[] = 'user_id';
            $values[] = '::user_id';
            $args['::user_id'] = $this->user_id;
        }

        if ($this->created_uid) {
            $fields[] = 'created_uid';
            $values[] = '::created_uid';
            $args['::created_uid'] = $this->created_uid;
        }

        $sql = "INSERT INTO " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " (
            " . implode(', ', $fields) . "
        ) VALUES (
            '" . implode("', '", $values) . "'
        )";

        if ($db->query($sql, $args)) {
            $this->liid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->liid) return false;

        $db = Rapidkart::getInstance()->getDB();

        // Build dynamic SQL to handle NULL values properly
        $updates = array(
            "name = '::name'",
            "description = '::description'",
            "structure = '::structure'",
            "config = '::config'"
        );

        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::structure' => $this->structure,
            '::config' => $this->config ? $this->config : '{}'
        );

        if ($this->ltid) {
            $updates[] = "ltid = '::ltid'";
            $args['::ltid'] = $this->ltid;
        } else {
            $updates[] = "ltid = NULL";
        }

        if ($this->company_id) {
            $updates[] = "company_id = '::company_id'";
            $args['::company_id'] = $this->company_id;
        } else {
            $updates[] = "company_id = NULL";
        }

        if ($this->user_id) {
            $updates[] = "user_id = '::user_id'";
            $args['::user_id'] = $this->user_id;
        } else {
            $updates[] = "user_id = NULL";
        }

        if ($this->updated_uid) {
            $updates[] = "updated_uid = '::updated_uid'";
            $args['::updated_uid'] = $this->updated_uid;
        }

        $args['::liid'] = $this->liid;

        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " SET
            " . implode(', ', $updates) . "
        WHERE liid = '::liid'";

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " SET lisid = 3 WHERE liid = '::liid'";
        return $db->query($sql, array('::liid' => intval($id))) ? true : false;
    }

    public function load()
    {
        if (!$this->liid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " WHERE liid = '::liid' AND lisid != 3 LIMIT 1";
        $res = $db->query($sql, array('::liid' => $this->liid));

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
            'liid' => $this->liid,
            'ltid' => $this->ltid,
            'name' => $this->name,
            'description' => $this->description,
            'structure' => $this->structure,
            'config' => $this->config,
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'created_ts' => $this->created_ts,
            'updated_ts' => $this->updated_ts
        );
    }

    /**
     * Get all instances
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " WHERE lisid != 3 ORDER BY updated_ts DESC";
        $res = $db->query($sql);

        $instances = array();
        while ($row = $db->fetchObject($res)) {
            $instance = new DashboardInstance();
            $instance->parse($row);
            $instances[] = $instance;
        }
        return $instances;
    }

    /**
     * Get all instances for a user
     */
    public static function getUserLayouts($userId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . "
                WHERE user_id = '::user_id' AND lisid != 3
                ORDER BY updated_ts DESC";
        $res = $db->query($sql, array('::user_id' => intval($userId)));

        $layouts = array();
        while ($row = $db->fetchObject($res)) {
            $layout = new DashboardInstance();
            $layout->parse($row);
            $layouts[] = $layout;
        }
        return $layouts;
    }

    /**
     * Get structure as array
     */
    public function getStructureArray()
    {
        return json_decode($this->structure, true);
    }

    /**
     * Get config as array
     */
    public function getConfigArray()
    {
        return json_decode($this->config, true);
    }

    /**
     * Update a specific section's content
     */
    public function updateAreaContent($sectionId, $areaId, $content)
    {
        $structure = $this->getStructureArray();

        foreach ($structure['sections'] as &$section) {
            if ($section['sid'] === $sectionId) {
                foreach ($section['areas'] as &$area) {
                    if ($area['aid'] === $areaId) {
                        $area['content'] = $content;
                        break 2;
                    }
                }
            }
        }

        $this->setStructure($structure);
        return $this->update();
    }

    /**
     * Add a section to the layout
     */
    public function addSection($sectionData, $position = 'bottom')
    {
        $structure = $this->getStructureArray();

        // Support numeric index position
        if (is_numeric($position)) {
            $index = intval($position);
            // Insert at specific index
            array_splice($structure['sections'], $index, 0, [$sectionData]);
        } elseif ($position === 'top') {
            array_unshift($structure['sections'], $sectionData);
        } else {
            $structure['sections'][] = $sectionData;
        }

        $this->setStructure($structure);
        return $this->update();
    }

    /**
     * Remove a section from the layout
     */
    public function removeSection($sectionId)
    {
        $structure = $this->getStructureArray();

        $structure['sections'] = array_filter($structure['sections'], function($section) use ($sectionId) {
            return $section['sid'] !== $sectionId;
        });

        // Re-index array
        $structure['sections'] = array_values($structure['sections']);

        $this->setStructure($structure);
        return $this->update();
    }

    /**
     * Reorder sections
     */
    public function reorderSections($order)
    {
        $structure = $this->getStructureArray();

        // Reorder sections based on provided order array
        $reordered = array();
        foreach ($order as $sectionId) {
            foreach ($structure['sections'] as $section) {
                if ($section['sid'] === $sectionId) {
                    $reordered[] = $section;
                    break;
                }
            }
        }

        $structure['sections'] = $reordered;
        $this->setStructure($structure);
        return $this->update();
    }

    // Getters and Setters
    public function getLtid() { return $this->ltid; }
    public function setLtid($value) { $this->ltid = $value ? intval($value) : NULL; }

    public function getName() { return $this->name; }
    public function setName($value) { $this->name = $value; }

    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; }

    public function getStructure() { return $this->structure; }
    public function setStructure($value) {
        $this->structure = is_string($value) ? $value : json_encode($value);
    }

    public function getConfig() { return $this->config; }
    public function setConfig($value) {
        $this->config = is_string($value) ? $value : json_encode($value);
    }

    public function getCompanyId() { return $this->company_id; }
    public function setCompanyId($value) { $this->company_id = $value ? intval($value) : NULL; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($value) { $this->user_id = $value ? intval($value) : NULL; }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }

    public function setCreatedUid($value) { $this->created_uid = intval($value); }
    public function setUpdatedUid($value) { $this->updated_uid = intval($value); }
}
