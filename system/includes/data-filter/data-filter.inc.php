<?php

/**
 * DataFilter Controller
 * Handles all data-filter-related actions
 */

// Require admin access (company 232 + admin user)
DGCHelper::requireAdminAccess();

// Load data-filter module assets (common assets loaded in index.php)
LocalUtility::addModuleCss('data-filter');
LocalUtility::addModuleJs('data-filter');

// $url is already parsed in index.php
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
        case 'get_system_placeholders':
            getSystemPlaceholders();
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

    // Add page-specific JS
    LocalUtility::addPageScript('data-filter', 'data-filter-list');

    $theme->setPageTitle('Data Filters - Dynamic Graph Creator');

    $tpl = new Template(SystemConfig::templatesPath() . 'data-filter/views/data-filter-list');
    $tpl->filters = DataFilterManager::getAllAsArray();
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show data filter add/edit form
 */
function showDataFilterForm($filterId = null)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/css/codemirror.min.css', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/css/material.min.css', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/js/codemirror.min.js', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/js/sql.min.js', 7);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery3/jquery.min.js', 4);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment-dgc/moment.min.js', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/js/daterangepicker.min.js', 8);

    $filter = null;

    if ($filterId) {
        $filter = new DataFilter($filterId);
        if (!$filter->getId()) {
            LocalUtility::redirect('data-filter');
            return;
        }
    }

    // Get all filters for sidebar navigation
    $allFilters = DataFilterManager::getAllAsArray();
    $totalFilters = count($allFilters);

    $theme->setPageTitle(($filter ? 'Edit' : 'Create') . ' Data Filter - Dynamic Graph Creator');

    $tpl = new Template(SystemConfig::templatesPath() . 'data-filter/forms/data-filter-form');
    $tpl->filter = $filter;
    $tpl->allFilters = $allFilters;
    $tpl->totalFilters = $totalFilters;
    $tpl->systemPlaceholders = SystemPlaceholderManager::getAllAsArray();
    $tpl->widgetTypes = WidgetTypeManager::getAllAsArray();
    $theme->setContent('full_main', $tpl->parse());
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

    // Validate data_query if provided (for query-based filters)
    $dataQuery = isset($data['data_query']) ? trim($data['data_query']) : '';
    if (!empty($dataQuery)) {
        if (!QueryHelper::validateOrFail($dataQuery)) {
            return; // Error response already sent
        }
    }

    $filter->setFilterKey($filterKey);
    $filter->setFilterLabel($filterLabel);
    $filter->setFilterType($filterType);
    $filter->setDataSource($dataSource);
    $filter->setDataQuery($dataQuery);
    $filter->setStaticOptions(isset($data['static_options']) ? $data['static_options'] : '');
    $filter->setFilterConfig(isset($data['filter_config']) ? $data['filter_config'] : '');
    $filter->setDefaultValue(isset($data['default_value']) ? $data['default_value'] : '');
    $filter->setIsRequired(isset($data['is_required']) ? intval($data['is_required']) : 0);
    $filter->setIsSystem(isset($data['is_system']) ? intval($data['is_system']) : 0);

    if ($filterId) {
        if (!$filter->update()) {
            Utility::ajaxResponseFalse('Failed to update data filter');
        }
    } else {
        if (!$filter->insert()) {
            Utility::ajaxResponseFalse('Failed to create data filter');
        }
    }

    // Save mandatory widget types
    $mandatoryWidgetTypes = isset($data['mandatory_widget_types']) ? $data['mandatory_widget_types'] : array();
    if (is_string($mandatoryWidgetTypes)) {
        $mandatoryWidgetTypes = json_decode($mandatoryWidgetTypes, true);
        if (!is_array($mandatoryWidgetTypes)) {
            $mandatoryWidgetTypes = array();
        }
    }
    FilterWidgetTypeMandatoryManager::setMandatoryForFilter($filter->getId(), $mandatoryWidgetTypes);

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
 * System placeholders (like ::logged_in_uid) are resolved before testing
 * Supports pagination with page parameter (100 records per page)
 */
