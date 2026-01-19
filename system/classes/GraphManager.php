<?php

/**
 * GraphManager - Centralized graph retrieval and management
 * Provides methods to get graphs by various criteria
 *
 * @author Dynamic Graph Creator
 */
class GraphManager
{
    /**
     * Get all active graphs
     * @return array Array of Graph objects
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_GRAPH . " WHERE gsid != 3 ORDER BY updated_ts DESC";
        $res = $db->query($sql);

        $graphs = array();
        while ($row = $db->fetchObject($res)) {
            $graph = new Graph();
            $graph->parse($row);
            $graphs[] = $graph;
        }
        return $graphs;
    }

    /**
     * Get a single graph by ID
     * @param int $id Graph ID
     * @return Graph|null
     */
    public static function getById($id)
    {
        if (!Graph::isExistent($id)) {
            return null;
        }
        return new Graph($id);
    }

    /**
     * Get all active graphs as array data
     * @return array Array of graph arrays
     */
    public static function getAllAsArray()
    {
        $graphs = self::getAll();
        $result = array();
        foreach ($graphs as $graph) {
            $result[] = $graph->toArray();
        }
        return $result;
    }
}
