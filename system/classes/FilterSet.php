<?php

/**
 * FilterSet - Manages filters linked to a graph or dashboard
 *
 * Extracts filter placeholders from SQL queries and applies filter values.
 * Filters are linked to graphs via placeholders in the query (e.g., :year, :date_from)
 *
 * @author Dynamic Graph Creator
 */
class FilterSet
{
    private $entity_type; // 'graph' or 'dashboard'
    private $entity_id;
    private $filters = array(); // Filter objects indexed by filter_key
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
        $placeholders = Filter::extractPlaceholders($this->query);

        if (empty($placeholders)) {
            return true; // No filters needed
        }

        // Get filters matching these placeholders
        $this->filters = Filter::getByKeys($placeholders);

        return true;
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

            if ($res && $db->numRows($res) > 0) {
                $row = $db->fetchAssoc($res);
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
     * @return string Query with placeholders replaced
     */
    public function applyToQuery($query, $filter_values = array())
    {
        if (empty($this->filters)) {
            // No filters, return query as-is but remove any remaining placeholders
            return $this->cleanUnusedPlaceholders($query);
        }

        $db = Rapidkart::getInstance()->getDB();

        foreach ($this->filters as $key => $filter) {
            // Get the value - from user input, or default value
            $value = '';
            if (isset($filter_values[$key])) {
                $value = $filter_values[$key];
            } elseif ($filter->getDefaultValue() !== null && $filter->getDefaultValue() !== '') {
                $value = $filter->getDefaultValue();
            }

            // Escape the value for SQL safety
            $escaped_value = $db->escape($value);

            // Replace the placeholder in the query
            // Handle both :key and ::key formats
            $query = str_replace($key, $escaped_value, $query);
        }

        // Clean up any remaining placeholders that weren't matched
        return $this->cleanUnusedPlaceholders($query);
    }

    /**
     * Remove any remaining placeholders that weren't matched
     *
     * @param string $query
     * @return string
     */
    private function cleanUnusedPlaceholders($query)
    {
        // Replace any remaining :word placeholders with empty string or a safe default
        // Be careful not to remove :: placeholders used by the database class
        return preg_replace('/(?<![:\']):[a-zA-Z_][a-zA-Z0-9_]*/', "''", $query);
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
