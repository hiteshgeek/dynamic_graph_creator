<?php

/**
 * ElementManager - Abstract base class for element managers
 * Provides common CRUD and retrieval methods
 *
 * @author Dynamic Graph Creator
 */
abstract class ElementManager
{
    /**
     * Get the Element class name for this manager
     * @return string Class name (e.g., 'Graph', 'Table')
     */
    abstract protected static function getElementClass();

    /**
     * Get all active elements
     * @return array Array of Element objects
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $elementClass = static::getElementClass();

        $table = $elementClass::getTableName();
        $statusCol = $elementClass::getStatusColumnName();

        $sql = "SELECT * FROM {$table} WHERE {$statusCol} != 3 ORDER BY updated_ts DESC";
        $res = $db->query($sql);

        $elements = array();
        while ($row = $db->fetchObject($res)) {
            $element = new $elementClass();
            $element->parse($row);
            $elements[] = $element;
        }
        return $elements;
    }

    /**
     * Get a single element by ID
     * @param int $id Element ID
     * @return Element|null
     */
    public static function getById($id)
    {
        $elementClass = static::getElementClass();

        if (!$elementClass::isExistent($id)) {
            return null;
        }
        return new $elementClass($id);
    }

    /**
     * Get multiple elements by IDs
     * @param array $ids Array of element IDs
     * @return array Array of Element objects
     */
    public static function getByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $db = Rapidkart::getInstance()->getDB();
        $elementClass = static::getElementClass();

        $table = $elementClass::getTableName();
        $pk = $elementClass::getPrimaryKeyName();
        $statusCol = $elementClass::getStatusColumnName();

        // Sanitize IDs
        $sanitizedIds = array_map('intval', $ids);
        $idList = implode(',', $sanitizedIds);

        $sql = "SELECT * FROM {$table} WHERE {$pk} IN ({$idList}) AND {$statusCol} != 3 ORDER BY FIELD({$pk}, {$idList})";
        $res = $db->query($sql);

        $elements = array();
        while ($row = $db->fetchObject($res)) {
            $element = new $elementClass();
            $element->parse($row);
            $elements[] = $element;
        }
        return $elements;
    }

    /**
     * Get all active elements as array data
     * @return array Array of element arrays
     */
    public static function getAllAsArray()
    {
        $elements = static::getAll();
        $result = array();
        foreach ($elements as $element) {
            $result[] = $element->toArray();
        }
        return $result;
    }

    /**
     * Get elements with their category mappings (for widget selector)
     * @return array Array of element data with categories
     */
    public static function getAllWithCategories()
    {
        $elements = static::getAll();
        $result = array();

        foreach ($elements as $element) {
            $data = $element->toArray();
            // Subclasses can override to add category data
            $result[] = $data;
        }

        return $result;
    }

    /**
     * Search elements by name or description
     * @param string $query Search query
     * @return array Array of Element objects
     */
    public static function search($query)
    {
        if (empty($query)) {
            return static::getAll();
        }

        $db = Rapidkart::getInstance()->getDB();
        $elementClass = static::getElementClass();

        $table = $elementClass::getTableName();
        $statusCol = $elementClass::getStatusColumnName();

        $sql = "SELECT * FROM {$table}
                WHERE {$statusCol} != 3
                AND (name LIKE '::query' OR description LIKE '::query')
                ORDER BY updated_ts DESC";

        $res = $db->query($sql, array('::query' => '%' . $query . '%'));

        $elements = array();
        while ($row = $db->fetchObject($res)) {
            $element = new $elementClass();
            $element->parse($row);
            $elements[] = $element;
        }
        return $elements;
    }

    /**
     * Count all active elements
     * @return int
     */
    public static function count()
    {
        $db = Rapidkart::getInstance()->getDB();
        $elementClass = static::getElementClass();

        $table = $elementClass::getTableName();
        $statusCol = $elementClass::getStatusColumnName();

        $sql = "SELECT COUNT(*) as cnt FROM {$table} WHERE {$statusCol} != 3";
        $res = $db->query($sql);
        $row = $db->fetchObject($res);

        return $row ? intval($row->cnt) : 0;
    }
}
