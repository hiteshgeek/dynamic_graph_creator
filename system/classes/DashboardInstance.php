<?php

/**
 * DashboardInstance - User-created dashboard instances
 *
 * @author Dynamic Graph Creator
 */
class DashboardInstance implements DatabaseObject
{
    private $diid;
    private $dtid; // Source template (nullable)
    private $name;
    private $description;
    private $structure; // JSON (template + content)
    private $config; // JSON (responsive config)
    private $company_id;
    private $is_system; // System dashboards cannot be deleted
    private $disid;
    private $created_ts;
    private $updated_ts;
    private $created_uid;
    private $updated_uid;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->diid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT diid FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " WHERE diid = '::diid' AND disid != 3 LIMIT 1";
        $res = $db->query($sql, array('::diid' => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    public function getId()
    {
        return $this->diid;
    }

    public function hasMandatoryData()
    {
        return !empty($this->name) && !empty($this->structure);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();

        // Build dynamic SQL to handle NULL values properly
        $fields = array('name', 'description', 'structure', 'config', 'is_system');
        $values = array('::name', '::description', '::structure', '::config', '::is_system');
        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::structure' => $this->structure,
            '::config' => $this->config ? $this->config : '{}',
            '::is_system' => $this->is_system ? 1 : 0
        );

        if ($this->dtid) {
            $fields[] = 'dtid';
            $values[] = '::dtid';
            $args['::dtid'] = $this->dtid;
        }

        if ($this->company_id) {
            $fields[] = 'company_id';
            $values[] = '::company_id';
            $args['::company_id'] = $this->company_id;
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
            $this->diid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->diid) return false;

        $db = Rapidkart::getInstance()->getDB();

        // Build dynamic SQL to handle NULL values properly
        $updates = array(
            "name = '::name'",
            "description = '::description'",
            "structure = '::structure'",
            "config = '::config'",
            "is_system = '::is_system'"
        );

        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::structure' => $this->structure,
            '::config' => $this->config ? $this->config : '{}',
            '::is_system' => $this->is_system ? 1 : 0
        );

        if ($this->dtid) {
            $updates[] = "dtid = '::dtid'";
            $args['::dtid'] = $this->dtid;
        } else {
            $updates[] = "dtid = NULL";
        }

        if ($this->company_id) {
            $updates[] = "company_id = '::company_id'";
            $args['::company_id'] = $this->company_id;
        } else {
            $updates[] = "company_id = NULL";
        }

        if ($this->updated_uid) {
            $updates[] = "updated_uid = '::updated_uid'";
            $args['::updated_uid'] = $this->updated_uid;
        }

        $args['::diid'] = $this->diid;

        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " SET
            " . implode(', ', $updates) . "
        WHERE diid = '::diid'";

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " SET disid = 3 WHERE diid = '::diid'";
        return $db->query($sql, array('::diid' => intval($id))) ? true : false;
    }

