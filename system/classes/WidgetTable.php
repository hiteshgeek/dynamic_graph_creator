<?php

require_once __DIR__ . '/element/Element.php';

/**
 * WidgetTable model - Table element type
 * Extends Element with table-specific functionality
 *
 * Table config includes:
 * - columns: Array of column configurations (key, label, visible, width, align)
 * - pagination: Pagination settings (enabled, rowsPerPage, rowsPerPageOptions)
 * - style: Table style settings (striped, bordered, hover, density)
 *
 * @author Dynamic Graph Creator
 */
class WidgetTable extends Element
{
    // Alias for backward compatibility (tid → id)
    protected $tid;

    /**
     * Get database table name
     * @return string
     */
    public static function getTableName()
    {
        return SystemTables::DB_TBL_TABLE;
    }

    /**
     * Get primary key column name
     * @return string
     */
    public static function getPrimaryKeyName()
    {
        return 'tid';
    }

    /**
     * Get status column name
     * @return string
     */
    public static function getStatusColumnName()
    {
        return 'tsid';
    }

    /**
     * Get element type identifier
     * @return string
     */
    public static function getElementType()
    {
        return 'table';
    }

    /**
     * Check mandatory data - table requires name and query
     * @return bool
     */
    public function hasMandatoryData()
    {
        return parent::hasMandatoryData();
    }

    /**
     * Get table-specific insert data
     * Table has no extra columns beyond Element base
     * @return array
     */
    protected function getTypeSpecificInsertData()
    {
        return array(
            'columns' => array(),
            'values' => array(),
            'args' => array()
        );
    }

    /**
     * Get table-specific update data
     * Table has no extra columns beyond Element base
     * @return array
     */
    protected function getTypeSpecificUpdateData()
    {
        return array(
            'set' => '',
            'args' => array()
        );
    }

    /**
     * Parse database row - handle tid alias
     * @param object $obj Database row
     * @return bool
     */
    public function parse($obj)
    {
        $result = parent::parse($obj);

        // Sync tid alias with id for backward compatibility
        if ($this->id) {
            $this->tid = $this->id;
        }

        return $result;
    }

    /**
     * Format query results for table rendering
     * Returns all rows with column metadata
     * @param array $rows Query result rows
     * @param array $mapping Data mapping configuration (not used for table)
     * @return array Table data
     */
    protected function formatData($rows, $mapping)
    {
        // Extract column names from first row
        $columns = array();
        if (!empty($rows) && isset($rows[0])) {
            $columns = array_keys($rows[0]);
        }

        return array(
            'columns' => $columns,
            'rows' => $rows,
            'total_rows' => count($rows)
        );
    }

    /**
     * Get empty table data structure
     * @param string|null $error Error message
     * @return array Empty table data
     */
    protected function getEmptyData($error = null)
    {
        $result = array(
            'columns' => array(),
            'rows' => array(),
            'total_rows' => 0
        );

        if ($error) {
            $result['error'] = $error;
        }

        return $result;
    }

    /**
     * Convert to array - includes table-specific data
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['tid'] = $this->id; // Backward compatibility
        return $data;
    }

    /**
     * Get ID (backward compatibility - returns tid)
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get config as array
     * @return array
     */
    public function getConfigArray()
    {
        $config = $this->config ? json_decode($this->config, true) : array();
        return is_array($config) ? $config : array();
    }

    /**
     * Get default config values
     * @return array
     */
    public static function getDefaultConfig()
    {
        return array(
            'columns' => array(),
            'pagination' => array(
                'enabled' => true,
                'rowsPerPage' => 10,
                'rowsPerPageOptions' => array(10, 25, 50, 100)
            ),
            'style' => array(
                'striped' => true,
                'bordered' => true,
                'hover' => true,
                'density' => 'comfortable',
                'headerBackground' => '#f8f9fa'
            ),
            'features' => array(
                'search' => false,
                'export' => false,
                'columnReorder' => false
            )
        );
    }

    /**
     * Get available density options
     * @return array
     */
    public static function getDensityOptions()
    {
        return array(
            array('value' => 'compact', 'label' => 'Compact'),
            array('value' => 'comfortable', 'label' => 'Comfortable'),
            array('value' => 'spacious', 'label' => 'Spacious')
        );
    }

    /**
     * Get available rows per page options
     * @return array
     */
    public static function getRowsPerPageOptions()
    {
        return array(
            array('value' => 10, 'label' => '10 rows'),
            array('value' => 25, 'label' => '25 rows'),
            array('value' => 50, 'label' => '50 rows'),
            array('value' => 100, 'label' => '100 rows')
        );
    }
}
