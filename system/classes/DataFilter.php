<?php

/**
 * DataFilter model - Independent/Reusable filter definition
 * Filters define how to get data (static options or SQL query)
 * and can be linked to multiple graphs
 *
 * @author Dynamic Graph Creator
 */
class DataFilter implements DatabaseObject
{
    private $dfid;
    private $filter_key;
    private $filter_label;
    private $filter_type;
    private $data_source;
    private $data_query;
    private $static_options;
    private $filter_config;
    private $default_value;
    private $is_required;
    private $is_system;
    private $dfsid;
    private $created_ts;
    private $updated_ts;

    /**
     * Constructor
     *
     * @param int $id DataFilter ID to load
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->dfid = intval($id);
            $this->load();
        }
    }

    /**
     * Check if filter exists
     *
     * @param int $id
     * @return bool
     */
    public static function isExistent($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT dfid FROM " . SystemTables::DB_TBL_DATA_FILTER . " WHERE dfid = '::dfid' AND dfsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::dfid' => intval($id)));
        return $db->resultNumRows($res) > 0;
    }

    /**
     * Get filter ID
     * @return int
     */
    public function getId()
    {
        return $this->dfid;
    }

    /**
     * Check mandatory data
     * @return bool
     */
    public function hasMandatoryData()
    {
        return !empty($this->filter_key) && !empty($this->filter_label);
    }

    /**
     * Insert new filter
     * @return bool
     */
    public function insert()
    {
        if (!$this->hasMandatoryData()) {
            return false;
        }

        $db = Rapidkart::getInstance()->getDB();

        $sql = "INSERT INTO " . SystemTables::DB_TBL_DATA_FILTER . " (
            filter_key,
            filter_label,
            filter_type,
            data_source,
            data_query,
            static_options,
            filter_config,
            default_value,
            is_required,
            is_system
        ) VALUES (
            '::filter_key',
            '::filter_label',
            '::filter_type',
            '::data_source',
            '::data_query',
            '::static_options',
            '::filter_config',
            '::default_value',
            '::is_required',
            '::is_system'
        )";

        $args = array(
            '::filter_key' => $this->filter_key,
            '::filter_label' => $this->filter_label,
            '::filter_type' => $this->filter_type ? $this->filter_type : 'text',
            '::data_source' => $this->data_source ? $this->data_source : 'static',
            '::data_query' => $this->data_query ? $this->data_query : '',
            '::static_options' => $this->static_options ? $this->static_options : '',
            '::filter_config' => $this->filter_config ? $this->filter_config : '',
            '::default_value' => $this->default_value ? $this->default_value : '',
            '::is_required' => $this->is_required ? 1 : 0,
            '::is_system' => $this->is_system ? 1 : 0
        );

