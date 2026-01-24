<?php

/**
 * Counter Controller
 * Handles all counter-related actions
 */

// Counter snapshot image size constants
define('COUNTER_SNAPSHOT_LARGE_WIDTH', 600);
define('COUNTER_SNAPSHOT_LARGE_HEIGHT', 300);
define('COUNTER_SNAPSHOT_THUMB_WIDTH', 300);
define('COUNTER_SNAPSHOT_THUMB_HEIGHT', 150);

// Require admin access (company 232 + admin user)
DGCHelper::requireAdminAccess();

// Load counter module assets (common assets loaded in index.php)
LocalUtility::addModuleCss('counter');
LocalUtility::addModuleJs('counter');

// $url is already parsed in index.php
$action = isset($url[1]) ? $url[1] : 'list';

// Handle POST actions
if (isset($_POST['submit'])) {
    switch ($_POST['submit']) {
        case 'save_counter':
            saveCounter($_POST);
            break;
        case 'delete_counter':
            deleteCounter($_POST);
            break;
        case 'test_query':
            testCounterQuery($_POST);
            break;
        case 'preview_counter':
            previewCounter($_POST);
            break;
        case 'load_counter':
            loadCounter($_POST);
            break;
        case 'counter-save-snapshot':
            counterSaveSnapshot($_POST);
            break;
    }
}

// Handle GET actions
switch ($action) {
    case 'create':
        showCreator();
        break;
    case 'edit':
        $counterId = isset($url[2]) ? intval($url[2]) : 0;
        showCreator($counterId);
        break;
    case 'view':
        $counterId = isset($url[2]) ? intval($url[2]) : 0;
        showView($counterId);
        break;
    case 'list':
    default:
        showList();
        break;
}

/**
 * Show counter list page
 */
function showList()
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific JS
    LocalUtility::addPageScript('counter', 'counter-list');

    $theme->setPageTitle('Counters - Dynamic Graph Creator');

    // Get all counters
    $counters = WidgetCounterManager::getAll();

    // Get categories for each counter
    $counterCategories = array();
    foreach ($counters as $counter) {
        $counterCategories[$counter->getId()] = WidgetCounterCategoryMappingManager::getCategoriesForCounter($counter->getId());
    }

    // Get content from template
    $tpl = new Template(SystemConfig::templatesPath() . 'counter/views/counter-list');
    $tpl->counters = $counters;
    $tpl->counterCategories = $counterCategories;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show counter creator/editor
 */
function showCreator($counterId = null)
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
    LocalUtility::addPageScript('counter', 'counter-creator');

    $counter = null;

    if ($counterId) {
        $counter = new WidgetCounter($counterId);
        if (!$counter->getId()) {
            LocalUtility::redirect('widget-counter');
            return;
        }
    }

    $theme->setPageTitle('Counters - ' . ($counter ? 'Edit' : 'Create') . ' Counter - Dynamic Graph Creator');

    // Get all available filters for selection
    $allFilters = DataFilterManager::getAllAsArray();

    // Get mandatory filters for widget type "counter"
    $mandatoryFilters = DataFilterManager::getMandatoryFiltersForWidgetTypeAsArray('counter');
    $mandatoryFilterKeys = array_map(function($f) {
        return ltrim($f['filter_key'], ':');
    }, $mandatoryFilters);

    // Permission to create filters (replace with actual framework permission check)
    $canCreateFilter = true;

    // Get all widget categories
    $categories = WidgetCategoryManager::getAllAsArray();

    // Get selected category IDs for this counter (if editing)
    $selectedCategoryIds = array();
    if ($counter && $counter->getId()) {
        $selectedCategoryIds = WidgetCounterCategoryMappingManager::getCategoryIdsForCounter($counter->getId());
    }

    // Get format options
    $formatOptions = WidgetCounter::getFormatOptions();

    $tpl = new Template(SystemConfig::templatesPath() . 'counter/forms/counter-creator');
    $tpl->counter = $counter;
    $tpl->allFilters = $allFilters;
    $tpl->mandatoryFilters = $mandatoryFilters;
    $tpl->mandatoryFilterKeys = $mandatoryFilterKeys;
    $tpl->canCreateFilter = $canCreateFilter;
    $tpl->categories = $categories;
    $tpl->selectedCategoryIds = $selectedCategoryIds;
    $tpl->formatOptions = $formatOptions;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show counter view page
 */
