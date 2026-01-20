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
require_once __DIR__ . '/system/utilities/SiteConfig.php';
require_once __DIR__ . '/system/config/BaseConfig.php';
require_once __DIR__ . '/system/utilities/SystemTables.php';
require_once __DIR__ . '/system/interfaces/DatabaseObject.php';
require_once __DIR__ . '/system/classes/SQLiDatabase.php';
require_once __DIR__ . '/system/classes/ThemeRegistry.php';
require_once __DIR__ . '/system/classes/Rapidkart.php';
require_once __DIR__ . '/system/classes/DGCHelper.php';
require_once __DIR__ . '/system/classes/Template.php';
require_once __DIR__ . '/system/classes/Utility.php';
require_once __DIR__ . '/system/classes/DataFilter.php';
require_once __DIR__ . '/system/classes/DataFilterSet.php';
require_once __DIR__ . '/system/classes/Graph.php';
require_once __DIR__ . '/system/classes/GraphManager.php';
require_once __DIR__ . '/system/classes/DataFilterManager.php';
require_once __DIR__ . '/system/classes/DashboardTemplateCategory.php';
require_once __DIR__ . '/system/classes/DashboardTemplate.php';
require_once __DIR__ . '/system/classes/DashboardInstance.php';
require_once __DIR__ . '/system/classes/DashboardBuilder.php';

// Load Rapidkart stub classes (only defines classes if they don't exist)
// These provide test data for company/outlet filters in DGC dev environment
require_once __DIR__ . '/system/classes/RapidkartStubs.php';

// Parse URL
$url = Utility::parseUrl();
$page = isset($url[0]) ? $url[0] : 'graph';

// Load common assets for all DGC pages
$theme = Rapidkart::getInstance()->getThemeRegistry();
Utility::addModuleCss('common');
Utility::addModuleJs('common');
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'bootstrap5/js/bootstrap.bundle.min.js', 5);

// Route to controller
switch ($page) {
    case 'data-filter':
        require_once SystemConfig::includesPath() . 'data-filter/data-filter.inc.php';
        break;
    case 'dashboard':
        require_once SystemConfig::includesPath() . 'dashboard/dashboard.inc.php';
        break;
    case 'graph':
    default:
        require_once SystemConfig::includesPath() . 'graph/graph.inc.php';
        break;
}

// Render the page
Rapidkart::getInstance()->getThemeRegistry()->renderPage();
