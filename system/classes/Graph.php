<?php

/**
 * Graph model - Stores graph definitions
 *
 * @author Dynamic Graph Creator
 */
class Graph implements DatabaseObject
{
    private $gid;
    private $name;
    private $description;
    private $graph_type;
    private $config;
    private $query;
    private $data_mapping;
    private $placeholder_settings;
    private $snapshot;
    private $gsid;
    private $created_ts;
    private $updated_ts;
    private $created_uid;
    private $updated_uid;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->gid = intval($id);
            $this->load();
        }
    }

    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT gid FROM " . SystemTables::DB_TBL_GRAPH . " WHERE gid = '::gid' AND gsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::gid' => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    public function getId() { return $this->gid; }

    public function hasMandatoryData()
    {
        return !empty($this->name) && !empty($this->query) && !empty($this->graph_type);
    }

    public function insert()
    {
        if (!$this->hasMandatoryData()) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "INSERT INTO " . SystemTables::DB_TBL_GRAPH . " (
            name, description, graph_type, config, query, data_mapping, placeholder_settings, created_uid
        ) VALUES (
            '::name', '::description', '::graph_type', '::config', '::query', '::data_mapping', '::placeholder_settings', '::created_uid'
        )";

        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::graph_type' => $this->graph_type,
            '::config' => $this->config ? $this->config : '{}',
            '::query' => $this->query,
            '::data_mapping' => $this->data_mapping ? $this->data_mapping : '{}',
            '::placeholder_settings' => $this->placeholder_settings ? $this->placeholder_settings : '{}',
            '::created_uid' => $this->created_uid ? $this->created_uid : 0
        );

        if ($db->query($sql, $args)) {
            $this->gid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!$this->gid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_GRAPH . " SET
            name = '::name',
            description = '::description',
            graph_type = '::graph_type',
            config = '::config',
            query = '::query',
            data_mapping = '::data_mapping',
            placeholder_settings = '::placeholder_settings',
            updated_uid = '::updated_uid'
        WHERE gid = '::gid'";

        $args = array(
            '::name' => $this->name,
            '::description' => $this->description ? $this->description : '',
            '::graph_type' => $this->graph_type,
            '::config' => $this->config ? $this->config : '{}',
            '::query' => $this->query,
            '::data_mapping' => $this->data_mapping ? $this->data_mapping : '{}',
            '::placeholder_settings' => $this->placeholder_settings ? $this->placeholder_settings : '{}',
            '::updated_uid' => $this->updated_uid ? $this->updated_uid : 0,
            '::gid' => $this->gid
        );

        return $db->query($sql, $args) ? true : false;
    }

    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_GRAPH . " SET gsid = 3 WHERE gid = '::gid'";
        $result = $db->query($sql, array('::gid' => intval($id)));

        if ($result) {
            FilterSet::deleteAllForEntity('graph', $id);
        }
        return $result ? true : false;
    }

    /**
     * Update only the snapshot field
     */
    public function updateSnapshot()
    {
        if (!$this->gid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_GRAPH . " SET
            snapshot = '::snapshot',
            updated_uid = '::updated_uid'
        WHERE gid = '::gid'";

        $args = array(
            '::snapshot' => $this->snapshot ? $this->snapshot : '',
            '::updated_uid' => $this->updated_uid ? $this->updated_uid : 0,
            '::gid' => $this->gid
        );

        return $db->query($sql, $args) ? true : false;
    }

    public function load()
    {
        if (!$this->gid) return false;

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_GRAPH . " WHERE gid = '::gid' AND gsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::gid' => $this->gid));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->gid = null;
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
            'gid' => $this->gid,
            'name' => $this->name,
            'description' => $this->description,
            'graph_type' => $this->graph_type,
            'config' => $this->config,
            'query' => $this->query,
            'data_mapping' => $this->data_mapping,
            'placeholder_settings' => $this->placeholder_settings,
            'snapshot' => $this->snapshot,
            'created_ts' => $this->created_ts,
            'updated_ts' => $this->updated_ts
        );
    }

    /**
     * Execute query and return chart data
     */
    public function execute($filter_values = array())
    {
        $db = Rapidkart::getInstance()->getDB();

        $filterSet = new DataFilterSet('graph', $this->gid);
        $filterSet->loadFilters();

        // Get placeholder settings saved with the graph
        $placeholderSettings = $this->placeholder_settings ? json_decode($this->placeholder_settings, true) : array();
        if (!is_array($placeholderSettings)) {
            $placeholderSettings = array();
        }

        // Validate required placeholders before executing query
        $missingRequired = DataFilterManager::validateRequiredPlaceholders($this->query, $filter_values, $placeholderSettings);
        if (!empty($missingRequired)) {
            // Format placeholder names for display (remove :: prefix)
            $filterNames = array_map(function($p) { return ltrim($p, ':'); }, $missingRequired);
            // Return empty data with error message
            return $this->getEmptyChartData('Required filter(s) missing value: ' . implode(', ', $filterNames));
        }

        $query = $filterSet->applyToQuery($this->query, $filter_values, $placeholderSettings);
        $res = $db->query($query);

        if (!$res) {
            return array('error' => $db->getMysqlError());
        }

        $mapping = json_decode($this->data_mapping, true);
        $rows = array();
        while ($row = $db->fetchAssocArray($res)) {
            $rows[] = $row;
        }

        return $this->formatChartData($rows, $mapping);
    }

    /**
     * Format query results into chart data
     */
    private function formatChartData($rows, $mapping)
    {
        if ($this->graph_type === 'pie') {
            $nameCol = isset($mapping['name_column']) ? $mapping['name_column'] : '';
            $valueCol = isset($mapping['value_column']) ? $mapping['value_column'] : '';

            $items = array();
            foreach ($rows as $row) {
                $items[] = array(
                    'name' => isset($row[$nameCol]) ? $row[$nameCol] : '',
                    'value' => isset($row[$valueCol]) ? floatval($row[$valueCol]) : 0
                );
            }
            return array('items' => $items);
        } else {
            $xCol = isset($mapping['x_column']) ? $mapping['x_column'] : '';
            $yCol = isset($mapping['y_column']) ? $mapping['y_column'] : '';

            $categories = array();
            $values = array();

            foreach ($rows as $row) {
                $categories[] = isset($row[$xCol]) ? $row[$xCol] : '';
                $values[] = isset($row[$yCol]) ? floatval($row[$yCol]) : 0;
            }

            return array('categories' => $categories, 'values' => $values);
        }
    }

    /**
     * Get empty chart data structure with optional error message
     *
     * @param string|null $error Error message to include
     * @return array Empty chart data with error
     */
    private function getEmptyChartData($error = null)
    {
        $result = array();

        if ($this->graph_type === 'pie') {
            $result['items'] = array();
        } else {
            $result['categories'] = array();
            $result['values'] = array();
        }

        if ($error) {
            $result['error'] = $error;
        }

        return $result;
    }

    // Getters and Setters
    public function getName() { return $this->name; }
    public function setName($value) { $this->name = $value; }

    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; }

    public function getGraphType() { return $this->graph_type; }
    public function setGraphType($value) { $this->graph_type = $value; }

    public function getConfig() { return $this->config; }
    public function setConfig($value) { $this->config = is_string($value) ? $value : json_encode($value); }

    public function getQuery() { return $this->query; }
    public function setQuery($value) { $this->query = $value; }

    public function getDataMapping() { return $this->data_mapping; }
    public function setDataMapping($value) { $this->data_mapping = is_string($value) ? $value : json_encode($value); }

    public function getPlaceholderSettings() { return $this->placeholder_settings; }
    public function setPlaceholderSettings($value) { $this->placeholder_settings = is_string($value) ? $value : json_encode($value); }

    public function getSnapshot() { return $this->snapshot; }
    public function setSnapshot($value) { $this->snapshot = $value; }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }

    public function setCreatedUid($value) { $this->created_uid = intval($value); }
    public function setUpdatedUid($value) { $this->updated_uid = intval($value); }
}