    public function load()
    {
        if (!$this->diid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " WHERE diid = '::diid' AND disid != 3 LIMIT 1";
        $res = $db->query($sql, array('::diid' => $this->diid));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->diid = null;
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

    public function __toString()
    {
        return $this->name ? $this->name : '';
    }

    public function toArray()
    {
        return array(
            'diid' => $this->diid,
            'dtid' => $this->dtid,
            'name' => $this->name,
            'description' => $this->description,
            'structure' => $this->structure,
            'config' => $this->config,
            'company_id' => $this->company_id,
            'is_system' => $this->is_system,
            'created_uid' => $this->created_uid,
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
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . " WHERE disid != 3 ORDER BY updated_ts DESC";
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
     * Get all instances for a user (by created_uid)
     * If user is admin, returns all dashboards for the company
     */
    public static function getUserDashboards($userId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $user = SystemConfig::getUser();

        // Admin users can see all company dashboards
        if ($user && $user->getIsAdmin()) {
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . "
                    WHERE company_id = '::company_id' AND disid != 3
                    ORDER BY updated_ts DESC";
            $res = $db->query($sql, array('::company_id' => BaseConfig::$company_id));
        } else {
            // Regular users only see their own dashboards
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . "
                    WHERE created_uid = '::created_uid' AND disid != 3
                    ORDER BY updated_ts DESC";
            $res = $db->query($sql, array('::created_uid' => intval($userId)));
        }

        $dashboards = array();
        while ($row = $db->fetchObject($res)) {
            $dashboard = new DashboardInstance();
            $dashboard->parse($row);
            $dashboards[] = $dashboard;
        }
        return $dashboards;
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
     * Get all widget (graph) IDs from the dashboard structure
     * @return array Array of unique graph IDs
     */
    public function getAllWidgetIds()
    {
        $structure = $this->getStructureArray();
        $widgetIds = array();

        if (!isset($structure['sections']) || !is_array($structure['sections'])) {
            return $widgetIds;
        }

        foreach ($structure['sections'] as $section) {
            if (!isset($section['areas']) || !is_array($section['areas'])) {
                continue;
            }

            foreach ($section['areas'] as $area) {
                // Check area's direct content
                if (isset($area['content']['widgetId']) && !empty($area['content']['widgetId'])) {
                    $widgetIds[] = intval($area['content']['widgetId']);
                }

                // Check sub-rows
                if (isset($area['subRows']) && is_array($area['subRows'])) {
                    foreach ($area['subRows'] as $subRow) {
                        if (isset($subRow['content']['widgetId']) && !empty($subRow['content']['widgetId'])) {
                            $widgetIds[] = intval($subRow['content']['widgetId']);
                        }
                    }
                }
            }
        }

        return array_unique($widgetIds);
    }

    /**
     * Get all unique filter keys from all widgets in the dashboard
     * Extracts placeholders from each graph's SQL query
     * @return array Array of unique filter keys (e.g., ['::company_list', '::global_datepicker'])
     */
    public function getAllFilterKeys()
    {
        $widgetIds = $this->getAllWidgetIds();
        $filterKeys = array();

        foreach ($widgetIds as $graphId) {
            $graph = new Graph($graphId);
            if ($graph->getId()) {
                $query = $graph->getQuery();
                if ($query) {
                    // Extract placeholders from query using DataFilterManager
                    $placeholders = DataFilterManager::extractPlaceholders($query);
                    $filterKeys = array_merge($filterKeys, $placeholders);
                }
            }
        }

        // Expand derived placeholders (_from, _to) to get base filter keys
        // e.g., ::global_datepicker_from -> ::global_datepicker
        $filterKeys = DataFilterManager::expandDerivedPlaceholders($filterKeys);

        // Return unique keys
        return array_values(array_unique($filterKeys));
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
     * Add a section to the dashboard
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
     * Remove a section from the dashboard
     */
    public function removeSection($sectionId)
    {
        $structure = $this->getStructureArray();

        $structure['sections'] = array_filter($structure['sections'], function ($section) use ($sectionId) {
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
    public function getDtid()
    {
        return $this->dtid;
    }
    public function setDtid($value)
    {
        $this->dtid = $value ? intval($value) : NULL;
    }

    public function getName()
    {
        return $this->name;
    }
    public function setName($value)
    {
        $this->name = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }
    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getStructure()
    {
        return $this->structure;
    }
    public function setStructure($value)
    {
        $this->structure = is_string($value) ? $value : json_encode($value);
    }

    public function getConfig()
    {
        return $this->config;
    }
    public function setConfig($value)
    {
        $this->config = is_string($value) ? $value : json_encode($value);
    }

    public function getCompanyId()
    {
        return $this->company_id;
    }
    public function setCompanyId($value)
    {
        $this->company_id = $value ? intval($value) : NULL;
    }

    public function getIsSystem()
    {
        return $this->is_system;
    }
    public function setIsSystem($value)
    {
        $this->is_system = $value ? 1 : 0;
    }

    public function getCreatedTs()
    {
        return $this->created_ts;
    }
    public function getUpdatedTs()
    {
        return $this->updated_ts;
    }

    public function getCreatedUid()
    {
        return $this->created_uid;
    }
    public function setCreatedUid($value)
    {
        $this->created_uid = intval($value);
    }

    public function getUpdatedUid()
    {
        return $this->updated_uid;
    }
    public function setUpdatedUid($value)
    {
        $this->updated_uid = intval($value);
    }
}
