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
        $placeholders = DataFilter::extractPlaceholders($this->query);

        if (empty($placeholders)) {
            return true; // No filters needed
        }

        // Get filters matching these placeholders
        $this->filters = DataFilter::getByKeys($placeholders);

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

            // Check if value is empty
            $isEmpty = $this->isValueEmpty($value);

            if (!$isEmpty) {
                // Escape the value for SQL safety
                if (is_array($value)) {
                    // Handle multi-value filters (multi-select, checkbox)
                    $escaped_parts = array();
                    foreach ($value as $v) {
                        $escaped_parts[] = "'" . $db->escapeString($v) . "'";
                    }
                    $escaped_value = implode(',', $escaped_parts);
                } else {
                    $escaped_value = "'" . $db->escapeString($value) . "'";
                }

                // Replace the placeholder in the query
                $query = str_replace($key, $escaped_value, $query);
            } else {
                // Value is empty - if filter is not required, replace condition with 1=1
                if (!$filter->getIsRequired()) {
                    $query = $this->replaceConditionWithTrue($query, $key);
                }
                // If required but empty, validation should have caught this
            }
        }

        // Clean up any remaining placeholders that weren't matched
        return $this->cleanUnusedPlaceholders($query);
    }

    /**
     * Check if a filter value is empty
     *
     * @param mixed $value
     * @return bool
     */
    private function isValueEmpty($value)
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (is_array($value)) {
            $filtered = array_filter($value, function ($v) {
                return $v !== null && $v !== '';
            });
            return empty($filtered);
        }
        return false;
    }

    /**
     * Replace a condition containing a placeholder with 1=1
     *
     * @param string $query
     * @param string $placeholder
     * @return string
     */
    private function replaceConditionWithTrue($query, $placeholder)
    {
        // Pattern to match common SQL conditions containing the placeholder
        // Matches: column = ::placeholder, column IN (::placeholder), column LIKE ::placeholder, etc.
        $patterns = array(
            // column IN (::placeholder)
            '/\b\w+\s+IN\s*\(\s*' . preg_quote($placeholder, '/') . '\s*\)/i',
            // column NOT IN (::placeholder)
            '/\b\w+\s+NOT\s+IN\s*\(\s*' . preg_quote($placeholder, '/') . '\s*\)/i',
            // column = ::placeholder
            '/\b\w+\s*=\s*' . preg_quote($placeholder, '/') . '/i',
            // column != ::placeholder or column <> ::placeholder
            '/\b\w+\s*(<>|!=)\s*' . preg_quote($placeholder, '/') . '/i',
            // column LIKE ::placeholder
            '/\b\w+\s+LIKE\s+' . preg_quote($placeholder, '/') . '/i',
            // column >= ::placeholder
            '/\b\w+\s*>=\s*' . preg_quote($placeholder, '/') . '/i',
            // column <= ::placeholder
            '/\b\w+\s*<=\s*' . preg_quote($placeholder, '/') . '/i',
            // column > ::placeholder
            '/\b\w+\s*>\s*' . preg_quote($placeholder, '/') . '/i',
            // column < ::placeholder
            '/\b\w+\s*<\s*' . preg_quote($placeholder, '/') . '/i',
        );

        foreach ($patterns as $pattern) {
            $query = preg_replace($pattern, '1=1', $query);
        }

        return $query;
    }

    /**
     * Remove any remaining placeholders that weren't matched
     *
     * @param string $query
     * @return string
     */
    private function cleanUnusedPlaceholders($query)
    {
        // For any remaining placeholders, replace the entire condition with 1=1
        preg_match_all('/::[a-zA-Z_][a-zA-Z0-9_]*/', $query, $matches);
        foreach ($matches[0] as $placeholder) {
            $query = $this->replaceConditionWithTrue($query, $placeholder);
        }
        return $query;
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
