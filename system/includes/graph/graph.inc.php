<?php

/**
 * Graph Controller
 * Handles all graph-related actions
 */

$url = Utility::parseUrl();
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
    require_once SystemConfig::templatesPath() . 'graph/graph-list.php';
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
            Utility::redirect('graph');
            return;
        }
    }

    // Get all available filters for selection
    $allFilters = Filter::getAll();

    require_once SystemConfig::templatesPath() . 'graph/graph-creator.php';
}

/**
 * Show graph view page
 */
function showView($graphId)
{
    $graph = new Graph($graphId);
    if (!$graph->getId()) {
        Utility::redirect('graph');
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

    require_once SystemConfig::templatesPath() . 'graph/graph-view.php';
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
            Utility::ajaxResponseFalse('Failed to update graph');
        }
    } else {
        if (!$graph->insert()) {
            Utility::ajaxResponseFalse('Failed to create graph');
        }
    }

    $message = $isUpdate ? 'Graph updated successfully' : 'Graph created successfully';
    Utility::ajaxResponseTrue($message, array('id' => $graph->getId()));
}

/**
 * Delete graph
 */
function deleteGraph($data)
{
    $graphId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$graphId || !Graph::delete($graphId)) {
        Utility::ajaxResponseFalse('Failed to delete graph');
    }

    Utility::ajaxResponseTrue('Graph deleted successfully');
}

/**
 * Test SQL query and return columns with sample rows
 */
function testQuery($data)
{
    $query = isset($data['query']) ? trim($data['query']) : '';

    if (empty($query)) {
        Utility::ajaxResponseFalse('Please enter a SQL query');
    }

    // Replace placeholders with dummy values for testing
    $testQuery = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', "'test'", $query);

    // Remove existing LIMIT and add our own for sample rows
    $testQuery = preg_replace('/\s+LIMIT\s+\d+(\s*,\s*\d+)?/i', '', $testQuery);
    $testQuery .= ' LIMIT 10';

    $db = Rapidkart::getInstance()->getDB();
    $res = $db->query($testQuery);

    if (!$res) {
        Utility::ajaxResponseFalse('Query error: ' . $db->getError());
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
        Utility::ajaxResponseFalse('Query returned no columns. Please check your query.');
    }

    Utility::ajaxResponseTrue('Query is valid', array(
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
            Utility::ajaxResponseFalse('Graph not found');
        }

        $filters = isset($data['filters']) ? $data['filters'] : array();
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        $chartData = $graph->execute($filters ? $filters : array());
        $config = json_decode($graph->getConfig(), true);

        Utility::ajaxResponseTrue('Graph data loaded', array(
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
            Utility::ajaxResponseFalse('No query provided');
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
            Utility::ajaxResponseFalse('Query error: ' . $db->getError());
        }

        $rows = $db->fetchAllAssoc($res);
        $chartData = formatPreviewData($rows, $mapping, $graphType);

        Utility::ajaxResponseTrue('Preview generated', array('chartData' => $chartData));
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
        Utility::ajaxResponseFalse('Invalid graph ID');
    }

    $graph = new Graph($graphId);
    if (!$graph->getId()) {
        Utility::ajaxResponseFalse('Graph not found');
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

    Utility::ajaxResponseTrue('Graph loaded', $response);
}
