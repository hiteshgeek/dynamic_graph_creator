<?php

/**
 * FilterWidgetTypeMandatoryManager - Manages filter-widget type mandatory relationships
 * Handles the many-to-many relationship between filters and widget types for mandatory filters
 *
 * @author Dynamic Graph Creator
 */
class FilterWidgetTypeMandatoryManager
{
    /**
     * Get all mandatory filters for a widget type
     * @param int $wtid Widget Type ID
     * @return array Array of DataFilter objects
     */
    public static function getMandatoryFiltersForWidgetType($wtid)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT df.* FROM " . SystemTables::DB_TBL_DATA_FILTER . " df
                INNER JOIN " . SystemTables::DB_TBL_FILTER_WIDGET_TYPE_MANDATORY . " fwtm ON df.dfid = fwtm.dfid
                WHERE fwtm.wtid = '::wtid' AND df.dfsid != 3
                ORDER BY df.filter_label ASC";
        $res = $db->query($sql, array('::wtid' => intval($wtid)));

        $filters = array();
        while ($row = $db->fetchObject($res)) {
            $filter = new DataFilter();
            $filter->parse($row);
            $filters[] = $filter;
        }
        return $filters;
    }

    /**
     * Get all mandatory filters for a widget type by slug
     * @param string $slug Widget type slug (e.g., 'graph')
     * @return array Array of DataFilter objects
     */
    public static function getMandatoryFiltersForWidgetTypeSlug($slug)
    {
        $wtid = WidgetTypeManager::getIdBySlug($slug);
        if (!$wtid) {
            return array();
        }
        return self::getMandatoryFiltersForWidgetType($wtid);
    }

    /**
     * Get mandatory filter keys for a widget type slug
     * @param string $slug Widget type slug (e.g., 'graph')
     * @return array Array of filter keys (e.g., ['::company_list', '::date_from'])
     */
    public static function getMandatoryFilterKeysForWidgetTypeSlug($slug)
    {
        $filters = self::getMandatoryFiltersForWidgetTypeSlug($slug);
        $keys = array();
        foreach ($filters as $filter) {
            $keys[] = $filter->getFilterKey();
        }
        return $keys;
    }

    /**
     * Get all widget types where a filter is mandatory
     * @param int $dfid Filter ID
     * @return array Array of WidgetType objects
     */
    public static function getMandatoryWidgetTypesForFilter($dfid)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT wt.* FROM " . SystemTables::DB_TBL_WIDGET_TYPE . " wt
                INNER JOIN " . SystemTables::DB_TBL_FILTER_WIDGET_TYPE_MANDATORY . " fwtm ON wt.wtid = fwtm.wtid
                WHERE fwtm.dfid = '::dfid' AND wt.wtsid != 3
                ORDER BY wt.display_order ASC";
        $res = $db->query($sql, array('::dfid' => intval($dfid)));

        $types = array();
        while ($row = $db->fetchObject($res)) {
            $type = new WidgetType();
            $type->parse($row);
            $types[] = $type;
        }
        return $types;
    }

    /**
     * Get widget type IDs where a filter is mandatory
     * @param int $dfid Filter ID
     * @return array Array of widget type IDs
     */
    public static function getMandatoryWidgetTypeIdsForFilter($dfid)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT fwtm.wtid FROM " . SystemTables::DB_TBL_FILTER_WIDGET_TYPE_MANDATORY . " fwtm
                INNER JOIN " . SystemTables::DB_TBL_WIDGET_TYPE . " wt ON fwtm.wtid = wt.wtid
                WHERE fwtm.dfid = '::dfid' AND wt.wtsid != 3";
        $res = $db->query($sql, array('::dfid' => intval($dfid)));

        $ids = array();
        while ($row = $db->fetchAssocArray($res)) {
            $ids[] = intval($row['wtid']);
        }
        return $ids;
    }

    /**
     * Set mandatory widget types for a filter (replaces all existing mappings)
     * @param int $dfid Filter ID
     * @param array $wtids Array of widget type IDs
     * @return bool Success
     */
    public static function setMandatoryForFilter($dfid, $wtids)
    {
        $db = Rapidkart::getInstance()->getDB();
        $dfid = intval($dfid);

        // Delete all existing mappings for this filter
        $sql = "DELETE FROM " . SystemTables::DB_TBL_FILTER_WIDGET_TYPE_MANDATORY . " WHERE dfid = '::dfid'";
        $db->query($sql, array('::dfid' => $dfid));

        // Insert new mappings
        if (!empty($wtids) && is_array($wtids)) {
            foreach ($wtids as $wtid) {
                $wtid = intval($wtid);
                if ($wtid > 0) {
                    $sql = "INSERT INTO " . SystemTables::DB_TBL_FILTER_WIDGET_TYPE_MANDATORY . " (dfid, wtid) VALUES ('::dfid', '::wtid')";
                    $db->query($sql, array('::dfid' => $dfid, '::wtid' => $wtid));
                }
            }
        }

        return true;
    }

    /**
     * Check if a filter is mandatory for a widget type
     * @param int $dfid Filter ID
     * @param int $wtid Widget Type ID
     * @return bool True if mandatory
     */
    public static function isMandatory($dfid, $wtid)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT fwtmid FROM " . SystemTables::DB_TBL_FILTER_WIDGET_TYPE_MANDATORY . "
                WHERE dfid = '::dfid' AND wtid = '::wtid' LIMIT 1";
        $res = $db->query($sql, array('::dfid' => intval($dfid), '::wtid' => intval($wtid)));
        return $db->resultNumRows($res) > 0;
    }

    /**
     * Check if a filter is mandatory for a widget type by slug
     * @param int $dfid Filter ID
     * @param string $slug Widget type slug
     * @return bool True if mandatory
     */
    public static function isMandatoryForSlug($dfid, $slug)
    {
        $wtid = WidgetTypeManager::getIdBySlug($slug);
        if (!$wtid) {
            return false;
        }
        return self::isMandatory($dfid, $wtid);
    }

    /**
     * Add a mandatory mapping for a filter and widget type
     * @param int $dfid Filter ID
     * @param int $wtid Widget Type ID
     * @return bool Success
     */
    public static function addMandatory($dfid, $wtid)
    {
        if (self::isMandatory($dfid, $wtid)) {
            return true; // Already exists
        }

        $db = Rapidkart::getInstance()->getDB();
        $sql = "INSERT INTO " . SystemTables::DB_TBL_FILTER_WIDGET_TYPE_MANDATORY . " (dfid, wtid) VALUES ('::dfid', '::wtid')";
        $result = $db->query($sql, array('::dfid' => intval($dfid), '::wtid' => intval($wtid)));
        return $result ? true : false;
    }

    /**
     * Remove a mandatory mapping for a filter and widget type
     * @param int $dfid Filter ID
     * @param int $wtid Widget Type ID
     * @return bool Success
     */
    public static function removeMandatory($dfid, $wtid)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_FILTER_WIDGET_TYPE_MANDATORY . " WHERE dfid = '::dfid' AND wtid = '::wtid'";
        $result = $db->query($sql, array('::dfid' => intval($dfid), '::wtid' => intval($wtid)));
        return $result ? true : false;
    }

    /**
     * Delete all mandatory mappings for a filter
     * @param int $dfid Filter ID
     * @return bool Success
     */
    public static function deleteAllForFilter($dfid)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_FILTER_WIDGET_TYPE_MANDATORY . " WHERE dfid = '::dfid'";
        $result = $db->query($sql, array('::dfid' => intval($dfid)));
        return $result ? true : false;
    }

    /**
     * Validate that a query contains all mandatory filter placeholders for a widget type
     * @param string $query SQL query to validate
     * @param string $widgetTypeSlug Widget type slug (e.g., 'graph')
     * @return array Array with 'valid' => bool and 'missing' => array of missing filter keys
     */
    public static function validateMandatoryFiltersInQuery($query, $widgetTypeSlug)
    {
        $mandatoryKeys = self::getMandatoryFilterKeysForWidgetTypeSlug($widgetTypeSlug);

        if (empty($mandatoryKeys)) {
            return array('valid' => true, 'missing' => array());
        }

        $missing = array();
        foreach ($mandatoryKeys as $key) {
            // Check if the placeholder exists in the query
            // Handle both ::key and :key formats, and also _from/_to variants for date ranges
            $keyClean = ltrim($key, ':');
            $patterns = array(
                '::' . $keyClean,
                ':' . $keyClean,
                '::' . $keyClean . '_from',
                '::' . $keyClean . '_to'
            );

            $found = false;
            foreach ($patterns as $pattern) {
                if (strpos($query, $pattern) !== false) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $missing[] = $key;
            }
        }

        return array(
            'valid' => empty($missing),
            'missing' => $missing
        );
    }
}