        $res = $db->query($sql, $args);
        if ($res) {
            $this->dfid = $db->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Update existing filter
     * @return bool
     */
    public function update()
    {
        if (!$this->dfid) {
            return false;
        }

        $db = Rapidkart::getInstance()->getDB();

        $sql = "UPDATE " . SystemTables::DB_TBL_DATA_FILTER . " SET
            filter_key = '::filter_key',
            filter_label = '::filter_label',
            filter_type = '::filter_type',
            data_source = '::data_source',
            data_query = '::data_query',
            static_options = '::static_options',
            filter_config = '::filter_config',
            default_value = '::default_value',
            is_required = '::is_required',
            is_system = '::is_system'
        WHERE dfid = '::dfid'";

        $args = array(
            '::filter_key' => $this->filter_key,
            '::filter_label' => $this->filter_label,
            '::filter_type' => $this->filter_type,
            '::data_source' => $this->data_source ? $this->data_source : 'static',
            '::data_query' => $this->data_query ? $this->data_query : '',
            '::static_options' => $this->static_options ? $this->static_options : '',
            '::filter_config' => $this->filter_config ? $this->filter_config : '',
            '::default_value' => $this->default_value ? $this->default_value : '',
            '::is_required' => $this->is_required ? 1 : 0,
            '::is_system' => $this->is_system ? 1 : 0,
            '::dfid' => $this->dfid
        );

        return $db->query($sql, $args) ? true : false;
    }

    /**
     * Soft delete filter
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "UPDATE " . SystemTables::DB_TBL_DATA_FILTER . " SET dfsid = 3 WHERE dfid = '::dfid'";
        return $db->query($sql, array('::dfid' => intval($id))) ? true : false;
    }

    /**
     * Load filter from database
     * @return bool
     */
    public function load()
    {
        if (!$this->dfid) {
            return false;
        }

        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DATA_FILTER . " WHERE dfid = '::dfid' AND dfsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::dfid' => $this->dfid));

        if (!$res || $db->resultNumRows($res) < 1) {
            $this->dfid = null;
            return false;
        }

        $row = $db->fetchObject($res);
        return $this->parse($row);
    }

    /**
     * Parse database row into object
     *
     * @param object $obj
     * @return bool
     */
    public function parse($obj)
    {
        if (!$obj) {
            return false;
        }

        foreach ($obj as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return true;
    }

    /**
     * Get filter options (executes query if data_source is 'query')
     * Query can return optional 'is_selected' column (1/0) to pre-select options
     * System placeholders (like ::logged_in_uid) are resolved before query execution
     *
     * @return array
     */
    public function getOptions()
    {
        if ($this->data_source === 'query' && !empty($this->data_query)) {
            // Execute query to get options
            $db = Rapidkart::getInstance()->getDB();

            // Resolve system placeholders in the query before execution
            $query = SystemPlaceholderManager::resolveInQuery($this->data_query);

            $res = $db->query($query);
            $options = array();
            if ($res) {
                while ($row = $db->fetchAssocArray($res)) {
                    $option = array(
                        'value' => isset($row['value']) ? $row['value'] : '',
                        'label' => isset($row['label']) ? $row['label'] : (isset($row['value']) ? $row['value'] : '')
                    );
                    // Check for is_selected column (supports 1, '1', true, 'true', 'yes')
                    if (isset($row['is_selected'])) {
                        $isSelected = $row['is_selected'];
                        $option['is_selected'] = ($isSelected === 1 || $isSelected === '1' || $isSelected === true || $isSelected === 'true' || $isSelected === 'yes');
                    }
                    $options[] = $option;
                }
            }
            return $options;
        } else {
            // Return static options
            if (!empty($this->static_options)) {
                $options = json_decode($this->static_options, true);
                return is_array($options) ? $options : array();
            }
            return array();
        }
    }

    /**
     * Convert to string
     * @return string
     */
    public function __toString()
    {
        return $this->filter_label ? $this->filter_label : '';
    }

    /**
     * Convert to array
     * @return array
     */
    public function toArray()
    {
        return array(
            'dfid' => $this->dfid,
            'filter_key' => $this->filter_key,
            'filter_label' => $this->filter_label,
            'filter_type' => $this->filter_type,
            'data_source' => $this->data_source,
            'data_query' => $this->data_query,
            'static_options' => $this->static_options,
            'filter_config' => $this->filter_config,
            'default_value' => $this->default_value,
            'is_required' => $this->is_required,
            'is_system' => $this->is_system,
            'options' => $this->getOptions(),
            'mandatory_widget_types' => $this->getMandatoryWidgetTypeIds()
        );
    }

    /**
     * Get widget type IDs where this filter is mandatory
     * @return array Array of widget type IDs
     */
    public function getMandatoryWidgetTypeIds()
    {
        if (!$this->dfid) {
            return array();
        }
        return FilterWidgetTypeMandatoryManager::getMandatoryWidgetTypeIdsForFilter($this->dfid);
    }

    /**
     * Get widget types where this filter is mandatory
     * @return array Array of WidgetType objects
     */
    public function getMandatoryWidgetTypes()
    {
        if (!$this->dfid) {
            return array();
        }
        return FilterWidgetTypeMandatoryManager::getMandatoryWidgetTypesForFilter($this->dfid);
    }

    /**
     * Check if this filter is mandatory for a specific widget type
     * @param int|string $widgetType Widget type ID or slug
     * @return bool
     */
    public function isMandatoryFor($widgetType)
    {
        if (!$this->dfid) {
            return false;
        }
        if (is_numeric($widgetType)) {
            return FilterWidgetTypeMandatoryManager::isMandatory($this->dfid, $widgetType);
        }
        return FilterWidgetTypeMandatoryManager::isMandatoryForSlug($this->dfid, $widgetType);
    }

    // Getters and Setters

    public function getFilterKey() { return $this->filter_key; }
    public function setFilterKey($value) { $this->filter_key = $value; }

    public function getFilterLabel() { return $this->filter_label; }
    public function setFilterLabel($value) { $this->filter_label = $value; }

    public function getFilterType() { return $this->filter_type; }
    public function setFilterType($value) { $this->filter_type = $value; }

    public function getDataSource() { return $this->data_source; }
    public function setDataSource($value) { $this->data_source = $value; }

    public function getDataQuery() { return $this->data_query; }
    public function setDataQuery($value) { $this->data_query = $value; }

    public function getStaticOptions() { return $this->static_options; }
    public function setStaticOptions($value) { $this->static_options = $value; }

    public function getFilterConfig() { return $this->filter_config; }
    public function setFilterConfig($value) { $this->filter_config = $value; }

    public function getDefaultValue() { return $this->default_value; }
    public function setDefaultValue($value) { $this->default_value = $value; }

    /**
     * Get decoded default value from JSON
     * Returns parsed array or null if empty/invalid
     * @return array|null
     */
    public function getDefaultValueDecoded()
    {
        if (empty($this->default_value)) {
            return null;
        }
        $decoded = json_decode($this->default_value, true);
        // If valid JSON object/array, return it
        if (is_array($decoded)) {
            return $decoded;
        }
        // Legacy: plain string value - wrap it
        return array('value' => $this->default_value);
    }

    /**
     * Validate that a required filter has a proper default value
     * @return bool True if valid, false if required filter missing default
     */
    public function validateRequiredDefault()
    {
        if (!$this->is_required) {
            return true; // Not required, no validation needed
        }

        $defaultValue = $this->getDefaultValueDecoded();
        if (empty($defaultValue)) {
            return false;
        }

        // Type-specific validation
        switch ($this->filter_type) {
            case 'date_range':
            case 'main_datepicker':
                // Must have a mode set
                return !empty($defaultValue['mode']);

            case 'multi_select':
            case 'checkbox':
            case 'tokeninput':
                // Must have values array with at least one item (unless mode is select_all)
                if (isset($defaultValue['mode']) && $defaultValue['mode'] === 'select_all') {
                    return true;
                }
                return !empty($defaultValue['values']) && is_array($defaultValue['values']);

            default:
                // text, number, date, select, radio - must have a value
                return isset($defaultValue['value']) && $defaultValue['value'] !== '';
        }
    }

    /**
     * Resolve default value to actual runtime values
     * For example: preset "Last 7 Days" -> actual date range
     * @return array|string|null Resolved default value ready for use
     */
    public function resolveDefaultValue()
    {
        $defaultValue = $this->getDefaultValueDecoded();
        if (empty($defaultValue)) {
            return null;
        }

        switch ($this->filter_type) {
            case 'date_range':
            case 'main_datepicker':
                return $this->resolveDateRangeDefault($defaultValue);

            case 'multi_select':
            case 'checkbox':
            case 'tokeninput':
                return isset($defaultValue['values']) ? $defaultValue['values'] : array();

            case 'select':
            case 'radio':
            case 'text':
            case 'number':
            case 'date':
            default:
                return isset($defaultValue['value']) ? $defaultValue['value'] : null;
        }
    }

    /**
     * Resolve date range default value based on mode
     * @param array $defaultValue The decoded default value
     * @return array Resolved date range with 'from' and 'to' keys, or mode indicator
     */
    private function resolveDateRangeDefault($defaultValue)
    {
        $mode = isset($defaultValue['mode']) ? $defaultValue['mode'] : 'selected';

        switch ($mode) {
            case 'select_all':
                // No date filter - return indicator
                return array('mode' => 'select_all');

            case 'block':
                // Block query until user selects - return indicator
                return array('mode' => 'block');

            case 'selected':
                // Use last selected value (from session) - return indicator
                return array('mode' => 'selected');

            case 'specific':
                // Use specific dates
                return array(
                    'mode' => 'specific',
                    'from' => isset($defaultValue['from']) ? $defaultValue['from'] : '',
                    'to' => isset($defaultValue['to']) ? $defaultValue['to'] : ''
                );

            case 'preset':
                // Resolve preset to actual dates
                $preset = isset($defaultValue['preset']) ? $defaultValue['preset'] : 'Last 7 Days';
                return $this->resolvePresetToDateRange($preset);

            default:
                return array('mode' => 'selected');
        }
    }

    /**
     * Resolve a preset name to actual date range
     * @param string $preset Preset name like "Last 7 Days", "Today", etc.
     * @return array Array with 'mode', 'from', 'to', and 'preset' keys
     */
    private function resolvePresetToDateRange($preset)
    {
        $today = date('Y-m-d');
        $from = $today;
        $to = $today;

        switch ($preset) {
            case 'Today':
                // from and to are both today
                break;

            case 'Yesterday':
                $from = date('Y-m-d', strtotime('-1 day'));
                $to = $from;
                break;

            case 'Last 7 Days':
                $from = date('Y-m-d', strtotime('-6 days'));
                break;

            case 'Last 30 Days':
                $from = date('Y-m-d', strtotime('-29 days'));
                break;

            case 'This Month':
                $from = date('Y-m-01');
                $to = date('Y-m-t');
                break;

            case 'Last Month':
                $from = date('Y-m-01', strtotime('first day of last month'));
                $to = date('Y-m-t', strtotime('last day of last month'));
                break;

            case 'Year to Date':
                $from = date('Y-01-01');
                break;

            case 'This Financial Year':
                // Assuming April to March financial year
                $currentMonth = (int)date('m');
                if ($currentMonth >= 4) {
                    $from = date('Y-04-01');
                    $to = date('Y-03-31', strtotime('+1 year'));
                } else {
                    $from = date('Y-04-01', strtotime('-1 year'));
                    $to = date('Y-03-31');
                }
                break;

            case 'Last Financial Year':
                $currentMonth = (int)date('m');
                if ($currentMonth >= 4) {
                    $from = date('Y-04-01', strtotime('-1 year'));
                    $to = date('Y-03-31');
                } else {
                    $from = date('Y-04-01', strtotime('-2 years'));
                    $to = date('Y-03-31', strtotime('-1 year'));
                }
                break;

            default:
                // Default to last 7 days
                $from = date('Y-m-d', strtotime('-6 days'));
        }

        return array(
            'mode' => 'preset',
            'preset' => $preset,
            'from' => $from,
            'to' => $to
        );
    }

    public function getIsRequired() { return $this->is_required; }
    public function setIsRequired($value) { $this->is_required = $value ? 1 : 0; }

    public function getIsSystem() { return $this->is_system; }
    public function setIsSystem($value) { $this->is_system = $value ? 1 : 0; }

    public function getCreatedTs() { return $this->created_ts; }
    public function getUpdatedTs() { return $this->updated_ts; }
}
