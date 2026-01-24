<?php

require_once __DIR__ . '/element/ElementManager.php';

/**
 * TableManager - Table-specific manager
 * Extends ElementManager with table-specific functionality
 *
 * @author Dynamic Graph Creator
 */
class TableManager extends ElementManager
{
    /**
     * Get the Element class name
     * @return string
     */
    protected static function getElementClass()
    {
        return 'Table';
    }

    /**
     * Get all tables with their category mappings
     * @return array Array of table data with categories
     */
    public static function getAllWithCategories()
    {
        $tables = static::getAll();
        $result = array();

        foreach ($tables as $table) {
            $data = $table->toArray();

            // Get category IDs for this table
            $categoryIds = TableWidgetCategoryMappingManager::getCategoryIdsForTable($table->getId());
            $data['category_ids'] = $categoryIds;

            // Get full category data
            $categories = array();
            if (!empty($categoryIds)) {
                $allCategories = WidgetCategoryManager::getAll();
                foreach ($allCategories as $cat) {
                    if (in_array($cat->getId(), $categoryIds)) {
                        $categories[] = $cat->toArray();
                    }
                }
            }
            $data['categories'] = $categories;

            $result[] = $data;
        }

        return $result;
    }

    /**
     * Get tables for widget selector (with categories)
     * @return array
     */
    public static function getForWidgetSelector()
    {
        return static::getAllWithCategories();
    }
}
