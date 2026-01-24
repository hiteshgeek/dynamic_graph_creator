<?php

/**
 * Table Controller
 * Handles all table widget-related actions
 */

// Table snapshot image size constants
define('TABLE_SNAPSHOT_LARGE_WIDTH', 800);
define('TABLE_SNAPSHOT_LARGE_HEIGHT', 400);
define('TABLE_SNAPSHOT_THUMB_WIDTH', 400);
define('TABLE_SNAPSHOT_THUMB_HEIGHT', 200);

// Default pagination settings
define('TABLE_DEFAULT_ROWS_PER_PAGE', 10);
define('TABLE_MAX_PREVIEW_ROWS', 100);

// Require admin access (company 232 + admin user)
DGCHelper::requireAdminAccess();

// Load table module assets (common assets loaded in index.php)
LocalUtility::addModuleCss('table');
LocalUtility::addModuleJs('table');

// $url is already parsed in index.php
$action = isset($url[1]) ? $url[1] : 'list';

// Handle POST actions
if (isset($_POST['submit'])) {
    switch ($_POST['submit']) {
        case 'save_table':
            saveTable($_POST);
            break;
        case 'delete_table':
            deleteTable($_POST);
            break;
        case 'test_query':
            testTableQuery($_POST);
            break;
        case 'preview_table':
            previewTable($_POST);
            break;
        case 'load_table':
            loadTable($_POST);
            break;
        case 'table-save-snapshot':
            tableSaveSnapshot($_POST);
            break;
    }
}

// Handle GET actions
switch ($action) {
    case 'create':
        showCreator();
        break;
    case 'edit':
        $tableId = isset($url[2]) ? intval($url[2]) : 0;
        showCreator($tableId);
        break;
    case 'view':
        $tableId = isset($url[2]) ? intval($url[2]) : 0;
        showView($tableId);
        break;
    case 'list':
    default:
        showList();
        break;
}

/**
 * Show table list page
 */
function showList()
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific JS
    LocalUtility::addPageScript('table', 'table-list');

    $theme->setPageTitle('Tables - Dynamic Graph Creator');

    // Get all tables
    $tables = WidgetTableManager::getAll();

    // Get categories for each table
    $tableCategories = array();
    foreach ($tables as $table) {
        $tableCategories[$table->getId()] = WidgetTableCategoryMappingManager::getCategoriesForTable($table->getId());
    }

    // Get content from template
    $tpl = new Template(SystemConfig::templatesPath() . 'table/views/table-list');
    $tpl->tables = $tables;
    $tpl->tableCategories = $tableCategories;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show table creator/editor
 */
function showCreator($tableId = null)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries - jQuery and daterangepicker must load BEFORE dist JS (which has weight 10+)
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery3/jquery.min.js', 1);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment-dgc/moment.min.js', 2);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/js/daterangepicker.min.js', 3);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/css/codemirror.min.css', 5);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/css/material.min.css', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/js/codemirror.min.js', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror-dgc/js/sql.min.js', 6);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize-dgc/autosize.min.js', 7);

    // Add page-specific JS
    LocalUtility::addPageScript('table', 'table-creator');

    $table = null;

    if ($tableId) {
        $table = new WidgetTable($tableId);
        if (!$table->getId()) {
            LocalUtility::redirect('widget-table');
            return;
        }
    }

    $theme->setPageTitle('Tables - ' . ($table ? 'Edit' : 'Create') . ' Table - Dynamic Graph Creator');

    // Get all available filters for selection
    $allFilters = DataFilterManager::getAllAsArray();

    // Get mandatory filters for widget type "table"
    $mandatoryFilters = DataFilterManager::getMandatoryFiltersForWidgetTypeAsArray('table');
    $mandatoryFilterKeys = array_map(function($f) {
        return ltrim($f['filter_key'], ':');
    }, $mandatoryFilters);

    // Permission to create filters (replace with actual framework permission check)
    $canCreateFilter = true;

    // Get all widget categories
    $categories = WidgetCategoryManager::getAllAsArray();

    // Get selected category IDs for this table (if editing)
    $selectedCategoryIds = array();
    if ($table && $table->getId()) {
        $selectedCategoryIds = WidgetTableCategoryMappingManager::getCategoryIdsForTable($table->getId());
    }

    // Get density options
    $densityOptions = WidgetTable::getDensityOptions();

    // Get rows per page options
    $rowsPerPageOptions = WidgetTable::getRowsPerPageOptions();

    $tpl = new Template(SystemConfig::templatesPath() . 'table/forms/table-creator');
    $tpl->table = $table;
    $tpl->allFilters = $allFilters;
    $tpl->mandatoryFilters = $mandatoryFilters;
    $tpl->mandatoryFilterKeys = $mandatoryFilterKeys;
    $tpl->canCreateFilter = $canCreateFilter;
    $tpl->categories = $categories;
    $tpl->selectedCategoryIds = $selectedCategoryIds;
    $tpl->densityOptions = $densityOptions;
    $tpl->rowsPerPageOptions = $rowsPerPageOptions;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show table view page
 */