function showView($counterId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries - jQuery and daterangepicker must load BEFORE dist JS (which has weight 10+)
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery3/jquery.min.js', 1);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment-dgc/moment.min.js', 2);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/js/daterangepicker.min.js', 3);

    $counter = new WidgetCounter($counterId);
    if (!$counter->getId()) {
        LocalUtility::redirect('widget-counter');
        return;
    }

    $theme->setPageTitle('Counters - ' . htmlspecialchars($counter->getName()) . ' - Dynamic Graph Creator');

    // Get all counters for navigation
    $allCounters = WidgetCounterManager::getAll();
    $totalCounters = count($allCounters);
    $currentIndex = 0;
    $prevCounterId = null;
    $nextCounterId = null;

    // Find current counter position and prev/next IDs
    foreach ($allCounters as $index => $c) {
        if ($c->getId() == $counterId) {
            $currentIndex = $index + 1; // 1-based for display
            if ($index > 0) {
                $prevCounterId = $allCounters[$index - 1]->getId();
            }
            if ($index < $totalCounters - 1) {
                $nextCounterId = $allCounters[$index + 1]->getId();
            }
            break;
        }
    }

    // Extract placeholders from the counter query and find matching filters
    $placeholders = DataFilterManager::extractPlaceholders($counter->getQuery());
    $matchedFilters = DataFilterManager::getByKeys($placeholders);

    // Convert to array format for template
    $filters = array();
    foreach ($matchedFilters as $key => $filter) {
        $filters[] = $filter->toArray();
    }

    // Get categories for this counter
    $counterCategories = WidgetCounterCategoryMappingManager::getCategoriesForCounter($counterId);

    // Get mandatory filters for widget type "counter"
    $mandatoryFilters = DataFilterManager::getMandatoryFiltersForWidgetTypeAsArray('counter');
    $mandatoryFilterKeys = array_map(function($f) {
        return ltrim($f['filter_key'], ':');
    }, $mandatoryFilters);

    $tpl = new Template(SystemConfig::templatesPath() . 'counter/views/counter-view');
    $tpl->counter = $counter;
    $tpl->filters = $filters;
    $tpl->mandatoryFilterKeys = $mandatoryFilterKeys;
    $tpl->categories = $counterCategories;
    $tpl->allCounters = $allCounters;
    $tpl->totalCounters = $totalCounters;
    $tpl->currentIndex = $currentIndex;
    $tpl->prevCounterId = $prevCounterId;
    $tpl->nextCounterId = $nextCounterId;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Save counter (create or update)
 */
