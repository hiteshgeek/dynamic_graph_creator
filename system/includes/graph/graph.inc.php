<?php

/**
 * Graph Controller
 * Handles all graph-related actions
 */

// Graph snapshot image size constants
define('GRAPH_SNAPSHOT_LARGE_WIDTH', 1200);
define('GRAPH_SNAPSHOT_LARGE_HEIGHT', 800);
define('GRAPH_SNAPSHOT_THUMB_WIDTH', 300);
define('GRAPH_SNAPSHOT_THUMB_HEIGHT', 200);

// Require admin access (company 232 + admin user)
DGCHelper::requireAdminAccess();

// Load graph module assets (common assets loaded in index.php)
LocalUtility::addModuleCss('graph');
LocalUtility::addModuleJs('graph');

// $url is already parsed in index.php
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
        case 'graph-save-snapshot':
            graphSaveSnapshot($_POST);
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
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific JS
    LocalUtility::addPageScript('graph', 'graph-list');

    $theme->setPageTitle('Graphs - Dynamic Graph Creator');

    // Get all graphs
    $graphs = GraphManager::getAll();

    // Get categories for each graph
    $graphCategories = array();
    foreach ($graphs as $graph) {
        $graphCategories[$graph->getId()] = GraphWidgetCategoryMappingManager::getCategoriesForGraph($graph->getId());
    }

    // Get content from template
    $tpl = new Template(SystemConfig::templatesPath() . 'graph/views/graph-list');
    $tpl->graphs = $graphs;
    $tpl->graphCategories = $graphCategories;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show graph creator/editor
 */
function showCreator($graphId = null)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries - jQuery and daterangepicker must load BEFORE dist JS (which has weight 10+)
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery3/jquery.min.js', 1);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment-dgc/moment.min.js', 2);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/js/daterangepicker.min.js', 3);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts-dgc/echarts.min.js', 4);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/css/codemirror.min.css', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/css/material.min.css', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/js/codemirror.min.js', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/js/sql.min.js', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize-dgc/autosize.min.js', 7);

    // Add page-specific JS
    LocalUtility::addPageScript('graph', 'graph-creator');

    $graph = null;

    if ($graphId) {
        $graph = new Graph($graphId);
        if (!$graph->getId()) {
            LocalUtility::redirect('graph');
            return;
        }
    }

    $theme->setPageTitle('Graphs - ' . ($graph ? 'Edit' : 'Create') . ' Graph - Dynamic Graph Creator');

    // Get all available filters for selection
    $allFilters = DataFilterManager::getAllAsArray();

    // Permission to create filters (replace with actual framework permission check)
    $canCreateFilter = true;

    // Get all widget categories
    $categories = WidgetCategoryManager::getAllAsArray();

    // Get selected category IDs for this graph (if editing)
    $selectedCategoryIds = array();
    if ($graph && $graph->getId()) {
        $selectedCategoryIds = GraphWidgetCategoryMappingManager::getCategoryIdsForGraph($graph->getId());
    }

    $tpl = new Template(SystemConfig::templatesPath() . 'graph/forms/graph-creator');
    $tpl->graph = $graph;
    $tpl->allFilters = $allFilters;
    $tpl->canCreateFilter = $canCreateFilter;
    $tpl->categories = $categories;
    $tpl->selectedCategoryIds = $selectedCategoryIds;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show graph view page
 */