function showView($tableId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries - jQuery and daterangepicker must load BEFORE dist JS (which has weight 10+)
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery3/jquery.min.js', 1);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment-dgc/moment.min.js', 2);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/js/daterangepicker.min.js', 3);

    $table = new WidgetTable($tableId);
    if (!$table->getId()) {
        LocalUtility::redirect('widget-table');
        return;
    }

    $theme->setPageTitle('Tables - ' . htmlspecialchars($table->getName()) . ' - Dynamic Graph Creator');

    // Get all tables for navigation
    $allTables = WidgetTableManager::getAll();
    $totalTables = count($allTables);
    $currentIndex = 0;
    $prevTableId = null;
    $nextTableId = null;

    // Find current table position and prev/next IDs
    foreach ($allTables as $index => $t) {
        if ($t->getId() == $tableId) {
            $currentIndex = $index + 1; // 1-based for display
            if ($index > 0) {
                $prevTableId = $allTables[$index - 1]->getId();
            }
            if ($index < $totalTables - 1) {
                $nextTableId = $allTables[$index + 1]->getId();
            }
            break;
        }
    }

    // Extract placeholders from the table query and find matching filters
    $placeholders = DataFilterManager::extractPlaceholders($table->getQuery());
    $matchedFilters = DataFilterManager::getByKeys($placeholders);

    // Convert to array format for template
    $filters = array();
    foreach ($matchedFilters as $key => $filter) {
        $filters[] = $filter->toArray();
    }

    // Get categories for this table
    $tableCategories = WidgetTableCategoryMappingManager::getCategoriesForTable($tableId);

    // Get mandatory filters for widget type "table"
    $mandatoryFilters = DataFilterManager::getMandatoryFiltersForWidgetTypeAsArray('table');
    $mandatoryFilterKeys = array_map(function($f) {
        return ltrim($f['filter_key'], ':');
    }, $mandatoryFilters);

    $tpl = new Template(SystemConfig::templatesPath() . 'table/views/table-view');
    $tpl->table = $table;
    $tpl->filters = $filters;
    $tpl->mandatoryFilterKeys = $mandatoryFilterKeys;
    $tpl->categories = $tableCategories;
    $tpl->allTables = $allTables;
    $tpl->totalTables = $totalTables;
    $tpl->currentIndex = $currentIndex;
    $tpl->prevTableId = $prevTableId;
    $tpl->nextTableId = $nextTableId;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Save table (create or update)
 */
