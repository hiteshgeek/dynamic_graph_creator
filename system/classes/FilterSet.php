<?php

/**
 * FilterSet - Collection manager for filters
 * Handles loading, saving, and applying filters for any entity
 *
 * @author Dynamic Graph Creator
 */
class FilterSet
{
    private $entity_type;
    private $entity_id;
    private $filters = array();

    /**
     * Constructor
     *
     * @param string $entity_type Type of entity (graph, report, etc.)
     * @param int $entity_id ID of the entity
     */
    public function __construct($entity_type, $entity_id = null)
    {
        $this->entity_type = $entity_type;
        $this->entity_id = $entity_id ? intval($entity_id) : null;
    }

    /**
     * Load all filters for the entity
     *
     * @return array
     */
    public function loadFilters()
    {
        if (!$this->entity_id) {
            return array();
        }

        $db = GraphDatabase::getInstance();

        $sql = "SELECT * FROM filter
                WHERE entity_type = '::entity_type'
                AND entity_id = '::entity_id'
                AND fsid != 3
                ORDER BY sequence ASC, fid ASC";

        $args = array(
            '::entity_type' => $this->entity_type,
            '::entity_id' => $this->entity_id
        );

        $res = $db->query($sql, $args);
        $this->filters = array();

        if ($res && $db->numRows($res) > 0) {
            while ($row = $db->fetchObject($res)) {
                $filter = new Filter();
                $filter->parse($row);
                $this->filters[] = $filter;
            }
        }

        return $this->filters;
    }

    /**
     * Get loaded filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get filters as array (for JSON response)
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();
        foreach ($this->filters as $filter) {
            $result[] = $filter->toArray();
        }
        return $result;
    }

    /**
     * Add a filter
     *
     * @param Filter $filter
     */
    public function addFilter(Filter $filter)
    {
        $filter->setEntityType($this->entity_type);
        $filter->setEntityId($this->entity_id);
        $this->filters[] = $filter;
    }

    /**
     * Save all filters from data array
     * Handles insert, update, and delete of filters
     *
     * @param array $filters_data Array of filter data
     * @return bool
     */
    public function saveFilters($filters_data)
    {
        if (!$this->entity_id) {
            return false;
        }

        $db = GraphDatabase::getInstance();

        // Get existing filter IDs
        $existing_ids = array();
        $this->loadFilters();
        foreach ($this->filters as $filter) {
            $existing_ids[] = $filter->getId();
        }

        $processed_ids = array();

        // Process each filter in the data
        foreach ($filters_data as $index => $data) {
            $filter_id = isset($data['fid']) ? intval($data['fid']) : 0;

            if ($filter_id && in_array($filter_id, $existing_ids)) {
                // Update existing filter
                $filter = new Filter($filter_id);
                $this->setFilterFromData($filter, $data, $index);
                $filter->update();
                $processed_ids[] = $filter_id;
            } else {
                // Insert new filter
                $filter = new Filter();
                $filter->setEntityType($this->entity_type);
                $filter->setEntityId($this->entity_id);
                $this->setFilterFromData($filter, $data, $index);

                if ($filter->insert()) {
                    $processed_ids[] = $filter->getId();
                }
            }
        }

        // Delete filters that are no longer in the data
        $to_delete = array_diff($existing_ids, $processed_ids);
        foreach ($to_delete as $fid) {
            Filter::hardDelete($fid);
        }

        // Reload filters
        $this->loadFilters();

        return true;
    }

