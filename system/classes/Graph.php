<?php

require_once __DIR__ . '/element/Element.php';

/**
 * Graph model - Chart element type
 * Extends Element with graph-specific functionality
 *
 * @author Dynamic Graph Creator
 */
class Graph extends Element
{
    // Graph-specific property
    protected $graph_type;

    // Alias for backward compatibility (gid → id)
    protected $gid;

    /**
     * Get database table name
     * @return string
     */
    public static function getTableName()
    {
        return SystemTables::DB_TBL_GRAPH;
    }

    /**
     * Get primary key column name
     * @return string
     */
    public static function getPrimaryKeyName()
    {
        return 'gid';
    }

    /**
     * Get status column name
     * @return string
     */
    public static function getStatusColumnName()
    {
        return 'gsid';
    }

    /**
     * Get element type identifier
     * @return string
     */
    public static function getElementType()
    {
        return 'graph';
    }

    /**
     * Check mandatory data - includes graph_type
     * @return bool
     */
    public function hasMandatoryData()
    {
        return parent::hasMandatoryData() && !empty($this->graph_type);
    }

    /**
     * Get graph-specific insert data
     * @return array
     */
    protected function getTypeSpecificInsertData()
    {
        return array(
            'columns' => array('graph_type'),
            'values' => array("'::graph_type'"),
            'args' => array('::graph_type' => $this->graph_type)
        );
    }

    /**
     * Get graph-specific update data
     * @return array
     */
    protected function getTypeSpecificUpdateData()
    {
        return array(
            'set' => "graph_type = '::graph_type'",
            'args' => array('::graph_type' => $this->graph_type)
        );
    }

    /**
     * Parse database row - handle gid alias
     * @param object $obj Database row
     * @return bool
     */
    public function parse($obj)
    {
        $result = parent::parse($obj);

        // Sync gid alias with id for backward compatibility
        if ($this->id) {
            $this->gid = $this->id;
        }

        // Parse graph_type if present
        if (isset($obj->graph_type)) {
            $this->graph_type = $obj->graph_type;
        }

        return $result;
    }

    /**
     * Format query results for chart rendering
     * @param array $rows Query result rows
     * @param array $mapping Data mapping configuration
     * @return array Chart data
     */
    protected function formatData($rows, $mapping)
    {
        if ($this->graph_type === 'pie') {
            $nameCol = isset($mapping['name_column']) ? $mapping['name_column'] : '';
            $valueCol = isset($mapping['value_column']) ? $mapping['value_column'] : '';

            $items = array();
            foreach ($rows as $row) {
                $items[] = array(
                    'name' => isset($row[$nameCol]) ? $row[$nameCol] : '',
                    'value' => isset($row[$valueCol]) ? floatval($row[$valueCol]) : 0
                );
            }
            return array('items' => $items);
        } else {
            // Bar/Line chart
            $xCol = isset($mapping['x_column']) ? $mapping['x_column'] : '';
            $yCol = isset($mapping['y_column']) ? $mapping['y_column'] : '';

            $categories = array();
            $values = array();

            foreach ($rows as $row) {
                $categories[] = isset($row[$xCol]) ? $row[$xCol] : '';
                $values[] = isset($row[$yCol]) ? floatval($row[$yCol]) : 0;
            }

            return array('categories' => $categories, 'values' => $values);
        }
    }

    /**
     * Get empty chart data structure
     * @param string|null $error Error message
     * @return array Empty chart data
     */
    protected function getEmptyData($error = null)
    {
        $result = array();

        if ($this->graph_type === 'pie') {
            $result['items'] = array();
        } else {
            $result['categories'] = array();
            $result['values'] = array();
        }

        if ($error) {
            $result['error'] = $error;
        }

        return $result;
    }

    /**
     * Convert to array - includes graph-specific data
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['gid'] = $this->id; // Backward compatibility
        $data['graph_type'] = $this->graph_type;
        return $data;
    }

    /**
     * Get ID (backward compatibility - returns gid)
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    // Graph-specific getters and setters

    public function getGraphType() { return $this->graph_type; }
    public function setGraphType($value) { $this->graph_type = $value; }
}
