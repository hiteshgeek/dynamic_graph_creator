<?php

/**
 * Entry point for Dynamic Graph Creator
 * Routes all requests to appropriate controllers
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration and classes
require_once __DIR__ . '/system/utilities/SystemConfig.php';
require_once __DIR__ . '/system/config/BaseConfig.php';
require_once __DIR__ . '/system/utilities/SystemTables.php';
require_once __DIR__ . '/system/interfaces/DatabaseObject.php';
require_once __DIR__ . '/system/classes/SQLiDatabase.php';
require_once __DIR__ . '/system/classes/Rapidkart.php';
require_once __DIR__ . '/system/classes/Utility.php';
require_once __DIR__ . '/system/classes/Filter.php';
require_once __DIR__ . '/system/classes/Graph.php';
require_once __DIR__ . '/system/classes/LayoutTemplate.php';
require_once __DIR__ . '/system/classes/LayoutInstance.php';
require_once __DIR__ . '/system/classes/LayoutBuilder.php';

// Parse URL
$url = Utility::parseUrl();
$page = isset($url[0]) ? $url[0] : 'graph';

// Route to controller
switch ($page) {
    case 'filters':
        require_once SystemConfig::includesPath() . 'filter/filter.inc.php';
        break;
    case 'layout':
        require_once SystemConfig::includesPath() . 'layout/layout.inc.php';
        break;
    case 'graph':
    default:
        require_once SystemConfig::includesPath() . 'graph/graph.inc.php';
        break;
}