function saveCounter($data)
{
    $counterId = isset($data['id']) ? intval($data['id']) : 0;
    $isUpdate = $counterId > 0;
    $userId = Session::loggedInUid();

    $query = isset($data['query']) ? $data['query'] : '';

    // Validate query security (SELECT only)
    if (!QueryHelper::validateOrFail($query)) {
        return; // Error response already sent
    }

    // Validate mandatory filters are in query
    $mandatoryValidation = QueryHelper::validateMandatoryFiltersInQuery($query, 'counter');
    if (!$mandatoryValidation['valid']) {
        $missingPlaceholders = array_map(function($key) {
            return '::' . ltrim($key, ':');
        }, $mandatoryValidation['missing']);
        $message = count($missingPlaceholders) === 1
            ? 'Query must include mandatory filter: ' . implode(', ', $missingPlaceholders)
            : 'Query must include mandatory filters: ' . implode(', ', $missingPlaceholders);
        DGCHelper::ajaxResponseFalse($message);
    }

    $counter = $isUpdate ? new WidgetCounter($counterId) : new WidgetCounter();

    $counter->setName(isset($data['name']) ? $data['name'] : '');
    $counter->setDescription(isset($data['description']) ? $data['description'] : '');
    $counter->setQuery($query);

    $config = isset($data['config']) ? $data['config'] : '{}';
    if (is_string($config)) {
        $counter->setConfig($config);
    } else {
        $counter->setConfig(json_encode($config));
    }

    $placeholderSettings = isset($data['placeholder_settings']) ? $data['placeholder_settings'] : '{}';
    if (is_string($placeholderSettings)) {
        $counter->setPlaceholderSettings($placeholderSettings);
    } else {
        $counter->setPlaceholderSettings(json_encode($placeholderSettings));
    }

    if ($isUpdate) {
        $counter->setUpdatedUid($userId);
        if (!$counter->update()) {
            DGCHelper::ajaxResponseFalse('Failed to update counter');
        }
    } else {
        $counter->setCreatedUid($userId);
        if (!$counter->insert()) {
            DGCHelper::ajaxResponseFalse('Failed to create counter');
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
    WidgetCounterCategoryMappingManager::setCounterCategories($counter->getId(), $categoryIds);

    $message = $isUpdate ? 'Counter updated successfully' : 'Counter created successfully';
    DGCHelper::ajaxResponseTrue($message, array('id' => $counter->getId()));
}

/**
 * Delete counter
 */
function deleteCounter($data)
{
    $counterId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$counterId || !WidgetCounter::delete($counterId)) {
        DGCHelper::ajaxResponseFalse('Failed to delete counter');
    }

    DGCHelper::ajaxResponseTrue('Counter deleted successfully');
}

/**
 * Test SQL query for counter (should return single row with 'counter' column)
 */
function testCounterQuery($data)
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
        DGCHelper::ajaxResponseFalse('Please enter a SQL query');
    }

    // Validate query security (SELECT only)
    if (!QueryHelper::validateOrFail($query)) {
        return; // Error response already sent
    }

    // Validate mandatory filters are in query
    $mandatoryValidation = QueryHelper::validateMandatoryFiltersInQuery($query, 'counter');
    if (!$mandatoryValidation['valid']) {
        $missingPlaceholders = array_map(function($key) {
            return '::' . ltrim($key, ':');
        }, $mandatoryValidation['missing']);
        $message = count($missingPlaceholders) === 1
            ? 'Query must include mandatory filter: ' . implode(', ', $missingPlaceholders)
            : 'Query must include mandatory filters: ' . implode(', ', $missingPlaceholders);
        DGCHelper::ajaxResponseFalse($message);
    }

    // Validate required placeholders have values
    $missingRequired = DataFilterManager::validateRequiredPlaceholders($query, $filters, $placeholderSettings);
    if (!empty($missingRequired)) {
        $filterNames = array_map(function($p) { return ltrim($p, ':'); }, $missingRequired);
        DGCHelper::ajaxResponseFalse('Required filter(s) missing value: ' . implode(', ', $filterNames));
    }

    $db = Rapidkart::getInstance()->getDB();
    $testQuery = DataFilterManager::replaceQueryPlaceholders($query, $filters, $placeholderSettings);

    // Store the debug query
    $debugQuery = $testQuery;

    // Ensure only 1 row is returned for counter
    if (!preg_match('/\s+LIMIT\s+/i', $testQuery)) {
        $testQuery .= ' LIMIT 1';
    }

    $res = $db->query($testQuery);

    if (!$res) {
        DGCHelper::ajaxResponseFalse('Query error: ' . $db->getMysqlError());
    }

    // Fetch the row
    $row = $db->fetchAssocArray($res);
    $columns = $row ? array_keys($row) : array();

    if (empty($row)) {
        DGCHelper::ajaxResponseFalse('Query returned no data. Please check your query.');
    }

    // Check for 'counter' column
    $hasCounterColumn = isset($row['counter']);
    $warning = null;
    if (!$hasCounterColumn) {
        $warning = "Query should return a column named 'counter'. Currently returning: " . implode(', ', $columns);
    }

    DGCHelper::ajaxResponseTrue('Query is valid', array(
        'columns' => $columns,
        'row' => $row,
        'row_count' => 1, // Counter queries always return 1 row
        'has_counter_column' => $hasCounterColumn,
        'warning' => $warning,
        'debug_query' => $debugQuery
    ));
}

/**
 * Preview counter with query execution
 */
