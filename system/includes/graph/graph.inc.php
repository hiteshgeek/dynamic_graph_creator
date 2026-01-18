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
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS using helper functions
    Utility::addModuleCss('common');
    Utility::addModuleCss('graph');

    // Add page-specific JS using helper functions
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('graph');
    $theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph-list.js');

    $theme->setPageTitle('Graphs - Dynamic Graph Creator');

    // Get content from template
    $graphs = Graph::getAll();
    ob_start();
    require_once SystemConfig::templatesPath() . 'graph/graph-list.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Show graph creator/editor
 */
function showCreator($graphId = null)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('graph');

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror/css/codemirror.min.css', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror/css/material.min.css', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror/js/codemirror.min.js', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror/js/sql.min.js', 7);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize/autosize.min.js', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery/jquery.min.js', 4);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment/moment.min.js', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker/js/daterangepicker.min.js', 8);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('graph');
    $theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph-creator.js');

    $graph = null;

    if ($graphId) {
        $graph = new Graph($graphId);
        if (!$graph->getId()) {
            Utility::redirect('graph');
            return;
        }
    }

    $theme->setPageTitle('Graphs - ' . ($graph ? 'Edit' : 'Create') . ' Graph - Dynamic Graph Creator');

    // Get all available filters for selection
    $allFilters = Filter::getAll();

    // Permission to create filters (replace with actual framework permission check)
    $canCreateFilter = true;

    ob_start();
    require_once SystemConfig::templatesPath() . 'graph/graph-creator.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Show graph view page
 */
