<?php

require_once __DIR__ . '/element/ElementManager.php';

/**
 * WidgetCounterManager - Counter-specific manager
 * Extends ElementManager with counter-specific functionality
 *
 * @author Dynamic Graph Creator
 */
class WidgetCounterManager extends ElementManager
{
    /**
     * Get the Element class name
     * @return string
     */
    protected static function getElementClass()
    {
        return 'WidgetCounter';
    }

    /**
     * Get all counters with their category mappings
     * @return array Array of counter data with categories
     */
    public static function getAllWithCategories()
    {
        $counters = static::getAll();
        $result = array();

        foreach ($counters as $counter) {
            $data = $counter->toArray();

            // Get category IDs for this counter
            $categoryIds = WidgetCounterCategoryMappingManager::getCategoryIdsForCounter($counter->getId());
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
     * Get counters for widget selector (with categories)
     * @return array
     */
    public static function getForWidgetSelector()
    {
        return static::getAllWithCategories();
    }
}
