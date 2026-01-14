<?php

/**
 * Graph Controller
 * Handles all graph-related actions
 */

$url = GraphUtility::parseUrl();
$action = isset($url[1]) ? $url[1] : 'list';

// Handle POST actions
if (isset($_POST['submit'])) {
    switch ($_POST['submit']) {
        case 'save_graph':
            saveGraph($_POST);
            break;
        case 'delete_graph':
            deleteGraph($_POST);
            break;
        case 'test_query':
            testQuery($_POST);
            break;
        case 'preview_graph':
            previewGraph($_POST);
            break;
        case 'load_graph':
            loadGraph($_POST);
            break;
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
    case 'create':
        showCreator();
        break;
    case 'edit':
        $graphId = isset($url[2]) ? intval($url[2]) : 0;
        showCreator($graphId);
        break;
    case 'view':
        $graphId = isset($url[2]) ? intval($url[2]) : 0;
        showView($graphId);
        break;
    case 'filters':
        $subAction = isset($url[2]) ? $url[2] : 'list';
        if ($subAction === 'add') {
            $graphId = isset($url[3]) ? intval($url[3]) : 0;
            showFilterForm($graphId);
        } elseif ($subAction === 'edit') {
            $filterId = isset($url[3]) ? intval($url[3]) : 0;
            showFilterForm(null, $filterId);
        } else {
            showFilterList();
        }
        break;
    case 'list':
    default:
        showList();
        break;
}

/**
 * Show graph list page
 */
function showList()
{
    $graphs = Graph::getAll();
    require_once GraphConfig::templatesPath() . 'graph/graph-list.php';
}

/**
 * Show graph creator/editor
 */
function showCreator($graphId = null)
{
    $graph = null;

    if ($graphId) {
        $graph = new Graph($graphId);
        if (!$graph->getId()) {
            GraphUtility::redirect('graph');
            return;
        }
    }

    // Get all available filters for selection
    $allFilters = Filter::getAll();

    require_once GraphConfig::templatesPath() . 'graph/graph-creator.php';
}

/**
 * Show graph view page
 */
function showView($graphId)
{
    $graph = new Graph($graphId);
    if (!$graph->getId()) {
        GraphUtility::redirect('graph');
        return;
    }

    // Extract placeholders from the graph query and find matching filters
    $placeholders = Filter::extractPlaceholders($graph->getQuery());
    $matchedFilters = Filter::getByKeys($placeholders);

    // Convert to array format for template
    $filters = array();
    foreach ($matchedFilters as $key => $filter) {
        $filters[] = $filter->toArray();
    }

    require_once GraphConfig::templatesPath() . 'graph/graph-view.php';
}

/**
 * Save graph (create or update)
 * Note: Filters are independent - no need to save filter associations
 * The system matches placeholders in the query to filter keys at runtime
 */
function saveGraph($data)
{
    $graphId = isset($data['id']) ? intval($data['id']) : 0;
    $isUpdate = $graphId > 0;

    $graph = $isUpdate ? new Graph($graphId) : new Graph();

    $graph->setName(isset($data['name']) ? $data['name'] : '');
    $graph->setDescription(isset($data['description']) ? $data['description'] : '');
    $graph->setGraphType(isset($data['graph_type']) ? $data['graph_type'] : 'bar');
    $graph->setQuery(isset($data['query']) ? $data['query'] : '');

    $config = isset($data['config']) ? $data['config'] : '{}';
    if (is_string($config)) {
        $graph->setConfig($config);
    } else {
        $graph->setConfig(json_encode($config));
    }

    $mapping = isset($data['data_mapping']) ? $data['data_mapping'] : '{}';
    if (is_string($mapping)) {
        $graph->setDataMapping($mapping);
    } else {
        $graph->setDataMapping(json_encode($mapping));
    }

    if ($isUpdate) {
        if (!$graph->update()) {
            GraphUtility::ajaxResponseFalse('Failed to update graph');
        }
    } else {
        if (!$graph->insert()) {
            GraphUtility::ajaxResponseFalse('Failed to create graph');
        }
    }

    $message = $isUpdate ? 'Graph updated successfully' : 'Graph created successfully';
    GraphUtility::ajaxResponseTrue($message, array('id' => $graph->getId()));
}

/**
 * Delete graph
 */
function deleteGraph($data)
{
    $graphId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$graphId || !Graph::delete($graphId)) {
        GraphUtility::ajaxResponseFalse('Failed to delete graph');
    }

    GraphUtility::ajaxResponseTrue('Graph deleted successfully');
}

