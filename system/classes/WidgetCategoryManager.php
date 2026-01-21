<?php

/**
 * WidgetCategoryManager - Centralized widget category retrieval and management
 * Provides methods to get categories by various criteria
 *
 * @author Dynamic Graph Creator
 */
class WidgetCategoryManager
{
    /**
     * Get all active widget categories
     * @return array Array of WidgetCategory objects
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_WIDGET_CATEGORY . " WHERE wcsid != 3 ORDER BY display_order ASC, name ASC";
        $res = $db->query($sql);

        $categories = array();
        while ($row = $db->fetchObject($res)) {
            $category = new WidgetCategory();
            $category->parse($row);
            $categories[] = $category;
        }
        return $categories;
    }

    /**
     * Get a single category by ID
     * @param int $id Category ID
     * @return WidgetCategory|null
     */
    public static function getById($id)
    {
        if (!WidgetCategory::isExistent($id)) {
            return null;
        }
        return new WidgetCategory($id);
    }

    /**
     * Get all active categories as array data
     * @return array Array of category arrays
     */
    public static function getAllAsArray()
    {
        $categories = self::getAll();
        $result = array();
        foreach ($categories as $category) {
            $result[] = $category->toArray();
        }
        return $result;
    }
}