function showView($graphId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries - jQuery and daterangepicker must load BEFORE dist JS (which has weight 10+)
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery3/jquery.min.js', 1);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment-dgc/moment.min.js', 2);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/js/daterangepicker.min.js', 3);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts-dgc/echarts.min.js', 4);

    $graph = new Graph($graphId);
    if (!$graph->getId()) {
        LocalUtility::redirect('graph');
        return;
    }

    $theme->setPageTitle('Graphs - ' . htmlspecialchars($graph->getName()) . ' - Dynamic Graph Creator');

    // Get all graphs for navigation
    $allGraphs = GraphManager::getAll();
    $totalGraphs = count($allGraphs);
    $currentIndex = 0;
    $prevGraphId = null;
    $nextGraphId = null;

    // Find current graph position and prev/next IDs
    foreach ($allGraphs as $index => $g) {
        if ($g->getId() == $graphId) {
            $currentIndex = $index + 1; // 1-based for display
            if ($index > 0) {
                $prevGraphId = $allGraphs[$index - 1]->getId();
            }
            if ($index < $totalGraphs - 1) {
                $nextGraphId = $allGraphs[$index + 1]->getId();
            }
            break;
        }
    }

    // Extract placeholders from the graph query and find matching filters
    $placeholders = DataFilterManager::extractPlaceholders($graph->getQuery());
    $matchedFilters = DataFilterManager::getByKeys($placeholders);

    // Convert to array format for template
    $filters = array();
    foreach ($matchedFilters as $key => $filter) {
        $filters[] = $filter->toArray();
    }

    // Get categories for this graph
    $categories = GraphWidgetCategoryMappingManager::getCategoriesForGraph($graphId);

    $tpl = new Template(SystemConfig::templatesPath() . 'graph/views/graph-view');
    $tpl->graph = $graph;
    $tpl->filters = $filters;
    $tpl->categories = $categories;
    $tpl->allGraphs = $allGraphs;
    $tpl->totalGraphs = $totalGraphs;
    $tpl->currentIndex = $currentIndex;
    $tpl->prevGraphId = $prevGraphId;
    $tpl->nextGraphId = $nextGraphId;
    $theme->setContent('full_main', $tpl->parse());
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
    $userId = Session::loggedInUid();

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

    $placeholderSettings = isset($data['placeholder_settings']) ? $data['placeholder_settings'] : '{}';
    if (is_string($placeholderSettings)) {
        $graph->setPlaceholderSettings($placeholderSettings);
    } else {
        $graph->setPlaceholderSettings(json_encode($placeholderSettings));
    }

    if ($isUpdate) {
        $graph->setUpdatedUid($userId);
        if (!$graph->update()) {
            Utility::ajaxResponseFalse('Failed to update graph');
        }
    } else {
        $graph->setCreatedUid($userId);
        if (!$graph->insert()) {
            Utility::ajaxResponseFalse('Failed to create graph');
        }
    }

    // Save widget categories
    $categoryIds = isset($data['categories']) ? $data['categories'] : array();
    if (is_string($categoryIds)) {
        $categoryIds = json_decode($categoryIds, true);
    }
    if (!is_array($categoryIds)) {
        $categoryIds = array();
    }
    GraphWidgetCategoryMappingManager::setGraphCategories($graph->getId(), $categoryIds);

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
 * Validate that required placeholders have values
 * Wrapper for DataFilterManager::validateRequiredPlaceholders
 *
 * @param string $query The SQL query with placeholders
 * @param array $filters Filter values keyed by placeholder
 * @param array $placeholderSettings Settings per placeholder
 * @return array Array of missing required placeholder names (without ::)
 */
function validateRequiredPlaceholders($query, $filters, $placeholderSettings)
{
    $missing = DataFilterManager::validateRequiredPlaceholders($query, $filters, $placeholderSettings);

    // Remove :: prefix for display
    return array_map(function($placeholder) {
        return ltrim($placeholder, ':');
    }, $missing);
}

/**
 * Replace placeholders in query with filter values
 * Wrapper for DataFilterManager::replaceQueryPlaceholders
 *
 * @param string $query The SQL query with placeholders
 * @param array $filters Filter values keyed by placeholder (e.g., ['::category' => 'value'])
 * @param array $placeholderSettings Settings per placeholder (e.g., ['::category' => ['allowEmpty' => false]])
 * @param object $db Database instance for escaping (deprecated - not used, kept for backward compatibility)
 * @return string The query with placeholders replaced
 */
function replaceQueryPlaceholders($query, $filters, $placeholderSettings, $db)
{
    return DataFilterManager::replaceQueryPlaceholders($query, $filters, $placeholderSettings);
}

