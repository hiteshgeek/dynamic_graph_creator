<?php

/**
 * Home Controller
 * Displays the main dashboard listing (home page)
 */

// Load dashboard module assets for styling
LocalUtility::addModuleCss('dashboard');
LocalUtility::addModuleJs('dashboard');

// Add page-specific JS
LocalUtility::addPageScript('dashboard', 'dashboard-list');

$theme = Rapidkart::getInstance()->getThemeRegistry();
$theme->setPageTitle('Home - Dynamic Graph Creator');

// Get user's dashboards
$userId = Session::loggedInUid();

$tpl = new Template(SystemConfig::templatesPath() . 'home/views/home-list');
$tpl->dashboards = DashboardInstance::getUserDashboards($userId);
$theme->setContent('full_main', $tpl->parse());