/**
 * Test SQL query and return columns with sample rows
 */
function testQuery($data)
{
    $query = isset($data['query']) ? trim($data['query']) : '';

    if (empty($query)) {
        GraphUtility::ajaxResponseFalse('Please enter a SQL query');
    }

    // Replace placeholders with dummy values for testing
    $testQuery = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', "'test'", $query);

    // Remove existing LIMIT and add our own for sample rows
    $testQuery = preg_replace('/\s+LIMIT\s+\d+(\s*,\s*\d+)?/i', '', $testQuery);
    $testQuery .= ' LIMIT 10';

    $db = Rapidkart::getInstance()->getDB();
    $res = $db->query($testQuery);

    if (!$res) {
        GraphUtility::ajaxResponseFalse('Query error: ' . $db->getError());
    }

    // Fetch all sample rows
    $rows = array();
    $columns = array();

    while ($row = $db->fetchAssoc($res)) {
        if (empty($columns)) {
            $columns = array_keys($row);
        }
        $rows[] = $row;
    }

    // If no rows, try to get field info
    if (empty($columns)) {
        if (function_exists('mysqli_fetch_fields')) {
            $result = $db->query($testQuery);
            if ($result && is_object($result)) {
                $fields = mysqli_fetch_fields($result);
                foreach ($fields as $field) {
                    $columns[] = $field->name;
                }
            }
        }
    }

    if (empty($columns)) {
        GraphUtility::ajaxResponseFalse('Query returned no columns. Please check your query.');
    }

    GraphUtility::ajaxResponseTrue('Query is valid', array(
        'columns' => $columns,
        'rows' => $rows,
        'row_count' => count($rows)
    ));
}

/**
 * Preview graph with query execution
 */
function previewGraph($data)
{
    $graphId = isset($data['id']) ? intval($data['id']) : 0;

    if ($graphId) {
        // Load existing graph
        $graph = new Graph($graphId);
        if (!$graph->getId()) {
            GraphUtility::ajaxResponseFalse('Graph not found');
        }

        $filters = isset($data['filters']) ? $data['filters'] : array();
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        $chartData = $graph->execute($filters ? $filters : array());
        $config = json_decode($graph->getConfig(), true);

        GraphUtility::ajaxResponseTrue('Graph data loaded', array(
            'chartData' => $chartData,
            'config' => $config
        ));
    } else {
        // Preview with provided data
        $query = isset($data['query']) ? trim($data['query']) : '';
        $mapping = isset($data['mapping']) ? $data['mapping'] : array();
        $graphType = isset($data['graph_type']) ? $data['graph_type'] : 'bar';
        $filters = isset($data['filters']) ? $data['filters'] : array();

        if (is_string($mapping)) {
            $mapping = json_decode($mapping, true);
        }
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        if (empty($query)) {
            GraphUtility::ajaxResponseFalse('No query provided');
        }

        // Apply filter values to query
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $db = Rapidkart::getInstance()->getDB();
                $escaped = array();
                foreach ($value as $v) {
                    $escaped[] = "'" . $db->escapeString($v) . "'";
                }
                $query = str_replace($key, implode(',', $escaped), $query);
            } else {
                $db = Rapidkart::getInstance()->getDB();
                $query = str_replace($key, "'" . $db->escapeString($value) . "'", $query);
            }
        }

        // Replace remaining placeholders with dummy values
        $query = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', "'test'", $query);

        $db = Rapidkart::getInstance()->getDB();
        $res = $db->query($query);

        if (!$res) {
            GraphUtility::ajaxResponseFalse('Query error: ' . $db->getError());
        }

        $rows = $db->fetchAllAssoc($res);
        $chartData = formatPreviewData($rows, $mapping, $graphType);

        GraphUtility::ajaxResponseTrue('Preview generated', array('chartData' => $chartData));
    }
}

/**
 * Format preview data
 */
