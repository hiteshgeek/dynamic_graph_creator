<?php

/**
 * GraphWidgetCategoryMappingManager - Manages graph-category relationships
 * Provides methods to manage the many-to-many relationship between graphs and widget categories
 *
 * @author Dynamic Graph Creator
 */
class GraphWidgetCategoryMappingManager
{
    /**
     * Get all categories for a graph
     * @param int $graphId Graph ID
     * @return array Array of WidgetCategory objects
     */
    public static function getCategoriesForGraph($graphId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT wc.* FROM " . SystemTables::DB_TBL_WIDGET_CATEGORY . " wc
                INNER JOIN " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " gwcm ON wc.wcid = gwcm.wcid
                WHERE gwcm.gid = '::gid' AND wc.wcsid != 3
                ORDER BY wc.display_order ASC, wc.name ASC";
        $res = $db->query($sql, array('::gid' => intval($graphId)));

        $categories = array();
        while ($row = $db->fetchObject($res)) {
            $category = new WidgetCategory();
            $category->parse($row);
            $categories[] = $category;
        }
        return $categories;
    }

    /**
     * Get all graphs for a category
     * @param int $categoryId Category ID
     * @return array Array of Graph objects
     */
    public static function getGraphsForCategory($categoryId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT g.* FROM " . SystemTables::DB_TBL_GRAPH . " g
                INNER JOIN " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " gwcm ON g.gid = gwcm.gid
                WHERE gwcm.wcid = '::wcid' AND g.gsid != 3
                ORDER BY g.updated_ts DESC";
        $res = $db->query($sql, array('::wcid' => intval($categoryId)));

        $graphs = array();
        while ($row = $db->fetchObject($res)) {
            $graph = new Graph();
            $graph->parse($row);
            $graphs[] = $graph;
        }
        return $graphs;
    }

    /**
     * Get category IDs for a graph
     * @param int $graphId Graph ID
     * @return array Array of category IDs
     */
    public static function getCategoryIdsForGraph($graphId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT gwcm.wcid FROM " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " gwcm
                INNER JOIN " . SystemTables::DB_TBL_WIDGET_CATEGORY . " wc ON gwcm.wcid = wc.wcid
                WHERE gwcm.gid = '::gid' AND wc.wcsid != 3";
        $res = $db->query($sql, array('::gid' => intval($graphId)));

        $ids = array();
        while ($row = $db->fetchAssocArray($res)) {
            $ids[] = intval($row['wcid']);
        }
        return $ids;
    }

    /**
     * Set categories for a graph (replaces all existing mappings)
     * @param int $graphId Graph ID
     * @param array $categoryIds Array of category IDs
     * @return bool Success
     */
    public static function setGraphCategories($graphId, $categoryIds)
    {
        $db = Rapidkart::getInstance()->getDB();
        $graphId = intval($graphId);

        // Delete all existing mappings for this graph
        $sql = "DELETE FROM " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " WHERE gid = '::gid'";
        $db->query($sql, array('::gid' => $graphId));

        // Insert new mappings
        if (!empty($categoryIds) && is_array($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $categoryId = intval($categoryId);
                if ($categoryId > 0) {
                    $mapping = new GraphWidgetCategoryMapping();
                    $mapping->setGid($graphId);
                    $mapping->setWcid($categoryId);
                    $mapping->insert();
                }
            }
        }

        return true;
    }

    /**
     * Add a single category mapping for a graph
     * @param int $graphId Graph ID
     * @param int $categoryId Category ID
     * @return bool Success
     */
    public static function addGraphCategory($graphId, $categoryId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $graphId = intval($graphId);
        $categoryId = intval($categoryId);

        // Check if mapping already exists
        $sql = "SELECT gwcmid FROM " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . "
                WHERE gid = '::gid' AND wcid = '::wcid' LIMIT 1";
        $res = $db->query($sql, array('::gid' => $graphId, '::wcid' => $categoryId));

        if ($db->resultNumRows($res) > 0) {
            return true; // Already exists
        }

        $mapping = new GraphWidgetCategoryMapping();
        $mapping->setGid($graphId);
        $mapping->setWcid($categoryId);
        return $mapping->insert();
    }

    /**
     * Remove a single category mapping for a graph
     * @param int $graphId Graph ID
     * @param int $categoryId Category ID
     * @return bool Success
     */
    public static function removeGraphCategory($graphId, $categoryId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . "
                WHERE gid = '::gid' AND wcid = '::wcid'";
        $result = $db->query($sql, array('::gid' => intval($graphId), '::wcid' => intval($categoryId)));
        return $result ? true : false;
    }

    /**
     * Delete all category mappings for a graph
     * @param int $graphId Graph ID
     * @return bool Success
     */
    public static function deleteAllForGraph($graphId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING . " WHERE gid = '::gid'";
        $result = $db->query($sql, array('::gid' => intval($graphId)));
        return $result ? true : false;
    }
}
