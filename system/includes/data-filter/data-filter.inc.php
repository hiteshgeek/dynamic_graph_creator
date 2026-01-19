<?php

/**
 * DataFilter Controller
 * Handles all data-filter-related actions
 */

$url = Utility::parseUrl();
$action = isset($url[1]) ? $url[1] : 'list';

// Handle POST actions
if (isset($_POST['submit'])) {
    switch ($_POST['submit']) {
        case 'save_data_filter':
            saveDataFilter($_POST);
            break;
        case 'get_data_filter':
            getDataFilter($_POST);
            break;
        case 'delete_data_filter':
            deleteDataFilter($_POST);
            break;
        case 'test_data_filter_query':
            testDataFilterQuery($_POST);
            break;
    }
}

// Handle GET actions
switch ($action) {
    case 'create':
        showDataFilterForm();
        break;
    case 'edit':
        $filterId = isset($url[2]) ? intval($url[2]) : 0;
        showDataFilterForm($filterId);
        break;
    case 'list':
    default:
        showDataFilterList();
        break;
}

/**
 * Show data filter list page (all independent filters)
 */
function showDataFilterList()
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('data-filter');

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('data-filter');
    $theme->addScript(SystemConfig::scriptsUrl() . 'data-filter/data-filter-list.js');

    $theme->setPageTitle('Data Filters - Dynamic Graph Creator');

    $filters = DataFilterManager::getAllAsArray();

    ob_start();
    require_once SystemConfig::templatesPath() . 'data-filter/data-filter-list.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Show data filter add/edit form
 */
function showDataFilterForm($filterId = null)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('data-filter');

    // Add libraries
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror/css/codemirror.min.css', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror/css/material.min.css', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror/js/codemirror.min.js', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror/js/sql.min.js', 7);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery/jquery.min.js', 4);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment/moment.min.js', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker/js/daterangepicker.min.js', 8);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('data-filter');

    $filter = null;

    if ($filterId) {
        $filter = new DataFilter($filterId);
        if (!$filter->getId()) {
            Utility::redirect('data-filter');
            return;
        }
    }

    // Get all filters for sidebar navigation
    $allFilters = DataFilterManager::getAllAsArray();
    $totalFilters = count($allFilters);

    $theme->setPageTitle(($filter ? 'Edit' : 'Create') . ' Data Filter - Dynamic Graph Creator');

    ob_start();
    require_once SystemConfig::templatesPath() . 'data-filter/data-filter-form.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Save data filter (create or update)
 * DataFilters are independent - not tied to any graph
 */
function saveDataFilter($data)
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
    if (DataFilterManager::keyExists($filterKey, $filterId ?: null)) {
        Utility::ajaxResponseFalse('A filter with this placeholder key already exists. Please use a unique key.');
    }

    // Check for substring conflicts with other filter keys
    // e.g., ::category and ::category_checkbox would conflict
    $conflict = DataFilterManager::checkKeyConflict($filterKey, $filterId ?: null);
    if ($conflict) {
        Utility::ajaxResponseFalse($conflict['message']);
    }

    $filter = $filterId ? new DataFilter($filterId) : new DataFilter();

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
            Utility::ajaxResponseFalse('Failed to update data filter');
        }
    } else {
        if (!$filter->insert()) {
            Utility::ajaxResponseFalse('Failed to create data filter');
        }
    }

    Utility::ajaxResponseTrue('Data filter saved successfully', array('id' => $filter->getId()));
}

/**
 * Get single data filter for editing
 */
function getDataFilter($data)
{
    $filterId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$filterId) {
        Utility::ajaxResponseFalse('Invalid filter ID');
    }

    $filter = new DataFilter($filterId);
    if (!$filter->getId()) {
        Utility::ajaxResponseFalse('Data filter not found');
    }

    Utility::ajaxResponseTrue('Data filter loaded', $filter->toArray());
}

/**
 * Delete data filter
 */
function deleteDataFilter($data)
{
    $filterId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$filterId || !DataFilter::delete($filterId)) {
        Utility::ajaxResponseFalse('Failed to delete data filter');
    }

    Utility::ajaxResponseTrue('Data filter deleted successfully');
}

/**
 * Test data filter query (for query-based filter options)
 * Query should return 'value' and 'label' columns
 * Optional 'is_selected' column (1/0) to pre-select options
 */
function testDataFilterQuery($data)
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
