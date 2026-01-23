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
require_once __DIR__ . '/system/utilities/LocalProjectConfig.php';
require_once __DIR__ . '/system/config/BaseConfig.php';
require_once __DIR__ . '/system/utilities/SystemTables.php';
require_once __DIR__ . '/system/utilities/SystemTablesStatus.php';
require_once __DIR__ . '/system/includes/functions.inc.php';
require_once __DIR__ . '/system/interfaces/DatabaseObject.php';
require_once __DIR__ . '/system/interfaces/User.php';
require_once __DIR__ . '/system/interfaces/UniqueIdentifier.php';
require_once __DIR__ . '/system/classes/SQLiDatabase.php';
require_once __DIR__ . '/system/classes/ThemeRegistry.php';
require_once __DIR__ . '/system/classes/Rapidkart.php';
require_once __DIR__ . '/system/classes/SystemPreferences.php';
require_once __DIR__ . '/system/classes/SiteVariable.php';
require_once __DIR__ . '/system/classes/SiteVariableManager.php';
require_once __DIR__ . '/system/classes/SiteVariableUpdateLog.php';
require_once __DIR__ . '/system/classes/SystemPreferences.php';
require_once __DIR__ . '/system/classes/SystemPreferencesManager.php';
require_once __DIR__ . '/system/classes/SystemPreferencesGroup.php';
require_once __DIR__ . '/system/classes/SystemPreferencesMapping.php';
require_once __DIR__ . '/system/classes/DGCHelper.php';
require_once __DIR__ . '/system/classes/Template.php';
require_once __DIR__ . '/system/classes/System.php';
require_once __DIR__ . '/system/classes/JPath.php';
require_once __DIR__ . '/system/classes/Utility.php';
require_once __DIR__ . '/system/classes/LocalUtility.php';
require_once __DIR__ . '/system/classes/QueryHelper.php';
require_once __DIR__ . '/system/classes/WidgetType.php';
require_once __DIR__ . '/system/classes/WidgetTypeManager.php';
require_once __DIR__ . '/system/classes/FilterWidgetTypeMandatoryManager.php';
require_once __DIR__ . '/system/classes/DataFilter.php';
require_once __DIR__ . '/system/classes/DataFilterSet.php';
require_once __DIR__ . '/system/classes/Graph.php';
require_once __DIR__ . '/system/classes/GraphManager.php';
require_once __DIR__ . '/system/classes/DataFilterManager.php';
require_once __DIR__ . '/system/classes/SystemPlaceholder.php';
require_once __DIR__ . '/system/classes/SystemPlaceholderManager.php';
require_once __DIR__ . '/system/classes/DashboardTemplateCategory.php';
require_once __DIR__ . '/system/classes/DashboardTemplate.php';
require_once __DIR__ . '/system/classes/DashboardInstance.php';
require_once __DIR__ . '/system/classes/DashboardBuilder.php';
require_once __DIR__ . '/system/classes/WidgetCategory.php';
require_once __DIR__ . '/system/classes/WidgetCategoryManager.php';
require_once __DIR__ . '/system/classes/GraphWidgetCategoryMapping.php';
require_once __DIR__ . '/system/classes/GraphWidgetCategoryMappingManager.php';
require_once __DIR__ . '/system/classes/Counter.php';
require_once __DIR__ . '/system/classes/CounterManager.php';
require_once __DIR__ . '/system/classes/CounterWidgetCategoryMapping.php';
require_once __DIR__ . '/system/classes/CounterWidgetCategoryMappingManager.php';

// Load session and authentication classes
require_once __DIR__ . '/system/classes/AdminUser.php';
require_once __DIR__ . '/system/classes/AdminUserManager.php';
require_once __DIR__ . '/system/classes/Session.php';
require_once __DIR__ . '/system/classes/SessionsManager.php';
require_once __DIR__ . '/system/classes/SessionDetails.php';
require_once __DIR__ . '/system/classes/Licence.php';
require_once __DIR__ . '/system/classes/LicenceCompanies.php';
require_once __DIR__ . '/system/classes/LicenceManager.php';
require_once __DIR__ . '/system/classes/LicenceDomain.php';
require_once __DIR__ . '/system/classes/Outlet.php';
require_once __DIR__ . '/system/classes/OutletManager.php';
require_once __DIR__ . '/system/classes/Warehouse.php';
require_once __DIR__ . '/system/classes/WarehouseManager.php';
require_once __DIR__ . '/system/classes/SimulateLogin.php';

