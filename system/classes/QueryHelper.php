<?php

/**
 * QueryHelper - Centralized SQL Query Operations
 *
 * Provides comprehensive query operations including:
 * - Security validation (SELECT-only enforcement)
 * - Placeholder extraction and validation
 * - Placeholder replacement with filter values
 * - Mandatory filter validation
 *
 * @author Dynamic Graph Creator
 */
class QueryHelper
{
    // =========================================================================
    // SECURITY VALIDATION
    // =========================================================================

    /**
     * Validate SQL query security - ensures only SELECT queries are allowed
     *
     * This method prevents SQL injection and unauthorized database modifications
     * by ensuring queries can only read data, not modify it.
     *
     * Security checks performed:
     * - Removes SQL comments to prevent bypass attempts
     * - Ensures query starts with SELECT
     * - Blocks dangerous keywords (INSERT, UPDATE, DELETE, DROP, etc.)
     * - Prevents multiple statements (no semicolons except at end)
     *
     * @param string $query The SQL query to validate
     * @return array Array with 'valid' (bool) and 'error' (string if invalid)
     */
    public static function validateQuerySecurity($query)
    {
        if (empty($query)) {
            return array(
                'valid' => false,
                'error' => 'Query cannot be empty'
            );
        }

        // Remove SQL comments to prevent bypass attempts
        // Remove single-line comments (-- comment)
        $cleanQuery = preg_replace('/--[^\r\n]*/', '', $query);

        // Remove multi-line comments (/* comment */)
        $cleanQuery = preg_replace('/\/\*.*?\*\//s', '', $cleanQuery);

        // Remove extra whitespace and normalize
        $cleanQuery = preg_replace('/\s+/', ' ', trim($cleanQuery));

        // Check if query starts with SELECT (allowing whitespace)
        if (!preg_match('/^\s*SELECT\s+/i', $cleanQuery)) {
            return array(
                'valid' => false,
                'error' => 'Only SELECT queries are allowed. Query must start with SELECT.'
            );
        }

        // List of dangerous SQL keywords that should not appear in SELECT queries
        $dangerousKeywords = array(
            'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER',
            'TRUNCATE', 'REPLACE', 'EXEC', 'EXECUTE', 'CALL',
            'GRANT', 'REVOKE', 'LOCK', 'UNLOCK', 'RENAME',
            'LOAD DATA', 'OUTFILE', 'DUMPFILE', 'INTO OUTFILE'
        );

        // Check for dangerous keywords
        // Use word boundaries to avoid false positives
        foreach ($dangerousKeywords as $keyword) {
            // For multi-word keywords, replace space with \s+ for flexible matching
            $pattern = '/\b' . str_replace(' ', '\s+', preg_quote($keyword, '/')) . '\b/i';

            if (preg_match($pattern, $cleanQuery)) {
                return array(
                    'valid' => false,
                    'error' => 'Query contains disallowed keyword: ' . $keyword . '. Only SELECT queries are permitted.'
                );
            }
        }

        // Additional check: ensure no semicolons (prevents multiple statements)
        // Allow semicolon only at the very end
        $trimmedQuery = rtrim($cleanQuery, '; ');
        if (strpos($trimmedQuery, ';') !== false) {
            return array(
                'valid' => false,
                'error' => 'Multiple SQL statements are not allowed. Query should not contain semicolons except at the end.'
            );
        }

        // Query is valid
        return array(
            'valid' => true,
            'error' => null
        );
    }

    /**
     * Validate and return error response if invalid
     *
     * Convenience method that validates the query and sends AJAX error response if invalid.
     *
     * @param string $query The SQL query to validate
     * @return bool True if valid, false if invalid (and error response sent)
     */
    public static function validateOrFail($query)
    {
        $validation = self::validateQuerySecurity($query);

        if (!$validation['valid']) {
            Utility::ajaxResponseFalse($validation['error']);
            return false;
        }

        return true;
    }

    // =========================================================================
    // PLACEHOLDER EXTRACTION
    // =========================================================================