/**
 * Test SQL query and return columns with sample rows
 */
function testQuery($data)
{
    $query = isset($data['query']) ? trim($data['query']) : '';
    $filters = isset($data['filters']) ? $data['filters'] : array();
    $placeholderSettings = isset($data['placeholder_settings']) ? $data['placeholder_settings'] : array();

    // If filters is a JSON string, decode it
    if (is_string($filters)) {
        $filters = json_decode($filters, true);
        if (!is_array($filters)) {
            $filters = array();
        }
    }

    // If placeholderSettings is a JSON string, decode it
    if (is_string($placeholderSettings)) {
        $placeholderSettings = json_decode($placeholderSettings, true);
        if (!is_array($placeholderSettings)) {
            $placeholderSettings = array();
        }
    }

    if (empty($query)) {
        Utility::ajaxResponseFalse('Please enter a SQL query');
    }

    // Validate required placeholders have values
    $missingRequired = validateRequiredPlaceholders($query, $filters, $placeholderSettings);
    if (!empty($missingRequired)) {
        $filterNames = implode(', ', $missingRequired);
        Utility::ajaxResponseFalse('Required filter(s) missing value: ' . $filterNames);
    }

    $db = Rapidkart::getInstance()->getDB();
    $testQuery = $query;

    // Replace placeholders with filter values, handling empty values based on settings
    $testQuery = replaceQueryPlaceholders($testQuery, $filters, $placeholderSettings, $db);

    // Store the debug query (with placeholders replaced, but without forced LIMIT)
    $debugQuery = $testQuery;

    // Remove existing LIMIT and add our own for sample rows (for execution only)
    $testQuery = preg_replace('/\s+LIMIT\s+\d+(\s*,\s*\d+)?/i', '', $testQuery);
    $testQuery .= ' LIMIT 10';

    $res = $db->query($testQuery);

    if (!$res) {
        Utility::ajaxResponseFalse('Query error: ' . $db->getMysqlError());
    }

    // Fetch all sample rows
    $rows = array();
    $columns = array();

    while ($row = $db->fetchAssocArray($res)) {
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
        'row_count' => count($rows),
        'debug_query' => $debugQuery
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
        $placeholderSettings = isset($data['placeholder_settings']) ? $data['placeholder_settings'] : array();

        if (is_string($mapping)) {
            $mapping = json_decode($mapping, true);
        }
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }
        if (is_string($placeholderSettings)) {
            $placeholderSettings = json_decode($placeholderSettings, true);
            if (!is_array($placeholderSettings)) {
                $placeholderSettings = array();
            }
        }

        if (empty($query)) {
            Utility::ajaxResponseFalse('No query provided');
        }

        // Validate required placeholders have values
        $missingRequired = validateRequiredPlaceholders($query, $filters, $placeholderSettings);
        if (!empty($missingRequired)) {
            $filterNames = implode(', ', $missingRequired);
            Utility::ajaxResponseFalse('Required filter(s) missing value: ' . $filterNames);
        }

        $db = Rapidkart::getInstance()->getDB();

        // Replace placeholders with filter values, handling empty values based on settings
        $query = replaceQueryPlaceholders($query, $filters, $placeholderSettings, $db);

        $res = $db->query($query);

        if (!$res) {
            Utility::ajaxResponseFalse('Query error: ' . $db->getMysqlError());
        }

        $rows = array();
        while ($row = $db->fetchAssocArray($res)) {
            $rows[] = $row;
        }
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
    $placeholders = DataFilterManager::extractPlaceholders($graph->getQuery());
    $matchedFilters = DataFilterManager::getByKeys($placeholders);

    $response = $graph->toArray();
    $response['placeholders'] = $placeholders;
    $response['matched_filters'] = array();
    foreach ($matchedFilters as $key => $filter) {
        $response['matched_filters'][$key] = $filter->toArray();
    }

    Utility::ajaxResponseTrue('Graph loaded', $response);
}

