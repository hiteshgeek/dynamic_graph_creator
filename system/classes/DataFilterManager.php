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
        while ($row = $db->fetchAssoc($res)) {
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
        while ($row = $db->fetchAssoc($res)) {
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
        while ($row = $db->fetchAssoc($res)) {
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
     *
     * @param array $keys Array of filter keys like ['::year', '::date_from']
     * @return array Array of DataFilter objects indexed by filter_key
     */
    public static function getByKeys($keys)
    {
        if (empty($keys)) {
            return array();
        }

        $db = Rapidkart::getInstance()->getDB();

        // Build placeholders for IN clause
        $placeholders = array();
        $args = array();
        foreach ($keys as $i => $key) {
            $placeholders[] = "'::key{$i}'";
            $args["::key{$i}"] = $key;
        }

        $sql = "SELECT * FROM " . SystemTables::DB_TBL_DATA_FILTER . "
                WHERE filter_key IN (" . implode(',', $placeholders) . ") AND dfsid != 3";
        $res = $db->query($sql, $args);

        $filters = array();
        while ($row = $db->fetchAssoc($res)) {
            $filter = new DataFilter();
            $filter->parse((object)$row);
            $filters[$row['filter_key']] = $filter;
        }
        return $filters;
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
        return $db->numRows($res) > 0;
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

        while ($row = $db->fetchAssoc($res)) {
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
     *
     * @param string $query SQL query string
     * @return array Array of placeholder keys found
     */
    public static function extractPlaceholders($query)
    {
        $placeholders = array();
        if (preg_match_all('/::([a-zA-Z_][a-zA-Z0-9_]*)/', $query, $matches)) {
            foreach ($matches[0] as $match) {
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
}