    /**
     * Set filter properties from data array
     *
     * @param Filter $filter
     * @param array $data
     * @param int $index
     */
    private function setFilterFromData(Filter $filter, $data, $index)
    {
        $filter->setFilterKey(isset($data['filter_key']) ? $data['filter_key'] : '');
        $filter->setFilterLabel(isset($data['filter_label']) ? $data['filter_label'] : '');
        $filter->setFilterType(isset($data['filter_type']) ? $data['filter_type'] : 'text');
        $filter->setFilterOptions(isset($data['filter_options']) ? $data['filter_options'] : '');
        $filter->setDefaultValue(isset($data['default_value']) ? $data['default_value'] : '');
        $filter->setIsRequired(isset($data['is_required']) ? $data['is_required'] : 0);
        $filter->setSequence(isset($data['sequence']) ? $data['sequence'] : $index);
    }

    /**
     * Apply filter values to a query string
     * Replaces placeholders with actual values
     *
     * @param string $query SQL query with placeholders
     * @param array $filter_values Array of placeholder => value
     * @return string
     */
    public function applyToQuery($query, $filter_values)
    {
        $db = GraphDatabase::getInstance();

        foreach ($this->filters as $filter) {
            $key = $filter->getFilterKey();
            $type = $filter->getFilterType();

            // Get value from provided values or use default
            $value = isset($filter_values[$key]) ? $filter_values[$key] : $filter->getDefaultValue();

            // Handle empty values
            if ($value === '' || $value === null) {
                // For required filters with no value, this could be an error
                // For now, we'll use a safe default
                if ($type === 'number') {
                    $value = '0';
                } elseif ($type === 'date' || $type === 'date_range') {
                    $value = date('Y-m-d');
                } else {
                    $value = '';
                }
            }

            // Format value based on type
            $formatted = $this->formatValue($value, $type, $db);

            // Replace placeholder in query
            $query = str_replace($key, $formatted, $query);
        }

        return $query;
    }

    /**
     * Format a value based on filter type
     *
     * @param mixed $value
     * @param string $type
     * @param GraphDatabase $db
     * @return string
     */
    private function formatValue($value, $type, $db)
    {
        switch ($type) {
            case 'multi_select':
                // Handle array of values for IN clause
                if (is_array($value)) {
                    $escaped = array();
                    foreach ($value as $v) {
                        $escaped[] = "'" . $db->escapeString($v) . "'";
                    }
                    return implode(',', $escaped);
                } elseif (is_string($value) && strpos($value, ',') !== false) {
                    // Handle comma-separated string
                    $parts = explode(',', $value);
                    $escaped = array();
                    foreach ($parts as $v) {
                        $escaped[] = "'" . $db->escapeString(trim($v)) . "'";
                    }
                    return implode(',', $escaped);
                } else {
                    return "'" . $db->escapeString($value) . "'";
                }

            case 'number':
                return $db->escapeString($value);

            case 'date':
            case 'text':
            case 'select':
            default:
                return "'" . $db->escapeString($value) . "'";
        }
    }

    /**
     * Validate filter values
     *
     * @param array $filter_values
     * @return array Array of errors (empty if valid)
     */
    public function validate($filter_values)
    {
        $errors = array();

        foreach ($this->filters as $filter) {
            $key = $filter->getFilterKey();
            $value = isset($filter_values[$key]) ? $filter_values[$key] : '';

            if ($filter->getIsRequired() && empty($value)) {
                $errors[$key] = $filter->getFilterLabel() . ' is required';
            }
        }

        return $errors;
    }

    /**
     * Delete all filters for an entity
     *
     * @param string $entity_type
     * @param int $entity_id
     * @return bool
     */
    public static function deleteAllForEntity($entity_type, $entity_id)
    {
        $db = GraphDatabase::getInstance();

        $sql = "DELETE FROM filter
                WHERE entity_type = '::entity_type'
                AND entity_id = '::entity_id'";

        $args = array(
            '::entity_type' => $entity_type,
            '::entity_id' => intval($entity_id)
        );

        return $db->query($sql, $args) ? true : false;
    }

    /**
     * Set entity ID (useful when saving filters for a new entity)
     *
     * @param int $id
     */
    public function setEntityId($id)
    {
        $this->entity_id = intval($id);
    }
}