/**
 * Save graph snapshot image to filesystem and database
 */
function graphSaveSnapshot($data)
{
    $gid = isset($data['gid']) ? intval($data['gid']) : 0;
    $imageData = isset($data['image_data']) ? $data['image_data'] : '';

    if (!$gid || !Graph::isExistent($gid)) {
        Utility::ajaxResponseFalse('Invalid graph');
    }

    // Validate base64 PNG data
    if (!preg_match('/^data:image\/png;base64,/', $imageData)) {
        Utility::ajaxResponseFalse('Invalid image data');
    }

    // Decode base64
    $base64 = preg_replace('/^data:image\/png;base64,/', '', $imageData);
    $binary = base64_decode($base64);

    if ($binary === false) {
        Utility::ajaxResponseFalse('Failed to decode image data');
    }

    // Generate filename: graph_{gid}_{timestamp}.png
    $filename = 'graph_' . $gid . '_' . time() . '.png';

    // Create directories
    $baseDir = SiteConfig::filesDirectory() . 'graph/';
    $largeDir = $baseDir . 'large/';
    $thumbDir = $baseDir . 'thumbnail/';

    if (!is_dir($largeDir)) {
        mkdir($largeDir, 0755, true);
    }
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }

    // Delete old snapshots if exist
    $graph = new Graph($gid);
    if ($graph->getSnapshot()) {
        $oldLarge = $largeDir . $graph->getSnapshot();
        $oldThumb = $thumbDir . $graph->getSnapshot();
        if (file_exists($oldLarge)) {
            unlink($oldLarge);
        }
        if (file_exists($oldThumb)) {
            unlink($oldThumb);
        }
    }

    // Create image from binary data
    $srcImage = imagecreatefromstring($binary);
    if (!$srcImage) {
        Utility::ajaxResponseFalse('Failed to process image');
    }

    $srcWidth = imagesx($srcImage);
    $srcHeight = imagesy($srcImage);

    // Create and save LARGE version
    $largeImage = imagecreatetruecolor(GRAPH_SNAPSHOT_LARGE_WIDTH, GRAPH_SNAPSHOT_LARGE_HEIGHT);
    $white = imagecolorallocate($largeImage, 255, 255, 255);
    imagefill($largeImage, 0, 0, $white);
    imagecopyresampled(
        $largeImage,
        $srcImage,
        0,
        0,
        0,
        0,
        GRAPH_SNAPSHOT_LARGE_WIDTH,
        GRAPH_SNAPSHOT_LARGE_HEIGHT,
        $srcWidth,
        $srcHeight
    );
    imagepng($largeImage, $largeDir . $filename, 6);
    imagedestroy($largeImage);

    // Create and save THUMBNAIL version
    $thumbImage = imagecreatetruecolor(GRAPH_SNAPSHOT_THUMB_WIDTH, GRAPH_SNAPSHOT_THUMB_HEIGHT);
    $white = imagecolorallocate($thumbImage, 255, 255, 255);
    imagefill($thumbImage, 0, 0, $white);
    imagecopyresampled(
        $thumbImage,
        $srcImage,
        0,
        0,
        0,
        0,
        GRAPH_SNAPSHOT_THUMB_WIDTH,
        GRAPH_SNAPSHOT_THUMB_HEIGHT,
        $srcWidth,
        $srcHeight
    );
    imagepng($thumbImage, $thumbDir . $filename, 6);
    imagedestroy($thumbImage);

    imagedestroy($srcImage);

    // Update database
    $graph->setSnapshot($filename);
    $graph->setUpdatedUid(Session::loggedInUid());
    if (!$graph->updateSnapshot()) {
        Utility::ajaxResponseFalse('Failed to update graph');
    }

    Utility::ajaxResponseTrue('Image saved successfully', array(
        'filename' => $filename,
        'large_url' => SiteConfig::filesUrl() . 'graph/large/' . $filename,
        'thumb_url' => SiteConfig::filesUrl() . 'graph/thumbnail/' . $filename
    ));
}
