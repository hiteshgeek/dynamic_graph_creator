<?php

/**
 * Filter Controller
 * Handles all filter-related actions
 */

$url = Utility::parseUrl();
$action = isset($url[1]) ? $url[1] : 'list';

// Handle POST actions
if (isset($_POST['submit'])) {
    switch ($_POST['submit']) {
        case 'save_filter':
            saveFilter($_POST);
            break;
        case 'get_filter':
            getFilter($_POST);
            break;
        case 'delete_filter':
            deleteFilter($_POST);
            break;
        case 'test_filter_query':
            testFilterQuery($_POST);
            break;
    }
}

// Handle GET actions
switch ($action) {
    case 'add':
        showFilterForm();
        break;
    case 'edit':
        $filterId = isset($url[2]) ? intval($url[2]) : 0;
        showFilterForm($filterId);
        break;
    case 'list':
    default:
        showFilterList();
        break;
}

/**
 * Show filter list page (all independent filters)
 */
function showFilterList()
{
    $filters = Filter::getAll();
    require_once SystemConfig::templatesPath() . 'filter/filter-list.php';
}

/**
 * Show filter add/edit form
 */
function showFilterForm($filterId = null)
{
    $filter = null;

    if ($filterId) {
        $filter = new Filter($filterId);
        if (!$filter->getId()) {
            Utility::redirect('filters');
            return;
        }
    }

    require_once SystemConfig::templatesPath() . 'filter/filter-form.php';
}

/**
 * Save filter (create or update)
 * Filters are independent - not tied to any graph
 */
function saveFilter($data)
{
    $filterId = isset($data['filter_id']) ? intval($data['filter_id']) : 0;

    $filterKey = isset($data['filter_key']) ? trim($data['filter_key']) : '';
    $filterLabel = isset($data['filter_label']) ? trim($data['filter_label']) : '';
    $filterType = isset($data['filter_type']) ? $data['filter_type'] : 'text';
    $dataSource = isset($data['data_source']) ? $data['data_source'] : 'static';

    if (empty($filterKey)) {
        Utility::ajaxResponseFalse('Filter key is required');
    }

    if (empty($filterLabel)) {
        Utility::ajaxResponseFalse('Filter label is required');
    }

    // Ensure filter key starts with ::
    if (strpos($filterKey, '::') !== 0) {
        $filterKey = (strpos($filterKey, ':') === 0) ? ':' . $filterKey : '::' . $filterKey;
    }

    // Check if filter key already exists (excluding current filter for updates)
    if (Filter::keyExists($filterKey, $filterId ?: null)) {
        Utility::ajaxResponseFalse('A filter with this placeholder key already exists. Please use a unique key.');
    }

    // Check for substring conflicts with other filter keys
    // e.g., ::category and ::category_checkbox would conflict
    $conflict = Filter::checkKeyConflict($filterKey, $filterId ?: null);
    if ($conflict) {
        Utility::ajaxResponseFalse($conflict['message']);
    }

    $filter = $filterId ? new Filter($filterId) : new Filter();

    $filter->setFilterKey($filterKey);
    $filter->setFilterLabel($filterLabel);
    $filter->setFilterType($filterType);
    $filter->setDataSource($dataSource);
    $filter->setDataQuery(isset($data['data_query']) ? $data['data_query'] : '');
    $filter->setStaticOptions(isset($data['static_options']) ? $data['static_options'] : '');
    $filter->setFilterConfig(isset($data['filter_config']) ? $data['filter_config'] : '');
    $filter->setDefaultValue(isset($data['default_value']) ? $data['default_value'] : '');
    $filter->setIsRequired(isset($data['is_required']) ? intval($data['is_required']) : 0);

    if ($filterId) {
        if (!$filter->update()) {
            Utility::ajaxResponseFalse('Failed to update filter');
        }
    } else {
        if (!$filter->insert()) {
            Utility::ajaxResponseFalse('Failed to create filter');
        }
    }

    Utility::ajaxResponseTrue('Filter saved successfully', array('id' => $filter->getId()));
}

/**
 * Get single filter for editing
 */
function getFilter($data)
{
    $filterId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$filterId) {
        Utility::ajaxResponseFalse('Invalid filter ID');
    }

    $filter = new Filter($filterId);
    if (!$filter->getId()) {
        Utility::ajaxResponseFalse('Filter not found');
    }

    Utility::ajaxResponseTrue('Filter loaded', $filter->toArray());
}

/**
 * Delete filter
 */
function deleteFilter($data)
{
    $filterId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$filterId || !Filter::delete($filterId)) {
        Utility::ajaxResponseFalse('Failed to delete filter');
    }

    Utility::ajaxResponseTrue('Filter deleted successfully');
}

/**
 * Test filter query (for query-based filter options)
 * Query should return 'value' and 'label' columns
 * Optional 'is_selected' column (1/0) to pre-select options
 */
function testFilterQuery($data)
{
    $query = isset($data['query']) ? trim($data['query']) : '';

    if (empty($query)) {
        Utility::ajaxResponseFalse('Please enter a SQL query');
    }

    // Add LIMIT for safety
    $testQuery = preg_replace('/\s+LIMIT\s+\d+(\s*,\s*\d+)?/i', '', $query);
    $testQuery .= ' LIMIT 20';

    $db = Rapidkart::getInstance()->getDB();
    $res = $db->query($testQuery);

    if (!$res) {
        Utility::ajaxResponseFalse('Query error: ' . $db->getError());
    }

    $options = array();
    $columns = array();

    while ($row = $db->fetchAssoc($res)) {
        if (empty($columns)) {
            $columns = array_keys($row);
        }
        $option = array(
            'value' => isset($row['value']) ? $row['value'] : '',
            'label' => isset($row['label']) ? $row['label'] : (isset($row['value']) ? $row['value'] : '')
        );
        // Include is_selected if present
        if (isset($row['is_selected'])) {
            $isSelected = $row['is_selected'];
            $option['is_selected'] = ($isSelected === 1 || $isSelected === '1' || $isSelected === true || $isSelected === 'true' || $isSelected === 'yes');
        }
        $options[] = $option;
    }

    if (empty($options)) {
        Utility::ajaxResponseFalse('Query returned no results');
    }

    // Check if required columns exist
    $hasValue = in_array('value', $columns);
    $hasLabel = in_array('label', $columns);
    $hasIsSelected = in_array('is_selected', $columns);
    $warnings = array();

    if (!$hasValue) {
        $warnings[] = "No 'value' column found. Using first column.";
    }
    if (!$hasLabel) {
        $warnings[] = "No 'label' column found. Using 'value' for labels.";
    }

    Utility::ajaxResponseTrue('Query is valid', array(
        'columns' => $columns,
        'options' => $options,
        'count' => count($options),
        'warnings' => $warnings,
        'hasIsSelected' => $hasIsSelected
    ));
}
