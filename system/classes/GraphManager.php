<?php

require_once __DIR__ . '/element/ElementManager.php';

/**
 * GraphManager - Graph-specific manager
 * Extends ElementManager with graph-specific functionality
 *
 * @author Dynamic Graph Creator
 */
class GraphManager extends ElementManager
{
    /**
     * Get the Element class name
     * @return string
     */
    protected static function getElementClass()
    {
        return 'Graph';
    }

    /**
     * Get all graphs with their category mappings
     * @return array Array of graph data with categories
     */
    public static function getAllWithCategories()
    {
        $graphs = static::getAll();
        $result = array();

        foreach ($graphs as $graph) {
            $data = $graph->toArray();

            // Get category IDs for this graph
            $categoryIds = GraphWidgetCategoryMappingManager::getCategoryIdsForGraph($graph->getId());
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
     * Get graphs for widget selector (with categories)
     * @return array
     */
    public static function getForWidgetSelector()
    {
        return static::getAllWithCategories();
    }
}