function showView($graphId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('graph');

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery/jquery.min.js', 4);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment/moment.min.js', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker/js/daterangepicker.min.js', 8);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('graph');

    $graph = new Graph($graphId);
    if (!$graph->getId()) {
        Utility::redirect('graph');
        return;
    }

    $theme->setPageTitle('Graphs - ' . htmlspecialchars($graph->getName()) . ' - Dynamic Graph Creator');

    // Extract placeholders from the graph query and find matching filters
    $placeholders = Filter::extractPlaceholders($graph->getQuery());
    $matchedFilters = Filter::getByKeys($placeholders);

    // Convert to array format for template
    $filters = array();
    foreach ($matchedFilters as $key => $filter) {
        $filters[] = $filter->toArray();
    }

    ob_start();
    require_once SystemConfig::templatesPath() . 'graph/graph-view.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
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
 * Validate that required placeholders have values
 * Returns array of missing required placeholders, or empty array if all valid
 *
 * @param string $query The SQL query with placeholders
 * @param array $filters Filter values keyed by placeholder
 * @param array $placeholderSettings Settings per placeholder
 * @return array Array of missing required placeholder names (without ::)
 */
function validateRequiredPlaceholders($query, $filters, $placeholderSettings)
{
    $missing = array();

    // Find all placeholders in the query
    preg_match_all('/::([a-zA-Z_][a-zA-Z0-9_]*)/', $query, $matches);
    $placeholders = array_unique($matches[0]);

    foreach ($placeholders as $placeholder) {
        $settings = isset($placeholderSettings[$placeholder]) ? $placeholderSettings[$placeholder] : array();
        $allowEmpty = isset($settings['allowEmpty']) ? $settings['allowEmpty'] : true;

        // If allowEmpty is false, check if filter has a value
        if (!$allowEmpty) {
            $filterValue = isset($filters[$placeholder]) ? $filters[$placeholder] : null;
            if (isFilterValueEmpty($filterValue)) {
                // Remove :: prefix for display
                $missing[] = ltrim($placeholder, ':');
            }
        }
    }

    return $missing;
}

/**
 * Replace placeholders in query with filter values
 * Handles empty values based on placeholder settings:
 * - If allowEmpty is true (default): replace condition with 1=1
 * - If allowEmpty is false (required): should be validated before calling this
 *
 * @param string $query The SQL query with placeholders
 * @param array $filters Filter values keyed by placeholder (e.g., ['::category' => 'value'])
 * @param array $placeholderSettings Settings per placeholder (e.g., ['::category' => ['allowEmpty' => false]])
 * @param object $db Database instance for escaping
 * @return string The query with placeholders replaced
 */
function replaceQueryPlaceholders($query, $filters, $placeholderSettings, $db)
{
    // Sort filter keys by length descending to replace longer placeholders first
    // This prevents ::category from matching within ::category_checkbox
    uksort($filters, function ($a, $b) {
        return strlen($b) - strlen($a);
    });

    // First, handle filters that have values
    foreach ($filters as $placeholder => $value) {
        $isEmpty = isFilterValueEmpty($value);

        if (!$isEmpty) {
            // Filter has a value, replace normally
            if (is_array($value)) {
                $escaped = array();
                foreach ($value as $v) {
                    $escaped[] = "'" . $db->escapeString($v) . "'";
                }
                $query = str_replace($placeholder, implode(',', $escaped), $query);
            } else {
                $query = str_replace($placeholder, "'" . $db->escapeString($value) . "'", $query);
            }
        } else {
            // Filter is empty, check if allowEmpty
            $settings = isset($placeholderSettings[$placeholder]) ? $placeholderSettings[$placeholder] : array();
            $allowEmpty = isset($settings['allowEmpty']) ? $settings['allowEmpty'] : true;

            if ($allowEmpty) {
                // Replace the entire condition containing this placeholder with 1=1
                $query = replaceConditionWithTrueValue($query, $placeholder);
            }
            // If not allowEmpty and empty, validation should have caught this
        }
    }

    // Handle any remaining placeholders not in filters
    $query = preg_replace_callback('/::([a-zA-Z_][a-zA-Z0-9_]*)/', function ($matches) use ($placeholderSettings) {
        $placeholder = '::' . $matches[1];
        $settings = isset($placeholderSettings[$placeholder]) ? $placeholderSettings[$placeholder] : array();
        $allowEmpty = isset($settings['allowEmpty']) ? $settings['allowEmpty'] : true;

        if ($allowEmpty) {
            // Will be handled by condition replacement below
            return $placeholder;
        }
        // Required but not in filters - validation should have caught this
        return $placeholder;
    }, $query);

    // Final pass: replace any remaining placeholders with allowEmpty=true using 1=1
    preg_match_all('/::([a-zA-Z_][a-zA-Z0-9_]*)/', $query, $matches);
    foreach ($matches[0] as $placeholder) {
        $settings = isset($placeholderSettings[$placeholder]) ? $placeholderSettings[$placeholder] : array();
        $allowEmpty = isset($settings['allowEmpty']) ? $settings['allowEmpty'] : true;

        if ($allowEmpty) {
            $query = replaceConditionWithTrueValue($query, $placeholder);
        }
    }

    return $query;
}

/**
 * Check if a filter value is empty
 */
function isFilterValueEmpty($value)
{
    if ($value === null || $value === '') {
        return true;
    }
    if (is_array($value) && count($value) === 0) {
        return true;
    }
    return false;
}

/**
 * Replace SQL condition containing placeholder with 1=1
 * Handles patterns like:
 * - field = ::placeholder
 * - field != ::placeholder
 * - field IN (::placeholder)
 * - field NOT IN (::placeholder)
 * - field LIKE ::placeholder
 * - field > ::placeholder, field < ::placeholder, etc.
 * - field BETWEEN ::placeholder_from AND ::placeholder_to
 *
 * @param string $query The SQL query
 * @param string $placeholder The placeholder to find and replace
 * @return string The query with condition replaced by 1=1
 */
function replaceConditionWithTrueValue($query, $placeholder)
{
    $escapedPlaceholder = preg_quote($placeholder, '/');

    // Pattern for IN/NOT IN clauses: field IN (::placeholder) or field NOT IN (::placeholder)
    $inPattern = '/\w+\s+(?:NOT\s+)?IN\s*\(\s*' . $escapedPlaceholder . '\s*\)/i';
    $query = preg_replace($inPattern, '1=1', $query);

    // Pattern for BETWEEN: field BETWEEN ::placeholder AND value or value AND ::placeholder
    $betweenPattern = '/\w+\s+BETWEEN\s+(?:' . $escapedPlaceholder . '\s+AND\s+[^\s]+|[^\s]+\s+AND\s+' . $escapedPlaceholder . ')/i';
    $query = preg_replace($betweenPattern, '1=1', $query);

    // Pattern for comparison operators: field = ::placeholder, field >= ::placeholder, etc.
    $comparisonPattern = '/\w+\s*(?:=|!=|<>|>=|<=|>|<)\s*' . $escapedPlaceholder . '/i';
    $query = preg_replace($comparisonPattern, '1=1', $query);

    // Pattern for LIKE: field LIKE ::placeholder
    $likePattern = '/\w+\s+(?:NOT\s+)?LIKE\s+' . $escapedPlaceholder . '/i';
    $query = preg_replace($likePattern, '1=1', $query);

    // If placeholder still exists (not part of a recognized condition), just replace with 'test'
    if (strpos($query, $placeholder) !== false) {
        $query = str_replace($placeholder, "'test'", $query);
    }

    return $query;
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