function saveTable($data)
{
    $tableId = isset($data['id']) ? intval($data['id']) : 0;
    $isUpdate = $tableId > 0;
    $userId = Session::loggedInUid();

    $query = isset($data['query']) ? $data['query'] : '';

    // Validate query security (SELECT only)
    if (!QueryHelper::validateOrFail($query)) {
        return; // Error response already sent
    }

    // Validate mandatory filters are in query
    $mandatoryValidation = QueryHelper::validateMandatoryFiltersInQuery($query, 'table');
    if (!$mandatoryValidation['valid']) {
        $missingPlaceholders = array_map(function($key) {
            return '::' . ltrim($key, ':');
        }, $mandatoryValidation['missing']);
        $message = count($missingPlaceholders) === 1
            ? 'Query must include mandatory filter: ' . implode(', ', $missingPlaceholders)
            : 'Query must include mandatory filters: ' . implode(', ', $missingPlaceholders);
        Utility::ajaxResponseFalse($message);
    }

    $table = $isUpdate ? new WidgetTable($tableId) : new WidgetTable();

    $table->setName(isset($data['name']) ? $data['name'] : '');
    $table->setDescription(isset($data['description']) ? $data['description'] : '');
    $table->setQuery($query);

    $config = isset($data['config']) ? $data['config'] : '{}';
    if (is_string($config)) {
        $table->setConfig($config);
    } else {
        $table->setConfig(json_encode($config));
    }

    $placeholderSettings = isset($data['placeholder_settings']) ? $data['placeholder_settings'] : '{}';
    if (is_string($placeholderSettings)) {
        $table->setPlaceholderSettings($placeholderSettings);
    } else {
        $table->setPlaceholderSettings(json_encode($placeholderSettings));
    }

    if ($isUpdate) {
        $table->setUpdatedUid($userId);
        if (!$table->update()) {
            Utility::ajaxResponseFalse('Failed to update table');
        }
    } else {
        $table->setCreatedUid($userId);
        if (!$table->insert()) {
            Utility::ajaxResponseFalse('Failed to create table');
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
    WidgetTableCategoryMappingManager::setTableCategories($table->getId(), $categoryIds);

    $message = $isUpdate ? 'Table updated successfully' : 'Table created successfully';
    Utility::ajaxResponseTrue($message, array('id' => $table->getId()));
}

/**
 * Delete table
 */
function deleteTable($data)
{
    $tableId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$tableId || !WidgetTable::delete($tableId)) {
        Utility::ajaxResponseFalse('Failed to delete table');
    }

    Utility::ajaxResponseTrue('Table deleted successfully');
}

/**
 * Test SQL query for table (returns multiple rows)
 */
function testTableQuery($data)
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

    // Validate query security (SELECT only)
    if (!QueryHelper::validateOrFail($query)) {
        return; // Error response already sent
    }

    // Validate mandatory filters are in query
    $mandatoryValidation = QueryHelper::validateMandatoryFiltersInQuery($query, 'table');
    if (!$mandatoryValidation['valid']) {
        $missingPlaceholders = array_map(function($key) {
            return '::' . ltrim($key, ':');
        }, $mandatoryValidation['missing']);
        $message = count($missingPlaceholders) === 1
            ? 'Query must include mandatory filter: ' . implode(', ', $missingPlaceholders)
            : 'Query must include mandatory filters: ' . implode(', ', $missingPlaceholders);
        Utility::ajaxResponseFalse($message);
    }

    // Validate required placeholders have values
    $missingRequired = DataFilterManager::validateRequiredPlaceholders($query, $filters, $placeholderSettings);
    if (!empty($missingRequired)) {
        $filterNames = array_map(function($p) { return ltrim($p, ':'); }, $missingRequired);
        Utility::ajaxResponseFalse('Required filter(s) missing value: ' . implode(', ', $filterNames));
    }

    $db = Rapidkart::getInstance()->getDB();
    $testQuery = DataFilterManager::replaceQueryPlaceholders($query, $filters, $placeholderSettings);

    // Store the debug query
    $debugQuery = $testQuery;

    // Limit preview rows for test
    if (!preg_match('/\s+LIMIT\s+/i', $testQuery)) {
        $testQuery .= ' LIMIT ' . TABLE_MAX_PREVIEW_ROWS;
    }

    $res = $db->query($testQuery);

    if (!$res) {
        Utility::ajaxResponseFalse('Query error: ' . $db->getMysqlError());
    }

    // Fetch all rows
    $rows = array();
    while ($row = $db->fetchAssocArray($res)) {
        $rows[] = $row;
    }

    // Get columns from first row
    $columns = !empty($rows) ? array_keys($rows[0]) : array();
    $rowCount = count($rows);

    if ($rowCount === 0) {
        Utility::ajaxResponseFalse('Query returned no data. Please check your query.');
    }

    Utility::ajaxResponseTrue('Query is valid', array(
        'columns' => $columns,
        'rows' => $rows,
        'row_count' => $rowCount,
        'debug_query' => $debugQuery
    ));
}

/**
 * Preview table with query execution
 */
function previewTable($data)
{
    $tableId = isset($data['id']) ? intval($data['id']) : 0;
    $page = isset($data['page']) ? intval($data['page']) : 1;
    $rowsPerPage = isset($data['rows_per_page']) ? intval($data['rows_per_page']) : TABLE_DEFAULT_ROWS_PER_PAGE;

    if ($tableId) {
        // Load existing table
        $table = new WidgetTable($tableId);
        if (!$table->getId()) {
            Utility::ajaxResponseFalse('Table not found');
        }

        $filters = isset($data['filters']) ? $data['filters'] : array();
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        $tableData = $table->execute($filters ? $filters : array());
        $configJson = $table->getConfig();
        $config = $configJson ? json_decode($configJson, true) : array();

        // Ensure config is always an array
        if (!is_array($config)) {
            $config = array();
        }

        Utility::ajaxResponseTrue('Table data loaded', array(
            'tableData' => $tableData,
            'config' => $config
        ));
    } else {
        // Preview with provided data
        $query = isset($data['query']) ? trim($data['query']) : '';
        $filters = isset($data['filters']) ? $data['filters'] : array();
        $placeholderSettings = isset($data['placeholder_settings']) ? $data['placeholder_settings'] : array();

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
        $missingRequired = DataFilterManager::validateRequiredPlaceholders($query, $filters, $placeholderSettings);
        if (!empty($missingRequired)) {
            $filterNames = array_map(function($p) { return ltrim($p, ':'); }, $missingRequired);
            Utility::ajaxResponseFalse('Required filter(s) missing value: ' . implode(', ', $filterNames));
        }

        $db = Rapidkart::getInstance()->getDB();

        // Replace placeholders with filter values
        $query = DataFilterManager::replaceQueryPlaceholders($query, $filters, $placeholderSettings);

        // Apply pagination limit
        if (!preg_match('/\s+LIMIT\s+/i', $query)) {
            $query .= ' LIMIT ' . TABLE_MAX_PREVIEW_ROWS;
        }

        $res = $db->query($query);

        if (!$res) {
            Utility::ajaxResponseFalse('Query error: ' . $db->getMysqlError());
        }

        // Fetch all rows
        $rows = array();
        while ($row = $db->fetchAssocArray($res)) {
            $rows[] = $row;
        }

        $columns = !empty($rows) ? array_keys($rows[0]) : array();
        $tableData = array(
            'columns' => $columns,
            'rows' => $rows,
            'total_rows' => count($rows)
        );

        Utility::ajaxResponseTrue('Preview generated', array('tableData' => $tableData));
    }
}