function testDataFilterQuery($data)
{
    $query = isset($data['query']) ? trim($data['query']) : '';
    $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
    $pageSize = BaseConfig::QUERY_RESULT_PAGE_SIZE;

    if (empty($query)) {
        Utility::ajaxResponseFalse('Please enter a SQL query');
    }

    // Validate query security (SELECT only)
    if (!QueryHelper::validateOrFail($query)) {
        return; // Error response already sent
    }

    // Find all placeholders in the query (::placeholder_name pattern)
    preg_match_all('/::([a-zA-Z_][a-zA-Z0-9_]*)/', $query, $matches);
    $usedPlaceholders = isset($matches[1]) ? array_unique($matches[1]) : array();

    // Get all valid system placeholder keys
    $systemPlaceholderKeys = SystemPlaceholderManager::getAllKeys();

    // Check for unknown placeholders
    $unknownPlaceholders = array();
    foreach ($usedPlaceholders as $placeholder) {
        if (!in_array($placeholder, $systemPlaceholderKeys)) {
            $unknownPlaceholders[] = '::' . $placeholder;
        }
    }

    if (!empty($unknownPlaceholders)) {
        $placeholderList = implode(', ', $unknownPlaceholders);
        Utility::ajaxResponseFalse('Unknown placeholder(s) found: ' . $placeholderList . '. Please use only valid system placeholders.');
    }

    // Resolve system placeholders before testing
    $resolvedQuery = SystemPlaceholderManager::resolveInQuery($query);

    // Check if user provided a LIMIT clause - respect it as maximum results
    $userLimit = null;
    $maxLimit = 1000; // Safety limit
    if (preg_match('/\s+LIMIT\s+(\d+)(?:\s*,\s*(\d+))?/i', $resolvedQuery, $limitMatches)) {
        $userLimit = isset($limitMatches[2]) ? intval($limitMatches[2]) : intval($limitMatches[1]);
        $userLimit = min($userLimit, $maxLimit); // Cap at safety limit
    }

    // Remove existing LIMIT clause for base query
    $baseQuery = preg_replace('/\s+LIMIT\s+\d+(\s*,\s*\d+)?/i', '', $resolvedQuery);

    $db = Rapidkart::getInstance()->getDB();

    // Try to execute the query with error handling
    try {
        // Get total count first
        $countQuery = "SELECT COUNT(*) as total FROM (" . $baseQuery . ") as subquery";
        $countRes = $db->query($countQuery);
        $totalCount = 0;
        if ($countRes && $countRow = $db->fetchAssocArray($countRes)) {
            $totalCount = intval($countRow['total']);
        }

        // If user provided LIMIT, cap the total count to that limit
        if ($userLimit !== null) {
            $totalCount = min($totalCount, $userLimit);
        }

        // Add pagination LIMIT (respecting user's limit if provided)
        $offset = ($page - 1) * $pageSize;
        $effectivePageSize = $pageSize;

        // If user provided LIMIT, ensure we don't exceed it
        if ($userLimit !== null) {
            $remainingRows = max(0, $userLimit - $offset);
            $effectivePageSize = min($pageSize, $remainingRows);
        }

        $testQuery = $baseQuery . " LIMIT " . $offset . ", " . $effectivePageSize;

        $res = $db->query($testQuery);

        if (!$res) {
            $error = $db->getMysqlError();
            Utility::ajaxResponseFalse('Query error: ' . $error);
        }
    } catch (mysqli_sql_exception $e) {
        Utility::ajaxResponseFalse('Query error: ' . $e->getMessage());
    } catch (Exception $e) {
        Utility::ajaxResponseFalse('Query error: ' . $e->getMessage());
    }

    $options = array();
    $columns = array();

    $rows = array();
    while ($row = $db->fetchAssocArray($res)) {
        if (empty($columns)) {
            $columns = array_keys($row);
        }
        // Store full row data for display
        $rows[] = $row;

        // Extract value/label/is_selected for filter preview
        $option = array(
            'value' => isset($row['value']) ? $row['value'] : '',
            'label' => isset($row['label']) ? $row['label'] : (isset($row['value']) ? $row['value'] : '')
        );
        if (isset($row['is_selected'])) {
            $isSelected = $row['is_selected'];
            $option['is_selected'] = ($isSelected === 1 || $isSelected === '1' || $isSelected === true || $isSelected === 'true' || $isSelected === 'yes');
        }
        $options[] = $option;
    }

    if (empty($options) && $page === 1) {
        Utility::ajaxResponseFalse('Query returned no results');
    }

    // Check if required columns exist
    $hasValue = in_array('value', $columns);
    $hasLabel = in_array('label', $columns);
    $hasIsSelected = in_array('is_selected', $columns);
    $warnings = array();

    if (!$hasValue && $page === 1) {
        $warnings[] = "No 'value' column found. Using first column.";
    }
    if (!$hasLabel && $page === 1) {
        $warnings[] = "No 'label' column found. Using 'value' for labels.";
    }

    $totalPages = ceil($totalCount / $pageSize);

    Utility::ajaxResponseTrue('Query is valid', array(
        'columns' => $columns,
        'rows' => $rows,
        'options' => $options,
        'count' => count($options),
        'totalCount' => $totalCount,
        'page' => $page,
        'pageSize' => $pageSize,
        'totalPages' => $totalPages,
        'warnings' => $warnings,
        'hasIsSelected' => $hasIsSelected,
        'resolvedQuery' => $testQuery
    ));
}

/**
 * Get all system placeholders (AJAX endpoint)
 */
function getSystemPlaceholders()
{
    $placeholders = SystemPlaceholderManager::getAllAsArray();
    Utility::ajaxResponseTrue('System placeholders loaded', array('placeholders' => $placeholders));
}
