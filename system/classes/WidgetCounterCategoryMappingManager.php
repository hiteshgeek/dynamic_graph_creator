<?php

/**
 * WidgetCounterCategoryMappingManager - Manages counter-category relationships
 * Provides methods to manage the many-to-many relationship between counters and widget categories
 *
 * @author Dynamic Graph Creator
 */
class WidgetCounterCategoryMappingManager
{
    /**
     * Get all categories for a counter
     * @param int $counterId Counter ID
     * @return array Array of WidgetCategory objects
     */
    public static function getCategoriesForCounter($counterId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT wc.* FROM " . SystemTables::DB_TBL_WIDGET_CATEGORY . " wc
                INNER JOIN " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " cwcm ON wc.wcid = cwcm.wcid
                WHERE cwcm.cid = '::cid' AND wc.wcsid != 3
                ORDER BY wc.display_order ASC, wc.name ASC";
        $res = $db->query($sql, array('::cid' => intval($counterId)));

        $categories = array();
        while ($row = $db->fetchObject($res)) {
            $category = new WidgetCategory();
            $category->parse($row);
            $categories[] = $category;
        }
        return $categories;
    }

    /**
     * Get all counters for a category
     * @param int $categoryId Category ID
     * @return array Array of WidgetCounter objects
     */
    public static function getCountersForCategory($categoryId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT c.* FROM " . SystemTables::DB_TBL_COUNTER . " c
                INNER JOIN " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " cwcm ON c.cid = cwcm.cid
                WHERE cwcm.wcid = '::wcid' AND c.csid != 3
                ORDER BY c.updated_ts DESC";
        $res = $db->query($sql, array('::wcid' => intval($categoryId)));

        $counters = array();
        while ($row = $db->fetchObject($res)) {
            $counter = new WidgetCounter();
            $counter->parse($row);
            $counters[] = $counter;
        }
        return $counters;
    }

    /**
     * Get category IDs for a counter
     * @param int $counterId Counter ID
     * @return array Array of category IDs
     */
    public static function getCategoryIdsForCounter($counterId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT cwcm.wcid FROM " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " cwcm
                INNER JOIN " . SystemTables::DB_TBL_WIDGET_CATEGORY . " wc ON cwcm.wcid = wc.wcid
                WHERE cwcm.cid = '::cid' AND wc.wcsid != 3";
        $res = $db->query($sql, array('::cid' => intval($counterId)));

        $ids = array();
        while ($row = $db->fetchAssocArray($res)) {
            $ids[] = intval($row['wcid']);
        }
        return $ids;
    }

    /**
     * Set categories for a counter (replaces all existing mappings)
     * @param int $counterId Counter ID
     * @param array $categoryIds Array of category IDs
     * @return bool Success
     */
    public static function setCounterCategories($counterId, $categoryIds)
    {
        $db = Rapidkart::getInstance()->getDB();
        $counterId = intval($counterId);

        // Delete all existing mappings for this counter
        $sql = "DELETE FROM " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " WHERE cid = '::cid'";
        $db->query($sql, array('::cid' => $counterId));

        // Insert new mappings
        if (!empty($categoryIds) && is_array($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $categoryId = intval($categoryId);
                if ($categoryId > 0) {
                    $mapping = new WidgetCounterCategoryMapping();
                    $mapping->setCid($counterId);
                    $mapping->setWcid($categoryId);
                    $mapping->insert();
                }
            }
        }

        return true;
    }

    /**
     * Add a single category mapping for a counter
     * @param int $counterId Counter ID
     * @param int $categoryId Category ID
     * @return bool Success
     */
    public static function addCounterCategory($counterId, $categoryId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $counterId = intval($counterId);
        $categoryId = intval($categoryId);

        // Check if mapping already exists
        $sql = "SELECT cwcmid FROM " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . "
                WHERE cid = '::cid' AND wcid = '::wcid' LIMIT 1";
        $res = $db->query($sql, array('::cid' => $counterId, '::wcid' => $categoryId));

        if ($db->resultNumRows($res) > 0) {
            return true; // Already exists
        }

        $mapping = new WidgetCounterCategoryMapping();
        $mapping->setCid($counterId);
        $mapping->setWcid($categoryId);
        return $mapping->insert();
    }

    /**
     * Remove a single category mapping for a counter
     * @param int $counterId Counter ID
     * @param int $categoryId Category ID
     * @return bool Success
     */
    public static function removeCounterCategory($counterId, $categoryId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . "
                WHERE cid = '::cid' AND wcid = '::wcid'";
        $result = $db->query($sql, array('::cid' => intval($counterId), '::wcid' => intval($categoryId)));
        return $result ? true : false;
    }

    /**
     * Delete all category mappings for a counter
     * @param int $counterId Counter ID
     * @return bool Success
     */
    public static function deleteAllForCounter($counterId)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "DELETE FROM " . SystemTables::DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING . " WHERE cid = '::cid'";
        $result = $db->query($sql, array('::cid' => intval($counterId)));
        return $result ? true : false;
    }
}
