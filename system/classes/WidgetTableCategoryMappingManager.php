<?php

/**
 * WidgetTableCategoryMappingManager - Manages table-category relationships
 * Provides methods to manage the many-to-many relationship between tables and widget categories
 *
 * @author Dynamic Graph Creator
 */
class WidgetTableCategoryMappingManager
{
    /**
     * Get all categories for a table
     * @param int $tableId Table ID
     * @return array Array of WidgetCategory objects
     */
    public static function getCategoriesForTable($tableId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT wc.* FROM " . SystemTables::DB_TBL_WIDGET_CATEGORY . " wc
                INNER JOIN " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " twcm ON wc.wcid = twcm.wcid
                WHERE twcm.tid = '::tid' AND wc.wcsid != 3
                ORDER BY wc.display_order ASC, wc.name ASC";
        $res = $db->query($sql, array('::tid' => intval($tableId)));

        $categories = array();
        while ($row = $db->fetchObject($res)) {
            $category = new WidgetCategory();
            $category->parse($row);
            $categories[] = $category;
        }
        return $categories;
    }

    /**
     * Get all tables for a category
     * @param int $categoryId Category ID
     * @return array Array of WidgetTable objects
     */
    public static function getTablesForCategory($categoryId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT t.* FROM " . SystemTables::DB_TBL_TABLE . " t
                INNER JOIN " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " twcm ON t.tid = twcm.tid
                WHERE twcm.wcid = '::wcid' AND t.tsid != 3
                ORDER BY t.updated_ts DESC";
        $res = $db->query($sql, array('::wcid' => intval($categoryId)));

        $tables = array();
        while ($row = $db->fetchObject($res)) {
            $table = new WidgetTable();
            $table->parse($row);
            $tables[] = $table;
        }
        return $tables;
    }

    /**
     * Get category IDs for a table
     * @param int $tableId Table ID
     * @return array Array of category IDs
     */
    public static function getCategoryIdsForTable($tableId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT twcm.wcid FROM " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " twcm
                INNER JOIN " . SystemTables::DB_TBL_WIDGET_CATEGORY . " wc ON twcm.wcid = wc.wcid
                WHERE twcm.tid = '::tid' AND wc.wcsid != 3";
        $res = $db->query($sql, array('::tid' => intval($tableId)));

        $ids = array();
        while ($row = $db->fetchAssocArray($res)) {
            $ids[] = intval($row['wcid']);
        }
        return $ids;
    }

    /**
     * Set categories for a table (replaces all existing mappings)
     * @param int $tableId Table ID
     * @param array $categoryIds Array of category IDs
     * @return bool Success
     */
    public static function setTableCategories($tableId, $categoryIds)
    {
        $db = Rapidkart::getInstance()->getDB();
        $tableId = intval($tableId);

        // Delete all existing mappings for this table
        $sql = "DELETE FROM " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " WHERE tid = '::tid'";
        $db->query($sql, array('::tid' => $tableId));

        // Insert new mappings
        if (!empty($categoryIds) && is_array($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $categoryId = intval($categoryId);
                if ($categoryId > 0) {
                    $mapping = new WidgetTableCategoryMapping();
                    $mapping->setTid($tableId);
                    $mapping->setWcid($categoryId);
                    $mapping->insert();
                }
            }
        }

        return true;
    }

    /**
     * Add a single category mapping for a table
     * @param int $tableId Table ID
     * @param int $categoryId Category ID
     * @return bool Success
     */
    public static function addTableCategory($tableId, $categoryId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $tableId = intval($tableId);
        $categoryId = intval($categoryId);

        // Check if mapping already exists
        $sql = "SELECT twcmid FROM " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . "
                WHERE tid = '::tid' AND wcid = '::wcid' LIMIT 1";
        $res = $db->query($sql, array('::tid' => $tableId, '::wcid' => $categoryId));

        if ($db->resultNumRows($res) > 0) {
            return true; // Already exists
        }

        $mapping = new WidgetTableCategoryMapping();
        $mapping->setTid($tableId);
        $mapping->setWcid($categoryId);
        return $mapping->insert();
    }

    /**
     * Remove a single category mapping for a table
     * @param int $tableId Table ID
     * @param int $categoryId Category ID
     * @return bool Success
     */
    public static function removeTableCategory($tableId, $categoryId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . "
                WHERE tid = '::tid' AND wcid = '::wcid'";
        $result = $db->query($sql, array('::tid' => intval($tableId), '::wcid' => intval($categoryId)));
        return $result ? true : false;
    }

    /**
     * Delete all category mappings for a table
     * @param int $tableId Table ID
     * @return bool Success
     */
    public static function deleteAllForTable($tableId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING . " WHERE tid = '::tid'";
        $result = $db->query($sql, array('::tid' => intval($tableId)));
        return $result ? true : false;
    }
}
