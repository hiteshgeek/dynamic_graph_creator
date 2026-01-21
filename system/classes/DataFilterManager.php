<?php

/**
 * DataFilterManager - Centralized filter retrieval and management
 * Provides methods to get filters by various criteria
 *
 * @author Dynamic Graph Creator
 */
class DataFilterManager
{
    /**
     * Get all active filters
     *
     * @return array Array of DataFilter objects indexed by filter_key
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DATA_FILTER . " WHERE dfsid != 3 ORDER BY filter_label";
        $res = $db->query($sql);

        $filters = array();
        while ($row = $db->fetchAssocArray($res)) {
            $filter = new DataFilter();
            $filter->parse((object)$row);
            $filters[$row['filter_key']] = $filter;
        }
        return $filters;
    }

    /**
     * Get all active filters as array data (for JSON responses)
     *
     * @return array Array of filter data arrays
     */
    public static function getAllAsArray()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DATA_FILTER . " WHERE dfsid != 3 ORDER BY filter_label";
        $res = $db->query($sql);

        $filters = array();
        while ($row = $db->fetchAssocArray($res)) {
            $filter = new DataFilter();
            $filter->parse((object)$row);
            $filters[] = $filter->toArray();
        }
        return $filters;
    }

    /**
     * Get a single filter by ID
     *
     * @param int $id Filter ID
     * @return DataFilter|null DataFilter object or null if not found
     */
    public static function getById($id)
    {
        if (!DataFilter::isExistent($id)) {
            return null;
        }
        return new DataFilter($id);
    }

    /**
     * Get filters by their IDs, maintaining order and indexed by filter_key
     *
     * @param array $ids Array of filter IDs
     * @return array Array of DataFilter objects indexed by filter_key, in order of IDs provided
     */
    public static function getByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $db = Rapidkart::getInstance()->getDB();

        // Build placeholders for IN clause
        $placeholders = array();
        $args = array();
        foreach ($ids as $i => $id) {
            $placeholders[] = "'::id{$i}'";
            $args["::id{$i}"] = intval($id);
        }

        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DATA_FILTER . "
                WHERE dfid IN (" . implode(',', $placeholders) . ") AND dfsid != 3";
        $res = $db->query($sql, $args);

        // First, collect all filters indexed by dfid
        $filtersByDfid = array();
        while ($row = $db->fetchAssocArray($res)) {
            $filter = new DataFilter();
            $filter->parse((object)$row);
            $filtersByDfid[$row['dfid']] = $filter;
        }

        // Build result array in order of IDs, indexed by filter_key
        $filters = array();
        foreach ($ids as $id) {
            if (isset($filtersByDfid[$id])) {
                $filter = $filtersByDfid[$id];
                $filters[$filter->getFilterKey()] = $filter;
            }
        }

        return $filters;
    }

    /**
     * Get filters by their IDs as array data (for JSON responses)
     *
     * @param array $ids Array of filter IDs
     * @return array Array of filter data arrays, in order of IDs provided
     */
    public static function getByIdsAsArray($ids)
    {
        $filters = self::getByIds($ids);
        $result = array();
        foreach ($filters as $filter) {
            $result[] = $filter->toArray();
        }
        return $result;
    }

    /**
     * Get filters by their keys (for matching placeholders in queries)
     * Also handles derived placeholders (_from, _to) by finding base filter keys
     *
     * @param array $keys Array of filter keys like ['::year', '::date_from', '::main_datepicker_from']
     * @return array Array of DataFilter objects indexed by filter_key
     */
    public static function getByKeys($keys)
    {
        if (empty($keys)) {
            return array();
        }

        // Expand derived placeholders to include base filter keys
        $expandedKeys = self::expandDerivedPlaceholders($keys);

        $db = Rapidkart::getInstance()->getDB();

        // Build placeholders for IN clause
        $placeholders = array();
        $args = array();
        foreach ($expandedKeys as $i => $key) {
            $placeholders[] = "'::key{$i}'";
            $args["::key{$i}"] = $key;
        }

        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DATA_FILTER . "
                WHERE filter_key IN (" . implode(',', $placeholders) . ") AND dfsid != 3";
        $res = $db->query($sql, $args);

        $filters = array();
        while ($row = $db->fetchAssocArray($res)) {
            $filter = new DataFilter();
            $filter->parse((object)$row);
            $filters[$row['filter_key']] = $filter;
        }
        return $filters;
    }

    /**
     * Expand derived placeholders to include base filter keys
     * e.g., ::main_datepicker_from -> also include ::main_datepicker
     *
     * @param array $placeholders Array of placeholder keys
     * @return array Array with both original and base keys
     */
    public static function expandDerivedPlaceholders($placeholders)
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
     * Get filters by their keys as array data (for JSON responses)
     *
     * @param array $keys Array of filter keys
     * @return array Array of filter data arrays
     */
    public static function getByKeysAsArray($keys)
    {
        $filters = self::getByKeys($keys);
        $result = array();
        foreach ($filters as $filter) {
            $result[] = $filter->toArray();
        }
        return $result;
    }

    /**
     * Check if a filter key already exists
     *
     * @param string $key The filter key to check
     * @param int|null $excludeId Filter ID to exclude (for updates)
     * @return bool True if key exists, false otherwise
     */
    public static function keyExists($key, $excludeId = null)
    {
        $db = Rapidkart::getInstance()->getDB();

        $sql = "SELECT dfid FROM " . SystemTables::DB_TBL_DATA_FILTER . "
                WHERE filter_key = '::key' AND dfsid != 3";
        $args = array('::key' => $key);

        if ($excludeId !== null) {
            $sql .= " AND dfid != '::exclude_id'";
            $args['::exclude_id'] = intval($excludeId);
        }

        $sql .= " LIMIT 1";
        $res = $db->query($sql, $args);
        return $db->resultNumRows($res) > 0;
    }

    /**
     * Check if a filter key conflicts with existing filter keys (substring issue)
     *
     * @param string $key The filter key to check
     * @param int|null $excludeId Filter ID to exclude (for updates)
     * @return array|null Array with conflict info, or null if no conflict
     */
    public static function checkKeyConflict($key, $excludeId = null)
    {
        $db = Rapidkart::getInstance()->getDB();

        $sql = "SELECT dfid, filter_key, filter_label FROM " . SystemTables::DB_TBL_DATA_FILTER . "
                WHERE dfsid != 3";
        $args = array();

        if ($excludeId !== null) {
            $sql .= " AND dfid != '::exclude_id'";
            $args['::exclude_id'] = intval($excludeId);
        }

        $res = $db->query($sql, $args);
        $conflicts = array();

        while ($row = $db->fetchAssocArray($res)) {
            $existingKey = $row['filter_key'];
            if ($existingKey === $key) {
                continue;
            }
            if (strpos($existingKey, $key) !== false) {
                $conflicts[] = "{$existingKey} ({$row['filter_label']})";
            }
            if (strpos($key, $existingKey) !== false) {
                $conflicts[] = "{$existingKey} ({$row['filter_label']})";
            }
        }

        if (!empty($conflicts)) {
            return array(
                'conflicts' => $conflicts,
                'message' => "Placeholder conflicts with: " . implode(', ', $conflicts)
            );
        }

        return null;
    }

    /**
     * Extract placeholders from a SQL query
     * Excludes system placeholders (like ::logged_in_uid) - only returns filter placeholders
     *
     * @param string $query SQL query string
     * @param bool $excludeSystemPlaceholders Whether to exclude system placeholders (default: true)
     * @return array Array of placeholder keys found
     */
    public static function extractPlaceholders($query, $excludeSystemPlaceholders = true)
    {
        $placeholders = array();

        // Get system placeholder keys to exclude
        $systemPlaceholderKeys = array();
        if ($excludeSystemPlaceholders) {
            $systemPlaceholderKeys = SystemPlaceholderManager::getAllKeys();
        }

        if (preg_match_all('/::([a-zA-Z_][a-zA-Z0-9_]*)/', $query, $matches)) {
            foreach ($matches[0] as $index => $match) {
                // Get the key without :: prefix
                $key = $matches[1][$index];

                // Skip if it's a system placeholder
                if ($excludeSystemPlaceholders && in_array($key, $systemPlaceholderKeys)) {
                    continue;
                }

                if (!in_array($match, $placeholders)) {
                    $placeholders[] = $match;
                }
            }
        }
        return $placeholders;
    }

    /**
     * Extract placeholders from a SQL query and get matching filters
     *
     * @param string $query SQL query string
     * @return array Array of DataFilter objects indexed by filter_key
     */
    public static function getFromQuery($query)
    {
        $placeholders = self::extractPlaceholders($query);
        return self::getByKeys($placeholders);
    }

    /**
     * Extract placeholders from a SQL query and get matching filters as array
     *
     * @param string $query SQL query string
     * @return array Array of filter data arrays
     */
    public static function getFromQueryAsArray($query)
    {
        $filters = self::getFromQuery($query);
        $result = array();
        foreach ($filters as $filter) {
            $result[] = $filter->toArray();
        }
        return $result;
    }

    /**
     * Check if a filter value is empty
     *
     * @param mixed $value Filter value to check
     * @return bool True if empty, false otherwise
     */
    public static function isValueEmpty($value)
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (is_array($value) && count($value) === 0) {
            return true;
        }
        return false;
    }

    /**
     * Expand filter values for date range filters
     * Converts {from: 'x', to: 'y'} into ::key_from = 'x', ::key_to = 'y'
     *
     * @param array $filterValues Filter values keyed by placeholder
     * @return array Expanded filter values
     */
    public static function expandDateRangeValues($filterValues)
    {
        $expanded = array();

        foreach ($filterValues as $placeholder => $value) {
            // Check if value is a date range object (has 'from' and 'to' keys)
            if (is_array($value) && (isset($value['from']) || isset($value['to']))) {
                // Add _from and _to variants
                if (isset($value['from'])) {
                    $expanded[$placeholder . '_from'] = $value['from'];
                }
                if (isset($value['to'])) {
                    $expanded[$placeholder . '_to'] = $value['to'];
                }
                // Also keep original placeholder with the from-to as a string for simple replacements
                $expanded[$placeholder] = (isset($value['from']) ? $value['from'] : '') . ' - ' . (isset($value['to']) ? $value['to'] : '');
            } else {
                $expanded[$placeholder] = $value;
            }
        }

        return $expanded;
    }

    /**
     * Replace SQL condition containing a placeholder with 1=1
     * Handles patterns like: field = ::placeholder, field IN (::placeholder),
     * field BETWEEN ::from AND ::to, etc.
     *
     * @param string $query The SQL query
     * @param string $placeholder The placeholder to find and replace
     * @return string The query with condition replaced by 1=1
     */
    public static function replaceConditionWithTrue($query, $placeholder)
    {
        $escapedPlaceholder = preg_quote($placeholder, '/');

        // Column pattern: matches table.column, alias.column, or just column
        // Also matches simple function calls like DATE(column) or FUNC(table.column)
        // The function pattern uses \w+\s*\([\w.,\s]+\) to avoid matching complex expressions
        $columnPattern = '(?:\w+\s*\([\w.,\s]+\)|(?:\w+\.)?+\w+)';

        // Pattern for IN/NOT IN clauses: field IN (::placeholder) or field NOT IN (::placeholder)
        $inPattern = '/' . $columnPattern . '\s+(?:NOT\s+)?IN\s*\(\s*' . $escapedPlaceholder . '\s*\)/i';
        $query = preg_replace($inPattern, '1=1', $query);

        // Pattern for BETWEEN: field BETWEEN ::placeholder AND value or value AND ::placeholder
        // Also handles the case where both sides are placeholders (::x_from AND ::x_to)
        $betweenPattern = '/' . $columnPattern . '\s+BETWEEN\s+(?:' . $escapedPlaceholder . '\s+AND\s+(?:::\w+|[^\s,)]+)|(?:::\w+|[^\s,)]+)\s+AND\s+' . $escapedPlaceholder . ')/i';
        $query = preg_replace($betweenPattern, '1=1', $query);

        // Pattern for comparison operators: field = ::placeholder, field >= ::placeholder, etc.
        $comparisonPattern = '/' . $columnPattern . '\s*(?:=|!=|<>|>=|<=|>|<)\s*' . $escapedPlaceholder . '/i';
        $query = preg_replace($comparisonPattern, '1=1', $query);

        // Pattern for LIKE: field LIKE ::placeholder
        $likePattern = '/' . $columnPattern . '\s+(?:NOT\s+)?LIKE\s+' . $escapedPlaceholder . '/i';
        $query = preg_replace($likePattern, '1=1', $query);

        // If placeholder still exists (not part of a recognized condition), just replace with 'test'
        if (strpos($query, $placeholder) !== false) {
            $query = str_replace($placeholder, "'test'", $query);
        }

        return $query;
    }

    /**
     * Replace placeholders in a query with filter values
     *
     * @param string $query SQL query with placeholders
     * @param array $filterValues Filter values keyed by placeholder (e.g., ['::category' => 'value'])
     * @param array $placeholderSettings Settings per placeholder (e.g., ['::category' => ['allowEmpty' => false]])
     * @return string Query with placeholders replaced
     */
    public static function replaceQueryPlaceholders($query, $filterValues, $placeholderSettings = array())
    {
        $db = Rapidkart::getInstance()->getDB();

        // First, resolve system placeholders (like ::logged_in_uid)
        $query = SystemPlaceholderManager::resolveInQuery($query);

        // Expand date range filters into _from and _to placeholders
        $filterValues = self::expandDateRangeValues($filterValues);

        // Sort filter keys by length descending to replace longer placeholders first
        // This prevents ::category from matching within ::category_checkbox
        uksort($filterValues, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        // First, handle filters that have values
        foreach ($filterValues as $placeholder => $value) {
            $isEmpty = self::isValueEmpty($value);

            if (!$isEmpty) {
                // Filter has a value, replace normally
                if (is_array($value)) {
                    $escaped = array();
                    foreach ($value as $v) {
                        $escaped[] = "'" . $db->escapeString($v) . "'";
                    }
                    $query = str_replace($placeholder, implode(',', $escaped), $query);
                } else {
                    $query = str_replace($placeholder, "'" . $db->escapeString($value) . "'", $query);
                }
            } else {
                // Filter is empty, check if allowEmpty
                $settings = isset($placeholderSettings[$placeholder]) ? $placeholderSettings[$placeholder] : array();
                $allowEmpty = isset($settings['allowEmpty']) ? $settings['allowEmpty'] : true;

                if ($allowEmpty) {
                    // Replace the entire condition containing this placeholder with 1=1
                    $query = self::replaceConditionWithTrue($query, $placeholder);
                }
                // If not allowEmpty and empty, validation should have caught this
            }
        }

        // Final pass: replace any remaining placeholders with allowEmpty=true using 1=1
        preg_match_all('/::([a-zA-Z_][a-zA-Z0-9_]*)/', $query, $matches);
        foreach ($matches[0] as $placeholder) {
            $settings = isset($placeholderSettings[$placeholder]) ? $placeholderSettings[$placeholder] : array();
            $allowEmpty = isset($settings['allowEmpty']) ? $settings['allowEmpty'] : true;

            if ($allowEmpty) {
                $query = self::replaceConditionWithTrue($query, $placeholder);
            }
        }

        return $query;
    }

    /**
     * Validate that required placeholders have values
     *
     * @param string $query SQL query with placeholders
     * @param array $filterValues Filter values keyed by placeholder
     * @param array $placeholderSettings Settings per placeholder
     * @return array Array of missing required placeholder keys (empty if all valid)
     */
    public static function validateRequiredPlaceholders($query, $filterValues, $placeholderSettings)
    {
        $missing = array();

        // Expand date range values first
        $filterValues = self::expandDateRangeValues($filterValues);

        // Extract all placeholders from query
        $placeholders = self::extractPlaceholders($query);

        foreach ($placeholders as $placeholder) {
            $settings = isset($placeholderSettings[$placeholder]) ? $placeholderSettings[$placeholder] : array();
            $allowEmpty = isset($settings['allowEmpty']) ? $settings['allowEmpty'] : true;

            if (!$allowEmpty) {
                // Check if value is provided and not empty
                $value = isset($filterValues[$placeholder]) ? $filterValues[$placeholder] : null;
                if (self::isValueEmpty($value)) {
                    $missing[] = $placeholder;
                }
            }
        }

        return $missing;
    }
}
