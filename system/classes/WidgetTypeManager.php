<?php

/**
 * WidgetTypeManager - Centralized widget type retrieval and management
 * Provides methods to get widget types by various criteria
 *
 * @author Dynamic Graph Creator
 */
class WidgetTypeManager
{
    /**
     * Get all active widget types
     * @return array Array of WidgetType objects
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_WIDGET_TYPE . " WHERE wtsid != 3 ORDER BY display_order ASC, name ASC";
        $res = $db->query($sql);

        $types = array();
        while ($row = $db->fetchObject($res)) {
            $type = new WidgetType();
            $type->parse($row);
            $types[] = $type;
        }
        return $types;
    }

    /**
     * Get a single widget type by ID
     * @param int $id Widget Type ID
     * @return WidgetType|null
     */
    public static function getById($id)
    {
        if (!WidgetType::isExistent($id)) {
            return null;
        }
        return new WidgetType($id);
    }

    /**
     * Get a single widget type by slug
     * @param string $slug Widget type slug (e.g., 'graph', 'link')
     * @return WidgetType|null
     */
    public static function getBySlug($slug)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_WIDGET_TYPE . " WHERE slug = '::slug' AND wtsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::slug' => $slug));

        if (!$res || $db->resultNumRows($res) < 1) {
            return null;
        }

        $type = new WidgetType();
        $type->parse($db->fetchObject($res));
        return $type;
    }

    /**
     * Get widget type ID by slug
     * @param string $slug Widget type slug
     * @return int|null Widget type ID or null if not found
     */
    public static function getIdBySlug($slug)
    {
        $type = self::getBySlug($slug);
        return $type ? $type->getId() : null;
    }

    /**
     * Get all active widget types as array data
     * @return array Array of widget type arrays
     */
    public static function getAllAsArray()
    {
        $types = self::getAll();
        $result = array();
        foreach ($types as $type) {
            $result[] = $type->toArray();
        }
        return $result;
    }

    /**
     * Get all widget types indexed by slug
     * @return array Associative array slug => WidgetType
     */
    public static function getAllBySlug()
    {
        $types = self::getAll();
        $result = array();
        foreach ($types as $type) {
            $result[$type->getSlug()] = $type;
        }
        return $result;
    }
}
