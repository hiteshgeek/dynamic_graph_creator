<?php

require_once __DIR__ . '/element/Element.php';

/**
 * Counter model - Counter element type
 * Extends Element with counter-specific functionality
 *
 * Counter config includes:
 * - icon: Material Design icon name
 * - color: Background color (hex)
 * - format: Number format type (number, currency, percentage, compact)
 * - prefix: Text prefix before value
 * - suffix: Text suffix after value
 * - decimals: Number of decimal places
 *
 * @author Dynamic Graph Creator
 */
class Counter extends Element
{
    // Alias for backward compatibility (cid → id)
    protected $cid;

    /**
     * Get database table name
     * @return string
     */
    public static function getTableName()
    {
        return SystemTables::DB_TBL_COUNTER;
    }

    /**
     * Get primary key column name
     * @return string
     */
    public static function getPrimaryKeyName()
    {
        return 'cid';
    }

    /**
     * Get status column name
     * @return string
     */
    public static function getStatusColumnName()
    {
        return 'csid';
    }

    /**
     * Get element type identifier
     * @return string
     */
    public static function getElementType()
    {
        return 'counter';
    }

    /**
     * Check mandatory data - counter requires name and query
     * @return bool
     */
    public function hasMandatoryData()
    {
        return parent::hasMandatoryData();
    }

    /**
     * Get counter-specific insert data
     * Counter has no extra columns beyond Element base
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
     * Get counter-specific update data
     * Counter has no extra columns beyond Element base
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
     * Parse database row - handle cid alias
     * @param object $obj Database row
     * @return bool
     */
    public function parse($obj)
    {
        $result = parent::parse($obj);

        // Sync cid alias with id for backward compatibility
        if ($this->id) {
            $this->cid = $this->id;
        }

        return $result;
    }

    /**
     * Format query results for counter rendering
     * Expects a single row with a 'counter' column
     * @param array $rows Query result rows
     * @param array $mapping Data mapping configuration (not used for counter)
     * @return array Counter data
     */
    protected function formatData($rows, $mapping)
    {
        if (empty($rows)) {
            return array(
                'value' => 0,
                'error' => 'No data returned'
            );
        }

        $row = $rows[0];

        // Look for 'counter' key first, then any numeric value
        if (isset($row['counter'])) {
            $value = $row['counter'];
        } else {
            // Get the first column value
            $value = reset($row);
        }

        return array(
            'value' => is_numeric($value) ? floatval($value) : 0,
            'raw_value' => $value
        );
    }

    /**
     * Get empty counter data structure
     * @param string|null $error Error message
     * @return array Empty counter data
     */
    protected function getEmptyData($error = null)
    {
        $result = array(
            'value' => 0
        );

        if ($error) {
            $result['error'] = $error;
        }

        return $result;
    }

    /**
     * Convert to array - includes counter-specific data
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['cid'] = $this->id; // Backward compatibility
        return $data;
    }

    /**
     * Get ID (backward compatibility - returns cid)
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
            'icon' => 'trending_up',
            'color' => '#4CAF50',
            'format' => 'number',
            'prefix' => '',
            'suffix' => '',
            'decimals' => 0
        );
    }

    /**
     * Get available format options
     * @return array
     */
    public static function getFormatOptions()
    {
        return array(
            array('value' => 'number', 'label' => 'Number (1,234)'),
            array('value' => 'currency', 'label' => 'Currency (₹1,234)'),
            array('value' => 'percentage', 'label' => 'Percentage (12.5%)'),
            array('value' => 'compact', 'label' => 'Compact (1.2K, 1.5M)')
        );
    }
}