function formatPreviewData($rows, $mapping, $graphType)
{
    if ($graphType === 'pie') {
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
 * Load graph data for editing
 */
function loadGraph($data)
{
    $graphId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$graphId) {
        GraphUtility::ajaxResponseFalse('Invalid graph ID');
    }

    $graph = new Graph($graphId);
    if (!$graph->getId()) {
        GraphUtility::ajaxResponseFalse('Graph not found');
    }

    // Extract placeholders and find matching filters
    $placeholders = Filter::extractPlaceholders($graph->getQuery());
    $matchedFilters = Filter::getByKeys($placeholders);

    $response = $graph->toArray();
    $response['placeholders'] = $placeholders;
    $response['matched_filters'] = array();
    foreach ($matchedFilters as $key => $filter) {
        $response['matched_filters'][$key] = $filter->toArray();
    }

    GraphUtility::ajaxResponseTrue('Graph loaded', $response);
}

/**
 * Show filter list page (all independent filters)
 */
function showFilterList()
{
    $filters = Filter::getAll();
    require_once GraphConfig::templatesPath() . 'graph/filter-list.php';
}

/**
 * Show filter add/edit form
 */
function showFilterForm($graphId = null, $filterId = null)
{
    $filter = null;

    if ($filterId) {
        $filter = new Filter($filterId);
        if (!$filter->getId()) {
            GraphUtility::redirect('graph/filters');
            return;
        }
    }

    require_once GraphConfig::templatesPath() . 'graph/filter-form.php';
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
        GraphUtility::ajaxResponseFalse('Filter key is required');
    }

    if (empty($filterLabel)) {
        GraphUtility::ajaxResponseFalse('Filter label is required');
    }

    // Ensure filter key starts with :
    if (strpos($filterKey, ':') !== 0) {
        $filterKey = ':' . $filterKey;
    }

    $filter = $filterId ? new Filter($filterId) : new Filter();

    $filter->setFilterKey($filterKey);
    $filter->setFilterLabel($filterLabel);
    $filter->setFilterType($filterType);
    $filter->setDataSource($dataSource);
    $filter->setDataQuery(isset($data['data_query']) ? $data['data_query'] : '');
    $filter->setStaticOptions(isset($data['static_options']) ? $data['static_options'] : '');
    $filter->setDefaultValue(isset($data['default_value']) ? $data['default_value'] : '');
    $filter->setIsRequired(isset($data['is_required']) ? intval($data['is_required']) : 0);

    if ($filterId) {
        if (!$filter->update()) {
            GraphUtility::ajaxResponseFalse('Failed to update filter');
        }
    } else {
        if (!$filter->insert()) {
            GraphUtility::ajaxResponseFalse('Failed to create filter');
        }
    }

    GraphUtility::ajaxResponseTrue('Filter saved successfully', array('id' => $filter->getId()));
}

/**
 * Get single filter for editing
 */
function getFilter($data)
{
    $filterId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$filterId) {
        GraphUtility::ajaxResponseFalse('Invalid filter ID');
    }

    $filter = new Filter($filterId);
    if (!$filter->getId()) {
        GraphUtility::ajaxResponseFalse('Filter not found');
    }

    GraphUtility::ajaxResponseTrue('Filter loaded', $filter->toArray());
}

/**
 * Delete filter
 */
function deleteFilter($data)
{
    $filterId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$filterId || !Filter::delete($filterId)) {
        GraphUtility::ajaxResponseFalse('Failed to delete filter');
    }

    GraphUtility::ajaxResponseTrue('Filter deleted successfully');
}

/**
 * Test filter query (for query-based filter options)
 * Query should return 'value' and 'label' columns
 */
function testFilterQuery($data)
{
    $query = isset($data['query']) ? trim($data['query']) : '';

    if (empty($query)) {
        GraphUtility::ajaxResponseFalse('Please enter a SQL query');
    }

    // Add LIMIT for safety
    $testQuery = preg_replace('/\s+LIMIT\s+\d+(\s*,\s*\d+)?/i', '', $query);
    $testQuery .= ' LIMIT 20';

    $db = Rapidkart::getInstance()->getDB();
    $res = $db->query($testQuery);

    if (!$res) {
        GraphUtility::ajaxResponseFalse('Query error: ' . $db->getError());
    }

    $options = array();
    $columns = array();

    while ($row = $db->fetchAssoc($res)) {
        if (empty($columns)) {
            $columns = array_keys($row);
        }
        $options[] = array(
            'value' => isset($row['value']) ? $row['value'] : '',
            'label' => isset($row['label']) ? $row['label'] : (isset($row['value']) ? $row['value'] : '')
        );
    }

    if (empty($options)) {
        GraphUtility::ajaxResponseFalse('Query returned no results');
    }

    // Check if required columns exist
    $hasValue = in_array('value', $columns);
    $hasLabel = in_array('label', $columns);
    $warnings = array();

    if (!$hasValue) {
        $warnings[] = "No 'value' column found. Using first column.";
    }
    if (!$hasLabel) {
        $warnings[] = "No 'label' column found. Using 'value' for labels.";
    }

    GraphUtility::ajaxResponseTrue('Query is valid', array(
        'columns' => $columns,
        'options' => $options,
        'count' => count($options),
        'warnings' => $warnings
    ));
}
