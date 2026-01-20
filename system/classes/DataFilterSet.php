<?php

/**
 * DataFilterSet - Manages filters linked to a graph or dashboard
 *
 * Extracts filter placeholders from SQL queries and applies filter values.
 * Filters are linked to graphs via placeholders in the query (e.g., :year, :date_from)
 *
 * @author Dynamic Graph Creator
 */
class DataFilterSet
{
    private $entity_type; // 'graph' or 'dashboard'
    private $entity_id;
    private $filters = array(); // DataFilter objects indexed by filter_key
    private $query;

    /**
     * Constructor
     *
     * @param string $entity_type 'graph' or 'dashboard'
     * @param int $entity_id Graph ID or Dashboard ID
     */
    public function __construct($entity_type, $entity_id)
    {
        $this->entity_type = $entity_type;
        $this->entity_id = intval($entity_id);
    }

    /**
     * Load filters for this entity
     * Fetches the query and finds matching filters based on placeholders
     *
     * @return bool
     */
    public function loadFilters()
    {
        // Get the query from the entity
        $this->query = $this->getEntityQuery();

        if (empty($this->query)) {
            return false;
        }

        // Extract placeholders from query
        $placeholders = DataFilterManager::extractPlaceholders($this->query);

        if (empty($placeholders)) {
            return true; // No filters needed
        }

        // Expand derived placeholders (_from, _to) to include base filter keys
        $expandedKeys = $this->expandDerivedPlaceholders($placeholders);

        // Get filters matching these placeholders (both direct and base keys)
        $this->filters = DataFilterManager::getByKeys($expandedKeys);

        return true;
    }

    /**
     * Expand derived placeholders to include base filter keys
     * e.g., ::main_datepicker_from -> also include ::main_datepicker
     *
     * @param array $placeholders Array of placeholder keys
     * @return array Array with both original and base keys
     */
    private function expandDerivedPlaceholders($placeholders)
    {
        $result = $placeholders;

        foreach ($placeholders as $placeholder) {
            // Check for _from or _to suffix
            if (preg_match('/^(::[\w]+)_(from|to)$/', $placeholder, $matches)) {
                $baseKey = $matches[1];
                if (!in_array($baseKey, $result)) {
                    $result[] = $baseKey;
                }
            }
        }

        return $result;
    }

    /**
     * Get the SQL query from the entity (graph or dashboard)
     *
     * @return string
     */
    private function getEntityQuery()
    {
        if ($this->entity_type === 'graph') {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT query FROM " . SystemTables::DB_TBL_GRAPH . " WHERE gid = '::gid' LIMIT 1";
            $res = $db->query($sql, array('::gid' => $this->entity_id));

            if ($res && $db->resultNumRows($res) > 0) {
                $row = $db->fetchAssocArray($res);
                return isset($row['query']) ? $row['query'] : '';
            }
        }

        return '';
    }

    /**
     * Apply filter values to a query
     *
     * @param string $query SQL query with placeholders
     * @param array $filter_values Values from user input (key => value)
     * @param array $savedPlaceholderSettings Optional settings saved with the graph (overrides filter defaults)
     * @return string Query with placeholders replaced
     */
    public function applyToQuery($query, $filter_values = array(), $savedPlaceholderSettings = array())
    {
        // Merge filter values with default values from filter definitions
        $mergedValues = array();

        foreach ($this->filters as $key => $filter) {
            $filterType = $filter->getFilterType();

            // For date range filters, check for _from/_to suffixed values
            if ($filterType === 'date_range' || $filterType === 'main_datepicker') {
                $fromKey = $key . '_from';
                $toKey = $key . '_to';

                // Check if values are sent as separate _from/_to keys
                if (isset($filter_values[$fromKey]) || isset($filter_values[$toKey])) {
                    // Convert to {from, to} format for expandDateRangeValues
                    $mergedValues[$key] = array(
                        'from' => isset($filter_values[$fromKey]) ? $filter_values[$fromKey] : '',
                        'to' => isset($filter_values[$toKey]) ? $filter_values[$toKey] : ''
                    );
                } elseif (isset($filter_values[$key])) {
                    // Already in correct format or single value
                    $mergedValues[$key] = $filter_values[$key];
                } elseif ($filter->getDefaultValue() !== null && $filter->getDefaultValue() !== '') {
                    $mergedValues[$key] = $filter->getDefaultValue();
                }
            } else {
                // Non-date-range filters: use provided value or default
                if (isset($filter_values[$key])) {
                    $mergedValues[$key] = $filter_values[$key];
                } elseif ($filter->getDefaultValue() !== null && $filter->getDefaultValue() !== '') {
                    $mergedValues[$key] = $filter->getDefaultValue();
                }
            }
        }

        // Build placeholder settings - use saved settings if provided, otherwise use filter required flag
        $placeholderSettings = array();
        foreach ($this->filters as $key => $filter) {
            // Check if saved settings exist for this placeholder
            if (isset($savedPlaceholderSettings[$key])) {
                $placeholderSettings[$key] = $savedPlaceholderSettings[$key];
            } else {
                // Fall back to filter's required flag
                $placeholderSettings[$key] = array(
                    'allowEmpty' => !$filter->getIsRequired()
                );
            }

            // For date range filters, also set settings for _from and _to variants
            $filterType = $filter->getFilterType();
            if ($filterType === 'date_range' || $filterType === 'main_datepicker') {
                $fromKey = $key . '_from';
                $toKey = $key . '_to';

                if (isset($savedPlaceholderSettings[$fromKey])) {
                    $placeholderSettings[$fromKey] = $savedPlaceholderSettings[$fromKey];
                } else {
                    $placeholderSettings[$fromKey] = $placeholderSettings[$key];
                }

                if (isset($savedPlaceholderSettings[$toKey])) {
                    $placeholderSettings[$toKey] = $savedPlaceholderSettings[$toKey];
                } else {
                    $placeholderSettings[$toKey] = $placeholderSettings[$key];
                }
            }
        }

        // Use centralized method for query placeholder replacement
        return DataFilterManager::replaceQueryPlaceholders($query, $mergedValues, $placeholderSettings);
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
        foreach ($this->filters as $key => $filter) {
            $result[$key] = $filter->toArray();
        }
        return $result;
    }

    /**
     * Check if any filters are required but missing values
     *
     * @param array $filter_values Values from user input
     * @return array Array of missing required filter keys
     */
    public function getMissingRequired($filter_values = array())
    {
        $missing = array();

        foreach ($this->filters as $key => $filter) {
            if ($filter->getIsRequired()) {
                $hasValue = isset($filter_values[$key]) && $filter_values[$key] !== '';
                $hasDefault = $filter->getDefaultValue() !== null && $filter->getDefaultValue() !== '';

                if (!$hasValue && !$hasDefault) {
                    $missing[] = $key;
                }
            }
        }

        return $missing;
    }

    /**
     * Check if filters are loaded and valid
     *
     * @return bool
     */
    public function hasFilters()
    {
        return !empty($this->filters);
    }
}
