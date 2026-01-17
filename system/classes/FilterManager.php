<?php

/**
 * FilterManager - Centralized filter retrieval and management
 * Provides methods to get filters by various criteria
 *
 * @author Dynamic Graph Creator
 */
class FilterManager
{
    /**
     * Get all active filters
     *
     * @return array Array of Filter objects indexed by filter_key
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_FILTER . " WHERE fsid != 3 ORDER BY filter_label";
        $res = $db->query($sql);

        $filters = array();
        while ($row = $db->fetchAssoc($res)) {
            $filter = new Filter();
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
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_FILTER . " WHERE fsid != 3 ORDER BY filter_label";
        $res = $db->query($sql);

        $filters = array();
        while ($row = $db->fetchAssoc($res)) {
            $filter = new Filter();
            $filter->parse((object)$row);
            $filters[] = $filter->toArray();
        }
        return $filters;
    }

    /**
     * Get a single filter by ID
     *
     * @param int $id Filter ID
     * @return Filter|null Filter object or null if not found
     */
    public static function getById($id)
    {
        if (!Filter::isExistent($id)) {
            return null;
        }
        return new Filter($id);
    }

    /**
     * Get filters by their IDs, maintaining order and indexed by filter_key
     *
     * @param array $ids Array of filter IDs
     * @return array Array of Filter objects indexed by filter_key, in order of IDs provided
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

        $sql = "SELECT * FROM " . SystemTables::DB_TBL_FILTER . "
                WHERE fid IN (" . implode(',', $placeholders) . ") AND fsid != 3";
        $res = $db->query($sql, $args);

        // First, collect all filters indexed by fid
        $filtersByFid = array();
        while ($row = $db->fetchAssoc($res)) {
            $filter = new Filter();
            $filter->parse((object)$row);
            $filtersByFid[$row['fid']] = $filter;
        }

        // Build result array in order of IDs, indexed by filter_key
        $filters = array();
        foreach ($ids as $id) {
            if (isset($filtersByFid[$id])) {
                $filter = $filtersByFid[$id];
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
     * @return array Array of Filter objects indexed by filter_key
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

        $sql = "SELECT * FROM " . SystemTables::DB_TBL_FILTER . "
                WHERE filter_key IN (" . implode(',', $placeholders) . ") AND fsid != 3";
        $res = $db->query($sql, $args);

        $filters = array();
        while ($row = $db->fetchAssoc($res)) {
            $filter = new Filter();
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
     * Extract placeholders from a SQL query and get matching filters
     *
     * @param string $query SQL query string
     * @return array Array of Filter objects indexed by filter_key
     */
    public static function getFromQuery($query)
    {
        $placeholders = Filter::extractPlaceholders($query);
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