    /**
     * Extract placeholder keys from SQL query
     *
     * Finds all ::placeholder_name patterns in the query.
     * Optionally excludes system placeholders (like ::logged_in_uid).
     *
     * @param string $query SQL query string
     * @param bool $excludeSystemPlaceholders Whether to exclude system placeholders (default: true)
     * @return array Array of placeholder keys found (with :: prefix)
     */
    public static function extractPlaceholders($query, $excludeSystemPlaceholders = true)
    {
        $placeholders = array();

        // Get system placeholder keys to exclude
        $systemPlaceholderKeys = array();
        if ($excludeSystemPlaceholders) {
            $systemPlaceholderKeys = SystemPlaceholderManager::getAllKeys();
        }

        if (preg_match_all('::([a-zA-Z_][a-zA-Z0-9_]*)/', $query, $matches)) {
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

    // =========================================================================
    // PLACEHOLDER REPLACEMENT
    // =========================================================================

    /**
     * Replace placeholders in query with filter values
     *
     * Handles:
     * - System placeholders (::logged_in_uid, etc.)
     * - Date range expansion (_from, _to)
     * - Empty value handling based on settings
     * - SQL escaping for safety
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
                // Handle array values (multi-select)
                if (is_array($value)) {
                    $escapedValues = array_map(function ($v) use ($db) {
                        return "'" . $db->escapeString($v) . "'";
                    }, $value);
                    $replacement = implode(',', $escapedValues);
                } else {
                    $replacement = "'" . $db->escapeString($value) . "'";
                }

                $query = str_replace($placeholder, $replacement, $query);
            }
        }

        // Now handle empty placeholders based on settings
        foreach ($filterValues as $placeholder => $value) {
            $isEmpty = self::isValueEmpty($value);

            if ($isEmpty) {
                $settings = isset($placeholderSettings[$placeholder]) ? $placeholderSettings[$placeholder] : array();
                $allowEmpty = isset($settings['allowEmpty']) ? $settings['allowEmpty'] : true;

                if ($allowEmpty) {
                    // Replace with 1=1 to make condition always true
                    $query = str_replace($placeholder, '1=1', $query);
                }
                // If allowEmpty is false, placeholder should have been caught by validation
            }
        }

        return $query;
    }

    /**
     * Expand date range values into _from and _to placeholders
     *
     * Example: ['::date' => ['from' => '2024-01-01', 'to' => '2024-12-31']]
     * becomes: ['::date_from' => '2024-01-01', '::date_to' => '2024-12-31']
     *
     * @param array $filterValues Filter values
     * @return array Expanded filter values
     */
    private static function expandDateRangeValues($filterValues)
    {
        $expanded = array();

        foreach ($filterValues as $key => $value) {
            if (is_array($value) && (isset($value['from']) || isset($value['to']))) {
                // Date range filter
                if (isset($value['from'])) {
                    $expanded[$key . '_from'] = $value['from'];
                }
                if (isset($value['to'])) {
                    $expanded[$key . '_to'] = $value['to'];
                }
            } else {
                $expanded[$key] = $value;
            }
        }

        return $expanded;
    }

    /**
     * Check if a filter value is empty
     *
     * @param mixed $value The value to check
     * @return bool True if empty
     */
    private static function isValueEmpty($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }

    // =========================================================================
    // PLACEHOLDER VALIDATION
    // =========================================================================

    /**
     * Validate that required placeholders have values
     *
     * Checks each placeholder in the query against filter values.
     * Placeholders with allowEmpty=false must have non-empty values.
     *
     * @param string $query The SQL query with placeholders
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

    /**
     * Validate that mandatory filters are present in query
     *
     * Checks that all mandatory filters for a widget type are referenced in the query.
     *
     * @param string $query The SQL query
     * @param string $widgetTypeSlug Widget type slug (e.g., 'graph')
     * @return array Array with 'valid' => bool and 'missing' => array of missing filter keys
     */
    public static function validateMandatoryFiltersInQuery($query, $widgetTypeSlug)
    {
        return FilterWidgetTypeMandatoryManager::validateMandatoryFiltersInQuery($query, $widgetTypeSlug);
    }
}