/**
 * Load table data for editing
 */
function loadTable($data)
{
    $tableId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$tableId) {
        Utility::ajaxResponseFalse('Invalid table ID');
    }

    $table = new WidgetTable($tableId);
    if (!$table->getId()) {
        Utility::ajaxResponseFalse('Table not found');
    }

    // Extract placeholders and find matching filters
    $placeholders = DataFilterManager::extractPlaceholders($table->getQuery());
    $matchedFilters = DataFilterManager::getByKeys($placeholders);

    $response = $table->toArray();
    $response['placeholders'] = $placeholders;
    $response['matched_filters'] = array();
    foreach ($matchedFilters as $key => $filter) {
        $response['matched_filters'][$key] = $filter->toArray();
    }

    Utility::ajaxResponseTrue('Table loaded', $response);
}

/**
 * Save table snapshot image to filesystem and database
 */
function tableSaveSnapshot($data)
{
    $tid = isset($data['tid']) ? intval($data['tid']) : 0;
    $imageData = isset($data['image_data']) ? $data['image_data'] : '';

    if (!$tid || !WidgetTable::isExistent($tid)) {
        Utility::ajaxResponseFalse('Invalid table');
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

    // Generate filename: table_{tid}_{timestamp}.png
    $filename = 'table_' . $tid . '_' . time() . '.png';

    // Create directories
    $baseDir = SiteConfig::filesDirectory() . 'table/';
    $largeDir = $baseDir . 'large/';
    $thumbDir = $baseDir . 'thumbnail/';

    if (!is_dir($largeDir)) {
        mkdir($largeDir, 0755, true);
    }
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }

    // Delete old snapshots if exist
    $table = new WidgetTable($tid);
    if ($table->getSnapshot()) {
        $oldLarge = $largeDir . $table->getSnapshot();
        $oldThumb = $thumbDir . $table->getSnapshot();
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
    $largeImage = imagecreatetruecolor(TABLE_SNAPSHOT_LARGE_WIDTH, TABLE_SNAPSHOT_LARGE_HEIGHT);
    $white = imagecolorallocate($largeImage, 255, 255, 255);
    imagefill($largeImage, 0, 0, $white);
    imagecopyresampled(
        $largeImage,
        $srcImage,
        0,
        0,
        0,
        0,
        TABLE_SNAPSHOT_LARGE_WIDTH,
        TABLE_SNAPSHOT_LARGE_HEIGHT,
        $srcWidth,
        $srcHeight
    );
    imagepng($largeImage, $largeDir . $filename, 6);
    imagedestroy($largeImage);

    // Create and save THUMBNAIL version
    $thumbImage = imagecreatetruecolor(TABLE_SNAPSHOT_THUMB_WIDTH, TABLE_SNAPSHOT_THUMB_HEIGHT);
    $white = imagecolorallocate($thumbImage, 255, 255, 255);
    imagefill($thumbImage, 0, 0, $white);
    imagecopyresampled(
        $thumbImage,
        $srcImage,
        0,
        0,
        0,
        0,
        TABLE_SNAPSHOT_THUMB_WIDTH,
        TABLE_SNAPSHOT_THUMB_HEIGHT,
        $srcWidth,
        $srcHeight
    );
    imagepng($thumbImage, $thumbDir . $filename, 6);
    imagedestroy($thumbImage);

    imagedestroy($srcImage);

    // Update database
    $table->setSnapshot($filename);
    $table->setUpdatedUid(Session::loggedInUid());
    if (!$table->updateSnapshot()) {
        Utility::ajaxResponseFalse('Failed to update table');
    }

    Utility::ajaxResponseTrue('Image saved successfully', array(
        'filename' => $filename,
        'large_url' => SiteConfig::filesUrl() . 'table/large/' . $filename,
        'thumb_url' => SiteConfig::filesUrl() . 'table/thumbnail/' . $filename
    ));
}