function previewCounter($data)
{
    $counterId = isset($data['id']) ? intval($data['id']) : 0;

    if ($counterId) {
        // Load existing counter
        $counter = new WidgetCounter($counterId);
        if (!$counter->getId()) {
            DGCHelper::ajaxResponseFalse('Counter not found');
        }

        $filters = isset($data['filters']) ? $data['filters'] : array();
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        $counterData = $counter->execute($filters ? $filters : array());
        $configJson = $counter->getConfig();
        $config = $configJson ? json_decode($configJson, true) : array();

        // Ensure config is always an array
        if (!is_array($config)) {
            $config = array();
        }

        DGCHelper::ajaxResponseTrue('Counter data loaded', array(
            'counterData' => $counterData,
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
            DGCHelper::ajaxResponseFalse('No query provided');
        }

        // Validate required placeholders have values
        $missingRequired = DataFilterManager::validateRequiredPlaceholders($query, $filters, $placeholderSettings);
        if (!empty($missingRequired)) {
            $filterNames = array_map(function($p) { return ltrim($p, ':'); }, $missingRequired);
            DGCHelper::ajaxResponseFalse('Required filter(s) missing value: ' . implode(', ', $filterNames));
        }

        $db = Rapidkart::getInstance()->getDB();

        // Replace placeholders with filter values
        $query = DataFilterManager::replaceQueryPlaceholders($query, $filters, $placeholderSettings);

        // Ensure only 1 row
        if (!preg_match('/\s+LIMIT\s+/i', $query)) {
            $query .= ' LIMIT 1';
        }

        $res = $db->query($query);

        if (!$res) {
            DGCHelper::ajaxResponseFalse('Query error: ' . $db->getMysqlError());
        }

        $row = $db->fetchAssocArray($res);
        $counterData = formatCounterData($row);

        DGCHelper::ajaxResponseTrue('Preview generated', array('counterData' => $counterData));
    }
}

/**
 * Format counter data from query result
 */
function formatCounterData($row)
{
    if (empty($row)) {
        return array(
            'value' => 0,
            'error' => 'No data returned'
        );
    }

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
 * Load counter data for editing
 */
function loadCounter($data)
{
    $counterId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$counterId) {
        DGCHelper::ajaxResponseFalse('Invalid counter ID');
    }

    $counter = new WidgetCounter($counterId);
    if (!$counter->getId()) {
        DGCHelper::ajaxResponseFalse('Counter not found');
    }

    // Extract placeholders and find matching filters
    $placeholders = DataFilterManager::extractPlaceholders($counter->getQuery());
    $matchedFilters = DataFilterManager::getByKeys($placeholders);

    $response = $counter->toArray();
    $response['placeholders'] = $placeholders;
    $response['matched_filters'] = array();
    foreach ($matchedFilters as $key => $filter) {
        $response['matched_filters'][$key] = $filter->toArray();
    }

    DGCHelper::ajaxResponseTrue('Counter loaded', $response);
}

/**
 * Save counter snapshot image to filesystem and database
 */
function counterSaveSnapshot($data)
{
    $cid = isset($data['cid']) ? intval($data['cid']) : 0;
    $imageData = isset($data['image_data']) ? $data['image_data'] : '';

    if (!$cid || !WidgetCounter::isExistent($cid)) {
        DGCHelper::ajaxResponseFalse('Invalid counter');
    }

    // Validate base64 PNG data
    if (!preg_match('/^data:image\/png;base64,/', $imageData)) {
        DGCHelper::ajaxResponseFalse('Invalid image data');
    }

    // Decode base64
    $base64 = preg_replace('/^data:image\/png;base64,/', '', $imageData);
    $binary = base64_decode($base64);

    if ($binary === false) {
        DGCHelper::ajaxResponseFalse('Failed to decode image data');
    }

    // Generate filename: counter_{cid}_{timestamp}.png
    $filename = 'counter_' . $cid . '_' . time() . '.png';

    // Create directories
    $baseDir = SiteConfig::filesDirectory() . 'counter/';
    $largeDir = $baseDir . 'large/';
    $thumbDir = $baseDir . 'thumbnail/';

    if (!is_dir($largeDir)) {
        mkdir($largeDir, 0755, true);
    }
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }

    // Delete old snapshots if exist
    $counter = new WidgetCounter($cid);
    if ($counter->getSnapshot()) {
        $oldLarge = $largeDir . $counter->getSnapshot();
        $oldThumb = $thumbDir . $counter->getSnapshot();
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
        DGCHelper::ajaxResponseFalse('Failed to process image');
    }

    $srcWidth = imagesx($srcImage);
    $srcHeight = imagesy($srcImage);

    // Create and save LARGE version
    $largeImage = imagecreatetruecolor(COUNTER_SNAPSHOT_LARGE_WIDTH, COUNTER_SNAPSHOT_LARGE_HEIGHT);
    $white = imagecolorallocate($largeImage, 255, 255, 255);
    imagefill($largeImage, 0, 0, $white);
    imagecopyresampled(
        $largeImage,
        $srcImage,
        0,
        0,
        0,
        0,
        COUNTER_SNAPSHOT_LARGE_WIDTH,
        COUNTER_SNAPSHOT_LARGE_HEIGHT,
        $srcWidth,
        $srcHeight
    );
    imagepng($largeImage, $largeDir . $filename, 6);
    imagedestroy($largeImage);

    // Create and save THUMBNAIL version
    $thumbImage = imagecreatetruecolor(COUNTER_SNAPSHOT_THUMB_WIDTH, COUNTER_SNAPSHOT_THUMB_HEIGHT);
    $white = imagecolorallocate($thumbImage, 255, 255, 255);
    imagefill($thumbImage, 0, 0, $white);
    imagecopyresampled(
        $thumbImage,
        $srcImage,
        0,
        0,
        0,
        0,
        COUNTER_SNAPSHOT_THUMB_WIDTH,
        COUNTER_SNAPSHOT_THUMB_HEIGHT,
        $srcWidth,
        $srcHeight
    );
    imagepng($thumbImage, $thumbDir . $filename, 6);
    imagedestroy($thumbImage);

    imagedestroy($srcImage);

    // Update database
    $counter->setSnapshot($filename);
    $counter->setUpdatedUid(Session::loggedInUid());
    if (!$counter->updateSnapshot()) {
        DGCHelper::ajaxResponseFalse('Failed to update counter');
    }

    DGCHelper::ajaxResponseTrue('Image saved successfully', array(
        'filename' => $filename,
        'large_url' => SiteConfig::filesUrl() . 'counter/large/' . $filename,
        'thumb_url' => SiteConfig::filesUrl() . 'counter/thumbnail/' . $filename
    ));
}