// Initialize session
Session::init();

// Auto-login for development (only if not logged in)
if (!Session::isLoggedIn()) {
    SimulateLogin::loginByEmail('admin@agt.com');
    header('Location: .?urlq=widget-graph');
    exit;
}
// Restore BaseConfig values from session (like Rapidkart's system.inc.php)
// Must set licence_id first since many classes depend on it
if (Session::isLoggedIn()) {
    if (isset($_SESSION['licence_id']) && $_SESSION['licence_id'] > 0) {
        BaseConfig::$licence_id = (int) $_SESSION['licence_id'];
    }
    if (isset($_SESSION['company_id']) && $_SESSION['company_id'] > 0) {
        BaseConfig::$company_id = (int) $_SESSION['company_id'];
    }
}

// Parse URL
$url = LocalUtility::parseUrl();
$page = isset($url[0]) ? $url[0] : 'widget-graph';

// Manual logout for testing (uncomment when needed):
// SimulateLogin::logout();
// header('Location: .?urlq=login'); exit;

// Check if user is logged in (same as live project)
// Allow 'login' page without authentication
if (!Session::isLoggedIn(true)) {
    if ($page === 'login') {
        // Handle login page - will be created later or redirect to main system login
        require_once SystemConfig::includesPath() . 'login/login.inc.php';
        exit;
    } else {
        // Not logged in and trying to access secure page
        // For now, show a simple message. In live, this would redirect to login page.
        header('HTTP/1.1 401 Unauthorized');
        echo '<h1>Access Denied</h1>';
        echo '<p>You must be logged in to access this page.</p>';
        echo '<p>Use <code>simulate_login.php</code> to login for testing, or access via the main Rapidkart system.</p>';
        exit;
    }
}

// Load common assets for all DGC pages
$theme = Rapidkart::getInstance()->getThemeRegistry();
LocalUtility::addModuleCss('common');
LocalUtility::addModuleJs('common');
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'bootstrap5/js/bootstrap.bundle.min.js', 5);

function load_404()
{
    $tpl = new Template(SystemConfig::templatesPath() . "404");
    return $tpl->parse();
}

function load_403()
{
    $tpl = new Template(SystemConfig::templatesPath() . "403");
    return $tpl->parse();
}

// Route to controller
switch ($page) {
    case 'home':
        require_once SystemConfig::includesPath() . 'home/home.inc.php';
        break;
    case 'data-filter':
        require_once SystemConfig::includesPath() . 'data-filter/data-filter.inc.php';
        break;
    case 'dashboard':
        require_once SystemConfig::includesPath() . 'dashboard/dashboard.inc.php';
        break;
    case 'migrate':
        // Migration tool - standalone page with its own HTML
        require_once SystemConfig::includesPath() . 'migrate/migrate.inc.php';
        break; // migrate.inc.php calls exit after rendering

    // Widget routes
    case 'widget-graph':
        require_once SystemConfig::includesPath() . 'graph/graph.inc.php';
        break;
    case 'widget-table':
        // TODO: Add table widget controller
        require_once SystemConfig::includesPath() . 'widget-table/widget-table.inc.php';
        break;
    case 'widget-list':
        // TODO: Add list widget controller
        require_once SystemConfig::includesPath() . 'widget-list/widget-list.inc.php';
        break;
    case 'widget-counter':
        require_once SystemConfig::includesPath() . 'counter/counter.inc.php';
        break;

    // Backward compatibility - redirect old graph route to widget-graph
    case 'graph':
        header('Location: .?urlq=widget-graph' . (isset($url[1]) ? '/' . implode('/', array_slice($url, 1)) : ''));
        exit;

    default:
        require_once SystemConfig::includesPath() . 'graph/graph.inc.php';
        break;
}

// Render the page
Rapidkart::getInstance()->getThemeRegistry()->renderPage();
