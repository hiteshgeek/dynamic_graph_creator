<?php

/**
 * Dashboard Controller
 * Handles dashboard builder actions
 */

// Load dashboard module assets (common assets loaded in index.php)
LocalUtility::addModuleCss('dashboard');
LocalUtility::addModuleJs('dashboard');

// Permission control for template ordering operations
// Set to true to allow category reordering, template reordering, and moving templates between categories
$allowTemplateOrdering = true;

// $url is already parsed in index.php
$action = isset($url[1]) ? $url[1] : 'list';

// Handle POST actions
if (isset($_POST['submit'])) {
    switch ($_POST['submit']) {
        case 'get_templates':
            getTemplates($_POST);
            break;
        case 'create_from_template':
            createFromTemplate($_POST);
            break;
        case 'save_dashboard':
            saveDashboard($_POST);
            break;
        case 'get_dashboard':
            getDashboard($_POST);
            break;
        case 'delete_dashboard':
            deleteDashboard($_POST);
            break;
        case 'update_dashboard_name':
            updateDashboardName($_POST);
            break;
        case 'update_dashboard_details':
            updateDashboardDetails($_POST);
            break;
        case 'update_area_content':
            updateAreaContent($_POST);
            break;
        case 'add_section':
            addSection($_POST);
            break;
        case 'add_section_from_template':
            addSectionFromTemplate($_POST);
            break;
        case 'remove_section':
            removeSection($_POST);
            break;
        case 'reorder_sections':
            reorderSections($_POST);
            break;
        case 'save_dashboard_filter_values':
            saveDashboardFilterValues($_POST);
            break;
        case 'get_dashboard_filter_values':
            getDashboardFilterValues($_POST);
            break;
        // Template management actions (require admin access)
        case 'create_template':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            createTemplate($_POST);
            break;
        case 'update_template':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            updateTemplate($_POST);
            break;
        case 'delete_template':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            deleteTemplate($_POST);
            break;
        case 'duplicate_template':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            duplicateTemplate($_POST);
            break;
        case 'save_template_structure':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            saveTemplateStructure($_POST);
            break;
        case 'get_template':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            getTemplate($_POST);
            break;
        case 'remove_template_section':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            removeTemplateSection($_POST);
            break;
        case 'delete_category':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            deleteCategory($_POST);
            break;
        case 'reorder_templates':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            reorderTemplates($_POST);
            break;
        case 'reorder_categories':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            reorderCategories($_POST);
            break;
        case 'move_template_category':
            if (!DGCHelper::hasAdminAccess()) { Utility::ajaxResponseFalse('Access denied'); }
            moveTemplateCategory($_POST);
            break;
        case 'get_filters_by_keys':
            getFiltersByKeys($_POST);
            break;
        case 'get_dashboard_filters':
            getDashboardFilters($_POST);
            break;
        case 'get_widgets_for_selector':
            getWidgetsForSelector($_POST);
            break;
        case 'preview_graph':
            previewGraphForDashboard($_POST);
            break;
        case 'preview_counter':
            previewCounterForDashboard($_POST);
            break;
        case 'preview_table':
            previewTableForDashboard($_POST);
            break;
    }
}

// Handle GET actions
switch ($action) {
    case 'builder':
        $dashboardId = isset($url[2]) ? intval($url[2]) : 0;
        showBuilder($dashboardId);
        break;
    case 'preview':
        $dashboardId = isset($url[2]) ? intval($url[2]) : 0;
        showPreview($dashboardId);
        break;
    case 'templates':
        // Require admin access for templates page
        DGCHelper::requireAdminAccess();
        showTemplateList();
        break;
    case 'template':
        // Require admin access for all template actions
        DGCHelper::requireAdminAccess();
        $subAction = isset($url[2]) ? $url[2] : '';
        switch ($subAction) {
            case 'create':
                showTemplateCreator();
                break;
            case 'edit':
                $templateId = isset($url[3]) ? intval($url[3]) : 0;
                showTemplateEditor($templateId);
                break;
            case 'builder':
                $templateId = isset($url[3]) ? intval($url[3]) : 0;
                showTemplateBuilder($templateId);
                break;
            case 'preview':
                $templateId = isset($url[3]) ? intval($url[3]) : 0;
                showTemplatePreview($templateId);
                break;
            default:
                LocalUtility::redirect('dashboard/templates');
                break;
        }
        break;
    case 'list':
    default:
        System::redirectInternal('home');
        break;
}

/**
 * Show dashboard list
 */
function showList()
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific JS
    LocalUtility::addPageScript('dashboard', 'dashboard-list');

    $theme->setPageTitle('Dashboards - Dynamic Graph Creator');

    // Get user's dashboards
    $userId = Session::loggedInUid();

    $tpl = new Template(SystemConfig::templatesPath() . 'dashboard/views/dashboard-list');
    $tpl->dashboards = DashboardInstance::getUserDashboards($userId);
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show dashboard builder
 */
function showBuilder($dashboardId = 0)
{
    // Require admin access for dashboard builder
    DGCHelper::requireAdminAccess();

    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'sortablejs-dgc/Sortable.min.js', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts-dgc/echarts.min.js', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize-dgc/autosize.min.js', 5);
    // jQuery and Daterangepicker for filter bar
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery3/jquery.min.js', 1);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment-dgc/moment.min.js', 2);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/js/daterangepicker.min.js', 3);

    // Add page-specific JS
    LocalUtility::addPageScript('dashboard', 'dashboard-builder');

    $dashboard = null;
    $templates = DashboardTemplate::getAllGrouped();

    if ($dashboardId) {
        $dashboard = new DashboardInstance($dashboardId);
        if (!$dashboard->getId()) {
            LocalUtility::redirect('dashboard');
            return;
        }
    }

    $theme->setPageTitle(($dashboard ? 'Edit Dashboard' : 'Create Dashboard') . ' - Dynamic Graph Creator');

    $tpl = new Template(SystemConfig::templatesPath() . 'dashboard/forms/dashboard-builder');
    $tpl->dashboard = $dashboard;
    $tpl->templates = $templates;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show dashboard preview
 */
function showPreview($dashboardId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts-dgc/echarts.min.js', 5);
    // jQuery and Daterangepicker for filter bar
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery3/jquery.min.js', 1);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment-dgc/moment.min.js', 2);
    $theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/css/daterangepicker.css', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker-dgc/js/daterangepicker.min.js', 3);

    // Add page-specific JS (weight 15 to load after common.js which has weight 10)
    LocalUtility::addPageScript('dashboard', 'dashboard-preview', 15);

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        LocalUtility::redirect('dashboard');
        return;
    }

    $theme->setPageTitle(htmlspecialchars($dashboard->getName()) . ' - Preview');

    $tpl = new Template(SystemConfig::templatesPath() . 'dashboard/views/dashboard-preview');
    $tpl->dashboard = $dashboard;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Get all templates grouped by category
 */
function getTemplates($data)
{
    $templates = DashboardTemplate::getAllGrouped();
    Utility::ajaxResponseTrue('Templates loaded', $templates);
}

/**
 * Create new dashboard from template
 */
function createFromTemplate($data)
{
    $templateId = isset($data['template_id']) ? intval($data['template_id']) : 0;
    $name = isset($data['name']) ? $data['name'] : 'New Dashboard';
    $description = isset($data['description']) ? $data['description'] : '';
    $userId = Session::loggedInUid();
    $companyId = BaseConfig::$company_id;

    if (!$templateId) {
        Utility::ajaxResponseFalse('Template ID required');
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    $instance = $template->createInstance($userId, $name, $description, $companyId);
    if (!$instance->insert()) {
        Utility::ajaxResponseFalse('Failed to create dashboard');
    }

    Utility::ajaxResponseTrue('Dashboard created', array(
        'id' => $instance->getId(),
        'name' => $instance->getName()
    ));
}

/**
 * Save dashboard (create or update)
 */
function saveDashboard($data)
{
    $dashboardId = isset($data['dashboard_id']) ? intval($data['dashboard_id']) : 0;
    $name = isset($data['name']) ? $data['name'] : '';
    $structure = isset($data['structure']) ? $data['structure'] : '';
    $config = isset($data['config']) ? $data['config'] : '{}';
    $userId = Session::loggedInUid();
    $companyId = BaseConfig::$company_id;

    if (empty($name)) {
        Utility::ajaxResponseFalse('Dashboard name is required');
    }

    if (empty($structure)) {
        Utility::ajaxResponseFalse('Dashboard structure is required');
    }

    // Validate structure
    $structureArray = json_decode($structure, true);
    if (!$structureArray) {
        Utility::ajaxResponseFalse('Invalid JSON structure');
    }

    if (!DashboardBuilder::validateStructure($structureArray)) {
        Utility::ajaxResponseFalse('Invalid dashboard structure');
    }

    $dashboard = $dashboardId ? new DashboardInstance($dashboardId) : new DashboardInstance();

    // Check if this is a system dashboard (if editing)
    if ($dashboardId && $dashboard->getIsSystem()) {
        Utility::ajaxResponseFalse('System dashboards cannot be edited');
    }

    // Check if user owns this dashboard (if editing)
    if ($dashboardId && $dashboard->getCreatedUid() != $userId) {
        Utility::ajaxResponseFalse('Unauthorized');
    }

    $dashboard->setName($name);
    $dashboard->setDescription(isset($data['description']) ? $data['description'] : '');
    $dashboard->setStructure($structure);
    $dashboard->setConfig($config);

    if ($dashboardId) {
        $dashboard->setUpdatedUid($userId);
        $success = $dashboard->update();
    } else {
        $dashboard->setCreatedUid($userId);
        $dashboard->setCompanyId($companyId);
        $success = $dashboard->insert();
    }

    if (!$success) {
        Utility::ajaxResponseFalse('Failed to save dashboard');
    }

    Utility::ajaxResponseTrue('Dashboard saved successfully', array(
        'id' => $dashboard->getId(),
        'name' => $dashboard->getName()
    ));
}

/**
 * Get dashboard by ID
 */
function getDashboard($data)
{
    $dashboardId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Invalid dashboard ID');
    }

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        Utility::ajaxResponseFalse('Dashboard not found');
    }

    Utility::ajaxResponseTrue('Dashboard loaded', $dashboard->toArray());
}

/**
 * Delete dashboard
 */
function deleteDashboard($data)
{
    $dashboardId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Invalid dashboard ID');
    }

    // Check if this is a system dashboard
    $dashboard = new DashboardInstance($dashboardId);
    if ($dashboard->getIsSystem()) {
        Utility::ajaxResponseFalse('System dashboards cannot be deleted');
    }

    // Verify user owns this dashboard
    if ($dashboard->getCreatedUid() != Session::loggedInUid()) {
        Utility::ajaxResponseFalse('Unauthorized');
    }

    if (!DashboardInstance::delete($dashboardId)) {
        Utility::ajaxResponseFalse('Failed to delete dashboard');
    }

    Utility::ajaxResponseTrue('Dashboard deleted successfully');
}

/**
 * Update dashboard name
 */
function updateDashboardName($data)
{
    $dashboardId = isset($data['id']) ? intval($data['id']) : 0;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $userId = Session::loggedInUid();

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Invalid dashboard ID');
    }

    if (empty($name)) {
        Utility::ajaxResponseFalse('Dashboard name cannot be empty');
    }

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        Utility::ajaxResponseFalse('Dashboard not found');
    }

    // Check if user owns this dashboard
    if ($dashboard->getCreatedUid() != $userId) {
        Utility::ajaxResponseFalse('Unauthorized');
    }

    $dashboard->setName($name);
    $dashboard->setUpdatedUid($userId);

    if (!$dashboard->update()) {
        Utility::ajaxResponseFalse('Failed to update dashboard name');
    }

    Utility::ajaxResponseTrue('Dashboard name updated', array(
        'id' => $dashboard->getId(),
        'name' => $dashboard->getName()
    ));
}

/**
 * Update dashboard details (name and description)
 */
function updateDashboardDetails($data)
{
    $dashboardId = isset($data['id']) ? intval($data['id']) : 0;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $userId = Session::loggedInUid();

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Invalid dashboard ID');
    }

    if (empty($name)) {
        Utility::ajaxResponseFalse('Dashboard name cannot be empty');
    }

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        Utility::ajaxResponseFalse('Dashboard not found');
    }

    // Check if user owns this dashboard
    if ($dashboard->getCreatedUid() != $userId) {
        Utility::ajaxResponseFalse('Unauthorized');
    }

    $dashboard->setName($name);
    $dashboard->setDescription($description);
    $dashboard->setUpdatedUid($userId);

    if (!$dashboard->update()) {
        Utility::ajaxResponseFalse('Failed to update dashboard details');
    }

    Utility::ajaxResponseTrue('Dashboard details updated', array(
        'id' => $dashboard->getId(),
        'name' => $dashboard->getName(),
        'description' => $dashboard->getDescription()
    ));
}

/**
 * Update specific area content
 */
function updateAreaContent($data)
{
    $dashboardId = isset($data['dashboard_id']) ? intval($data['dashboard_id']) : 0;
    $sectionId = isset($data['section_id']) ? $data['section_id'] : '';
    $areaId = isset($data['area_id']) ? $data['area_id'] : '';
    $content = isset($data['content']) ? $data['content'] : array();

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Invalid dashboard ID');
    }

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        Utility::ajaxResponseFalse('Dashboard not found');
    }

    // TODO: Verify user owns this dashboard

    if (!$dashboard->updateAreaContent($sectionId, $areaId, $content)) {
        Utility::ajaxResponseFalse('Failed to update content');
    }

    Utility::ajaxResponseTrue('Content updated successfully');
}

/**
 * Add new section
 */
function addSection($data)
{
    $dashboardId = isset($data['dashboard_id']) ? intval($data['dashboard_id']) : 0;
    $position = isset($data['position']) ? $data['position'] : 'bottom'; // top or bottom
    $columns = isset($data['columns']) ? intval($data['columns']) : 1;

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Invalid dashboard ID');
    }

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        Utility::ajaxResponseFalse('Dashboard not found');
    }

    // TODO: Verify user owns this dashboard

    // Create empty section
    $sectionData = DashboardBuilder::createEmptySection($columns);

    if (!$dashboard->addSection($sectionData, $position)) {
        Utility::ajaxResponseFalse('Failed to add section');
    }

    Utility::ajaxResponseTrue('Section added successfully', array(
        'section' => $sectionData
    ));
}

/**
 * Add section from template
 */
function addSectionFromTemplate($data)
{
    $dashboardId = isset($data['dashboard_id']) ? intval($data['dashboard_id']) : 0;
    $templateId = isset($data['template_id']) ? intval($data['template_id']) : 0;
    $position = isset($data['position']) ? $data['position'] : 'bottom';

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Invalid dashboard ID');
    }

    if (!$templateId) {
        Utility::ajaxResponseFalse('Invalid template ID');
    }

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        Utility::ajaxResponseFalse('Dashboard not found');
    }

    // Get template
    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    // Get all sections from template structure
    $templateStructure = $template->getStructureArray();
    if (!isset($templateStructure['sections']) || empty($templateStructure['sections'])) {
        Utility::ajaxResponseFalse('Template has no sections');
    }

    // Add all sections from the template
    $addedSections = array();
    foreach ($templateStructure['sections'] as $sectionData) {
        // Generate new section ID to avoid conflicts
        $sectionData['sid'] = DashboardBuilder::generateSectionId();

        // Add section at the specified position
        if (!$dashboard->addSection($sectionData, $position)) {
            Utility::ajaxResponseFalse('Failed to add section');
        }

        $addedSections[] = $sectionData;

        // After adding the first section, subsequent sections should be added after it
        if ($position !== 'bottom') {
            $position = 'bottom';
        }
    }

    Utility::ajaxResponseTrue('Section(s) added successfully', array(
        'sections' => $addedSections
    ));
}

/**
 * Remove section
 */
function removeSection($data)
{
    $dashboardId = isset($data['dashboard_id']) ? intval($data['dashboard_id']) : 0;
    $sectionId = isset($data['section_id']) ? $data['section_id'] : '';

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Invalid dashboard ID');
    }

    if (empty($sectionId)) {
        Utility::ajaxResponseFalse('Section ID is required');
    }

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        Utility::ajaxResponseFalse('Dashboard not found');
    }

    // TODO: Verify user owns this dashboard

    if (!$dashboard->removeSection($sectionId)) {
        Utility::ajaxResponseFalse('Failed to remove section');
    }

    Utility::ajaxResponseTrue('Section removed successfully');
}

/**
 * Reorder sections (drag-drop)
 */
function reorderSections($data)
{
    $dashboardId = isset($data['dashboard_id']) ? intval($data['dashboard_id']) : 0;
    $order = isset($data['order']) ? $data['order'] : array(); // Array of section IDs in new order

    // Decode if it's a JSON string
    if (is_string($order)) {
        $order = json_decode($order, true);
    }

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Invalid dashboard ID');
    }

    if (empty($order) || !is_array($order)) {
        Utility::ajaxResponseFalse('Order array is required');
    }

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        Utility::ajaxResponseFalse('Dashboard not found');
    }

    // TODO: Verify user owns this dashboard

    if (!$dashboard->reorderSections($order)) {
        Utility::ajaxResponseFalse('Failed to reorder sections');
    }

    Utility::ajaxResponseTrue('Sections reordered successfully');
}

// ============================================================
// TEMPLATE MANAGEMENT FUNCTIONS
// ============================================================

/**
 * Show template list
 */
function showTemplateList()
{
    global $allowTemplateOrdering;
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'sortablejs-dgc/Sortable.min.js', 5);

    // Add page-specific JS
    LocalUtility::addPageScript('dashboard', 'template-list');

    $theme->setPageTitle('Dashboard Templates - Dynamic Graph Creator');

    $tpl = new Template(SystemConfig::templatesPath() . 'dashboard/views/template-list');
    $tpl->templates = DashboardTemplate::getAllCategoriesWithTemplates();
    $tpl->allowTemplateOrdering = $allowTemplateOrdering;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show template creator form
 */
function showTemplateCreator()
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize-dgc/autosize.min.js', 5);

    // Add page-specific JS
    LocalUtility::addPageScript('dashboard', 'template-editor');

    $theme->setPageTitle('Create Template - Dynamic Graph Creator');

    $tpl = new Template(SystemConfig::templatesPath() . 'dashboard/forms/template-editor');
    $tpl->pageTitle = 'Create Template';
    $tpl->template = null;
    $tpl->categories = DashboardTemplateCategory::getAll();
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show template editor form
 */
function showTemplateEditor($templateId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    if (!$templateId) {
        LocalUtility::redirect('dashboard/templates');
        return;
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        LocalUtility::redirect('dashboard/templates');
        return;
    }

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize-dgc/autosize.min.js', 5);

    // Add page-specific JS
    LocalUtility::addPageScript('dashboard', 'template-editor');

    $theme->setPageTitle('Edit Template - Dynamic Graph Creator');

    $tpl = new Template(SystemConfig::templatesPath() . 'dashboard/forms/template-editor');
    $tpl->pageTitle = 'Edit Template';
    $tpl->template = $template;
    $tpl->categories = DashboardTemplateCategory::getAll();
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show template builder (structure editor)
 */
function showTemplateBuilder($templateId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    if (!$templateId) {
        LocalUtility::redirect('dashboard/templates');
        return;
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId() || !$template->getName()) {
        LocalUtility::redirect('dashboard/templates');
        return;
    }

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'sortablejs-dgc/Sortable.min.js', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts-dgc/echarts.min.js', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize-dgc/autosize.min.js', 5);

    // Add page-specific JS
    LocalUtility::addPageScript('dashboard', 'template-builder');

    $theme->setPageTitle('Template Builder - ' . htmlspecialchars($template->getName()));

    $tpl = new Template(SystemConfig::templatesPath() . 'dashboard/forms/template-builder');
    $tpl->template = $template;
    $tpl->categories = DashboardTemplateCategory::getAll();
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Show template preview
 */
function showTemplatePreview($templateId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    if (!$templateId) {
        LocalUtility::redirect('dashboard/templates');
        return;
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId() || !$template->getName()) {
        LocalUtility::redirect('dashboard/templates');
        return;
    }

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts-dgc/echarts.min.js', 5);

    // Add page-specific JS
    LocalUtility::addPageScript('dashboard', 'template-preview');

    $theme->setPageTitle('Template Preview - ' . htmlspecialchars($template->getName()));

    $tpl = new Template(SystemConfig::templatesPath() . 'dashboard/views/template-preview');
    $tpl->template = $template;
    $theme->setContent('full_main', $tpl->parse());
}

/**
 * Create new template
 */
function createTemplate($data)
{
    $name = isset($data['name']) ? trim($data['name']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $dtcidValue = isset($data['dtcid']) ? $data['dtcid'] : '';
    $newCategoryName = isset($data['new_category_name']) ? trim($data['new_category_name']) : '';
    $newCategoryDescription = isset($data['new_category_description']) ? trim($data['new_category_description']) : '';
    $userId = Session::loggedInUid();

    if (empty($name)) {
        Utility::ajaxResponseFalse('Template name is required');
    }

    // Handle category assignment
    $dtcid = null;

    if ($dtcidValue === '__new__') {
        // Create new category
        if (empty($newCategoryName)) {
            Utility::ajaxResponseFalse('Category name is required');
        }

        // Generate slug from name
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $newCategoryName));
        $slug = trim($slug, '-');

        // Check for duplicate slug
        if (DashboardTemplateCategory::slugExists($slug)) {
            Utility::ajaxResponseFalse('A category with this name already exists');
        }

        // Create the category
        $category = new DashboardTemplateCategory();
        $category->setSlug($slug);
        $category->setName($newCategoryName);
        $category->setDescription($newCategoryDescription);
        $category->setDisplayOrder(100); // Put at end
        $category->setIsSystem(0); // User category

        if (!$category->insert()) {
            Utility::ajaxResponseFalse('Failed to create category');
        }

        $dtcid = $category->getId();
    } elseif (!empty($dtcidValue) && is_numeric($dtcidValue)) {
        $dtcid = intval($dtcidValue);
    }

    // Create empty structure with single section
    $structure = json_encode([
        'sections' => [
            [
                'sid' => 's1',
                'gridTemplate' => '1fr',
                'areas' => [
                    [
                        'aid' => 'a1',
                        'colSpanFr' => '1fr',
                        'content' => ['type' => 'empty'],
                        'emptyState' => [
                            'icon' => 'fa-plus-circle',
                            'message' => 'Add content'
                        ]
                    ]
                ]
            ]
        ]
    ]);

    $template = new DashboardTemplate();
    $template->setName($name);
    $template->setDescription($description);
    $template->setDtcid($dtcid);
    $template->setStructure($structure);
    $template->setIsSystem(0); // User template
    $template->setCreatedUid($userId);

    if (!$template->insert()) {
        Utility::ajaxResponseFalse('Failed to create template');
    }

    Utility::ajaxResponseTrue('Template created successfully', array(
        'dtid' => $template->getId(),
        'redirect' => '?urlq=dashboard/template/builder/' . $template->getId()
    ));
}

/**
 * Update template metadata
 */
function updateTemplate($data)
{
    $templateId = isset($data['id']) ? intval($data['id']) : 0;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $dtcidValue = isset($data['dtcid']) ? $data['dtcid'] : '';
    $newCategoryName = isset($data['new_category_name']) ? trim($data['new_category_name']) : '';
    $newCategoryDescription = isset($data['new_category_description']) ? trim($data['new_category_description']) : '';
    $userId = Session::loggedInUid();

    if (!$templateId) {
        Utility::ajaxResponseFalse('Invalid template ID');
    }

    if (empty($name)) {
        Utility::ajaxResponseFalse('Template name is required');
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    // Protect system templates
    if ($template->getIsSystem()) {
        Utility::ajaxResponseFalse('Cannot modify system templates');
    }

    // Handle category assignment
    $dtcid = null;

    if ($dtcidValue === '__new__') {
        // Create new category
        if (empty($newCategoryName)) {
            Utility::ajaxResponseFalse('Category name is required');
        }

        // Generate slug from name
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $newCategoryName));
        $slug = trim($slug, '-');

        // Check for duplicate slug
        if (DashboardTemplateCategory::slugExists($slug)) {
            Utility::ajaxResponseFalse('A category with this name already exists');
        }

        // Create the category
        $category = new DashboardTemplateCategory();
        $category->setSlug($slug);
        $category->setName($newCategoryName);
        $category->setDescription($newCategoryDescription);
        $category->setDisplayOrder(100); // Put at end
        $category->setIsSystem(0); // User category

        if (!$category->insert()) {
            Utility::ajaxResponseFalse('Failed to create category');
        }

        $dtcid = $category->getId();
    } elseif (!empty($dtcidValue) && is_numeric($dtcidValue)) {
        $dtcid = intval($dtcidValue);
    }

    $template->setName($name);
    $template->setDescription($description);
    $template->setDtcid($dtcid);
    $template->setUpdatedUid($userId);

    if (!$template->update()) {
        Utility::ajaxResponseFalse('Failed to update template');
    }

    $responseData = array(
        'dtid' => $template->getId(),
        'name' => $template->getName()
    );

    // Include new category ID if one was created
    if ($dtcidValue === '__new__' && $dtcid) {
        $responseData['new_category_id'] = $dtcid;
    }

    Utility::ajaxResponseTrue('Template updated successfully', $responseData);
}

/**
 * Delete template (soft delete)
 */
function deleteTemplate($data)
{
    $templateId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$templateId) {
        Utility::ajaxResponseFalse('Invalid template ID');
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    // Check if it's a system template
    if ($template->getIsSystem()) {
        Utility::ajaxResponseFalse('System templates cannot be deleted');
    }

    // Check if template is in use by any dashboards
    $db = Rapidkart::getInstance()->getDB();
    $sql = "SELECT COUNT(*) as count FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . "
            WHERE dtid = '::dtid' AND disid != 3";
    $result = $db->query($sql, array('::dtid' => $templateId));

    if ($result && $db->resultNumRows($result) > 0) {
        $row = $db->fetchAssocArray($result);
        if ($row && $row['count'] > 0) {
            Utility::ajaxResponseFalse(
                'Cannot delete template. It is being used by ' . $row['count'] . ' dashboard(s)'
            );
        }
    }

    if (!DashboardTemplate::delete($templateId)) {
        Utility::ajaxResponseFalse('Failed to delete template');
    }

    Utility::ajaxResponseTrue('Template deleted successfully');
}

/**
 * Duplicate template
 */
function duplicateTemplate($data)
{
    $templateId = isset($data['id']) ? intval($data['id']) : 0;
    $userId = Session::loggedInUid();

    if (!$templateId) {
        Utility::ajaxResponseFalse('Invalid template ID');
    }

    $sourceTemplate = new DashboardTemplate($templateId);
    if (!$sourceTemplate->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    // Create duplicate
    $newTemplate = new DashboardTemplate();
    $newTemplate->setName($sourceTemplate->getName() . ' (Copy)');
    $newTemplate->setDescription($sourceTemplate->getDescription());
    $newTemplate->setDtcid($sourceTemplate->getDtcid());
    $newTemplate->setThumbnail($sourceTemplate->getThumbnail());
    $newTemplate->setStructure($sourceTemplate->getStructure());
    $newTemplate->setIsSystem(0); // Always user template
    $newTemplate->setCreatedUid($userId);

    if (!$newTemplate->insert()) {
        Utility::ajaxResponseFalse('Failed to duplicate template');
    }

    Utility::ajaxResponseTrue('Template duplicated successfully', array(
        'dtid' => $newTemplate->getId(),
        'redirect' => '?urlq=dashboard/template/builder/' . $newTemplate->getId()
    ));
}

/**
 * Save template structure
 */
function saveTemplateStructure($data)
{
    $templateId = isset($data['id']) ? intval($data['id']) : 0;
    $structure = isset($data['structure']) ? $data['structure'] : '';
    $userId = Session::loggedInUid();

    if (!$templateId) {
        Utility::ajaxResponseFalse('Invalid template ID');
    }

    if (empty($structure)) {
        Utility::ajaxResponseFalse('Template structure is required');
    }

    // Validate structure
    $structureArray = json_decode($structure, true);
    if (!$structureArray) {
        Utility::ajaxResponseFalse('Invalid JSON structure');
    }

    if (!DashboardBuilder::validateStructure($structureArray)) {
        Utility::ajaxResponseFalse('Invalid template structure');
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    // Protect system templates
    if ($template->getIsSystem()) {
        Utility::ajaxResponseFalse('Cannot modify system templates');
    }

    $template->setStructure($structure);
    $template->setUpdatedUid($userId);

    if (!$template->update()) {
        Utility::ajaxResponseFalse('Failed to save template structure');
    }

    Utility::ajaxResponseTrue('Template saved successfully', array(
        'dtid' => $template->getId()
    ));
}

/**
 * Remove section from template
 */
function removeTemplateSection($data)
{
    $templateId = isset($data['template_id']) ? intval($data['template_id']) : 0;
    $sectionId = isset($data['section_id']) ? $data['section_id'] : '';
    $userId = Session::loggedInUid();

    if (!$templateId) {
        Utility::ajaxResponseFalse('Invalid template ID');
    }

    if (empty($sectionId)) {
        Utility::ajaxResponseFalse('Section ID is required');
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    // Protect system templates
    if ($template->getIsSystem()) {
        Utility::ajaxResponseFalse('Cannot modify system templates');
    }

    // Get current structure and remove the section
    $structure = $template->getStructureArray();
    if (!isset($structure['sections'])) {
        Utility::ajaxResponseFalse('Invalid template structure');
    }

    // Filter out the section to remove
    $structure['sections'] = array_values(array_filter($structure['sections'], function ($section) use ($sectionId) {
        return $section['sid'] !== $sectionId;
    }));

    // Save updated structure
    $template->setStructure(json_encode($structure));
    $template->setUpdatedUid($userId);

    if (!$template->update()) {
        Utility::ajaxResponseFalse('Failed to remove section');
    }

    Utility::ajaxResponseTrue('Section removed successfully');
}

/**
 * Get single template data
 */
function getTemplate($data)
{
    $templateId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$templateId) {
        Utility::ajaxResponseFalse('Invalid template ID');
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    Utility::ajaxResponseTrue('Template loaded successfully', array(
        'dtid' => $template->getId(),
        'name' => $template->getName(),
        'description' => $template->getDescription(),
        'dtcid' => $template->getDtcid(),
        'structure' => $template->getStructure(),
        'is_system' => $template->getIsSystem()
    ));
}

/**
 * Delete template category (soft delete)
 * Only allows deletion if category is empty (no templates)
 */
function deleteCategory($data)
{
    $categoryId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$categoryId) {
        Utility::ajaxResponseFalse('Invalid category ID');
    }

    $category = new DashboardTemplateCategory($categoryId);
    if (!$category->getId()) {
        Utility::ajaxResponseFalse('Category not found');
    }

    // Check if it's a system category
    if ($category->getIsSystem()) {
        Utility::ajaxResponseFalse('System categories cannot be deleted');
    }

    // Check if category has any templates
    $db = Rapidkart::getInstance()->getDB();
    $sql = "SELECT COUNT(*) as count FROM " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE . "
            WHERE dtcid = '::dtcid' AND dtsid != 3";
    $result = $db->query($sql, array('::dtcid' => $categoryId));

    if ($result && $db->resultNumRows($result) > 0) {
        $row = $db->fetchAssocArray($result);
        if ($row && $row['count'] > 0) {
            Utility::ajaxResponseFalse(
                'Cannot delete category. It contains ' . $row['count'] . ' template(s). Please move or delete templates first.'
            );
        }
    }

    // Soft delete the category
    if (!DashboardTemplateCategory::delete($categoryId)) {
        Utility::ajaxResponseFalse('Failed to delete category');
    }

    Utility::ajaxResponseTrue('Category deleted successfully');
}

/**
 * Reorder templates within a category
 */
function reorderTemplates($data)
{
    global $allowTemplateOrdering;

    if (!$allowTemplateOrdering) {
        Utility::ajaxResponseFalse('Template reordering is not allowed');
    }

    $order = isset($data['order']) ? $data['order'] : array();

    // Decode if it's a JSON string
    if (is_string($order)) {
        $order = json_decode($order, true);
    }

    if (empty($order) || !is_array($order)) {
        Utility::ajaxResponseFalse('Order array is required');
    }

    $db = Rapidkart::getInstance()->getDB();

    // Update display_order for each template
    foreach ($order as $index => $templateId) {
        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE . "
                SET display_order = '::order'
                WHERE dtid = '::dtid'";
        $db->query($sql, array(
            '::order' => $index,
            '::dtid' => intval($templateId)
        ));
    }

    Utility::ajaxResponseTrue('Templates reordered successfully');
}

/**
 * Reorder template categories
 */
function reorderCategories($data)
{
    global $allowTemplateOrdering;

    if (!$allowTemplateOrdering) {
        Utility::ajaxResponseFalse('Category reordering is not allowed');
    }

    $order = isset($data['order']) ? $data['order'] : array();

    // Decode if it's a JSON string
    if (is_string($order)) {
        $order = json_decode($order, true);
    }

    if (empty($order) || !is_array($order)) {
        Utility::ajaxResponseFalse('Order array is required');
    }

    $db = Rapidkart::getInstance()->getDB();

    // Update display_order for each category
    foreach ($order as $index => $categoryId) {
        $sql = "UPDATE " . SystemTables::DB_TBL_DASHBOARD_TEMPLATE_CATEGORY . "
                SET display_order = '::order'
                WHERE dtcid = '::dtcid'";
        $db->query($sql, array(
            '::order' => $index,
            '::dtcid' => intval($categoryId)
        ));
    }

    Utility::ajaxResponseTrue('Categories reordered successfully');
}

/**
 * Move template to a different category
 */
function moveTemplateCategory($data)
{
    global $allowTemplateOrdering;

    if (!$allowTemplateOrdering) {
        Utility::ajaxResponseFalse('Moving templates between categories is not allowed');
    }

    $templateId = isset($data['template_id']) ? intval($data['template_id']) : 0;
    $newCategoryId = isset($data['category_id']) ? $data['category_id'] : null;

    if (!$templateId) {
        Utility::ajaxResponseFalse('Template ID is required');
    }

    // Load the template
    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    // Check if template is system (cannot be moved)
    if ($template->getIsSystem()) {
        Utility::ajaxResponseFalse('System templates cannot be moved');
    }

    // Handle category ID - null/empty means uncategorized
    $categoryId = null;
    if (!empty($newCategoryId) && is_numeric($newCategoryId)) {
        $categoryId = intval($newCategoryId);

        // Verify category exists
        $category = new DashboardTemplateCategory($categoryId);
        if (!$category->getId()) {
            Utility::ajaxResponseFalse('Target category not found');
        }
    }

    // Update the template's category
    $template->setDtcid($categoryId);
    $template->update();

    // Get category name for response
    $categoryName = 'Uncategorized';
    if ($categoryId) {
        $category = new DashboardTemplateCategory($categoryId);
        $categoryName = $category->getName();
    }

    Utility::ajaxResponseTrue('Template moved to ' . $categoryName, array(
        'template_id' => $templateId,
        'category_id' => $categoryId,
        'category_name' => $categoryName
    ));
}

/**
 * Get filters by keys for dashboard filter bar
 * Returns filter data with options for FilterRenderer
 */
function getFiltersByKeys($data)
{
    $keys = isset($data['keys']) ? $data['keys'] : array();

    // Handle case where keys is a JSON string
    if (is_string($keys)) {
        $decoded = json_decode($keys, true);
        $keys = is_array($decoded) ? $decoded : array();
    }

    if (empty($keys) || !is_array($keys)) {
        Utility::ajaxResponseTrue('No filters', array());
    }

    // Get filters from DataFilterManager
    $filters = DataFilterManager::getByKeys($keys);

    if (empty($filters)) {
        Utility::ajaxResponseTrue('No filters found', array());
    }

    // Build filter data with options (ordered by input array)
    // Note: toArray() already includes options via getOptions()
    $result = array();
    foreach ($keys as $key) {
        if (isset($filters[$key])) {
            $filter = $filters[$key];
            $result[] = $filter->toArray();
        }
    }

    Utility::ajaxResponseTrue('Filters loaded', $result);
}

/**
 * Get all filters for a dashboard based on its widgets
 * Extracts filter keys from all graph queries in the dashboard
 */
function getDashboardFilters($data)
{
    $dashboardId = isset($data['dashboard_id']) ? intval($data['dashboard_id']) : 0;

    if (!$dashboardId || !DashboardInstance::isExistent($dashboardId)) {
        Utility::ajaxResponseTrue('No filters', array(
            'keys' => array(),
            'filters' => array()
        ));
    }

    $dashboard = new DashboardInstance($dashboardId);
    $filterKeys = $dashboard->getAllFilterKeys();

    if (empty($filterKeys)) {
        Utility::ajaxResponseTrue('No filters', array(
            'keys' => array(),
            'filters' => array()
        ));
    }

    // Get full filter data for these keys
    $filters = DataFilterManager::getByKeys($filterKeys);

    // Build filter data with options (ordered by extracted keys)
    $result = array();
    foreach ($filterKeys as $key) {
        if (isset($filters[$key])) {
            $filter = $filters[$key];
            $result[] = $filter->toArray();
        }
    }

    Utility::ajaxResponseTrue('Filters loaded', array(
        'keys' => $filterKeys,
        'filters' => $result
    ));
}

/**
 * Get all widgets (graphs) with their categories for the widget selector modal
 * Returns graphs and categories data for filtering
 */
function getWidgetsForSelector($data)
{
    // Get all widget types from database
    $widgetTypes = WidgetTypeManager::getAllAsArray();

    // Get all active graphs
    $graphs = GraphManager::getAll();

    // Get all active counters
    $counters = WidgetCounterManager::getAll();

    // Get all active tables
    $tables = WidgetTableManager::getAll();

    // Get all widget categories
    $categories = WidgetCategoryManager::getAll();

    // Build graphs data with category mappings
    $graphsData = array();
    foreach ($graphs as $graph) {
        $categoryIds = GraphWidgetCategoryMappingManager::getCategoryIdsForGraph($graph->getId());
        $graphCategories = GraphWidgetCategoryMappingManager::getCategoriesForGraph($graph->getId());

        $graphsData[] = array(
            'gid' => $graph->getId(),
            'name' => $graph->getName(),
            'description' => $graph->getDescription(),
            'graph_type' => $graph->getGraphType(),
            'category_ids' => $categoryIds,
            'categories' => array_map(function ($cat) {
                return $cat->toArray();
            }, $graphCategories)
        );
    }

    // Build counters data with category mappings
    $countersData = array();
    foreach ($counters as $counter) {
        $categoryIds = WidgetCounterCategoryMappingManager::getCategoryIdsForCounter($counter->getId());
        $counterCategories = WidgetCounterCategoryMappingManager::getCategoriesForCounter($counter->getId());
        $config = $counter->getConfigArray();

        $countersData[] = array(
            'cid' => $counter->getId(),
            'name' => $counter->getName(),
            'description' => $counter->getDescription(),
            'icon' => isset($config['icon']) ? $config['icon'] : 'analytics',
            'color' => isset($config['color']) ? $config['color'] : '#4361ee',
            'category_ids' => $categoryIds,
            'categories' => array_map(function ($cat) {
                return $cat->toArray();
            }, $counterCategories)
        );
    }

    // Build tables data with category mappings
    $tablesData = array();
    foreach ($tables as $table) {
        $categoryIds = WidgetTableCategoryMappingManager::getCategoryIdsForTable($table->getId());
        $tableCategories = WidgetTableCategoryMappingManager::getCategoriesForTable($table->getId());

        $tablesData[] = array(
            'tid' => $table->getId(),
            'name' => $table->getName(),
            'description' => $table->getDescription(),
            'category_ids' => $categoryIds,
            'categories' => array_map(function ($cat) {
                return $cat->toArray();
            }, $tableCategories)
        );
    }

    // Build categories data with widget counts
    $categoriesData = array();
    foreach ($categories as $cat) {
        $catArray = $cat->toArray();
        $catArray['graph_count'] = count(
            GraphWidgetCategoryMappingManager::getGraphsForCategory($cat->getId())
        );
        $catArray['counter_count'] = count(
            WidgetCounterCategoryMappingManager::getCountersForCategory($cat->getId())
        );
        $catArray['table_count'] = count(
            WidgetTableCategoryMappingManager::getTablesForCategory($cat->getId())
        );
        $catArray['widget_count'] = $catArray['graph_count'] + $catArray['counter_count'] + $catArray['table_count'];
        $categoriesData[] = $catArray;
    }

    Utility::ajaxResponseTrue('Widgets loaded', array(
        'widget_types' => $widgetTypes,
        'graphs' => $graphsData,
        'counters' => $countersData,
        'tables' => $tablesData,
        'categories' => $categoriesData,
        'total_count' => count($graphsData) + count($countersData) + count($tablesData)
    ));
}

/**
 * Preview a graph for dashboard widget rendering
 * This is a simplified version for loading existing graphs by ID
 * @param array $data
 */
function previewGraphForDashboard($data)
{
    $graphId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$graphId) {
        Utility::ajaxResponseFalse('Graph ID required');
    }

    // Load graph
    $graph = new Graph($graphId);
    if (!$graph->getId()) {
        Utility::ajaxResponseFalse('Graph not found');
    }

    // Get filter values (for dashboard filters)
    $filters = isset($data['filters']) ? $data['filters'] : array();
    if (is_string($filters)) {
        $filters = json_decode($filters, true);
    }

    // Execute graph query and get chart data
    $chartData = $graph->execute($filters ? $filters : array());
    $config = json_decode($graph->getConfig(), true);

    Utility::ajaxResponseTrue('Graph data loaded', array(
        'chartData' => $chartData,
        'config' => $config,
        'graphType' => $graph->getGraphType(),
        'name' => $graph->getName()
    ));
}

/**
 * Preview counter for dashboard (load counter data with filters)
 */
function previewCounterForDashboard($data)
{
    $counterId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$counterId) {
        Utility::ajaxResponseFalse('Counter ID required');
    }

    // Load counter
    $counter = new WidgetCounter($counterId);
    if (!$counter->getId()) {
        Utility::ajaxResponseFalse('Counter not found');
    }

    // Get filter values (for dashboard filters)
    $filters = isset($data['filters']) ? $data['filters'] : array();
    if (is_string($filters)) {
        $filters = json_decode($filters, true);
    }

    // Execute counter query
    $counterData = $counter->execute($filters ? $filters : array());
    $config = $counter->getConfigArray();
    $defaultConfig = WidgetCounter::getDefaultConfig();

    // Get icon and color from config with defaults
    $icon = isset($config['icon']) && $config['icon'] ? $config['icon'] : $defaultConfig['icon'];
    $color = isset($config['color']) && $config['color'] ? $config['color'] : $defaultConfig['color'];

    Utility::ajaxResponseTrue('Counter data loaded', array(
        'counterData' => $counterData,
        'config' => $config,
        'name' => $counter->getName(),
        'icon' => $icon,
        'color' => $color
    ));
}

/**
 * Save dashboard filter values to session
 */
function saveDashboardFilterValues($data)
{
    $dashboardId = isset($data['dashboard_id']) ? intval($data['dashboard_id']) : 0;
    $filters = isset($data['filters']) ? $data['filters'] : array();

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Dashboard ID is required');
    }

    // Decode filters if they're JSON string
    if (is_string($filters)) {
        $filters = json_decode($filters, true);
    }

    // Store in session with dashboard ID as key
    $sessionKey = 'dashboard_filters_' . $dashboardId;
    $_SESSION[$sessionKey] = $filters;

    Utility::ajaxResponseTrue('Filters saved', array('filters' => $filters));
}

/**
 * Get dashboard filter values from session
 */
function getDashboardFilterValues($data)
{
    $dashboardId = isset($data['dashboard_id']) ? intval($data['dashboard_id']) : 0;

    if (!$dashboardId) {
        Utility::ajaxResponseFalse('Dashboard ID is required');
    }

    // Get from session with dashboard ID as key
    $sessionKey = 'dashboard_filters_' . $dashboardId;
    $filters = isset($_SESSION[$sessionKey]) ? $_SESSION[$sessionKey] : array();

    Utility::ajaxResponseTrue('Filters loaded', array('filters' => $filters));
}

/**
 * Preview table for dashboard (load table data with filters)
 */
function previewTableForDashboard($data)
{
    $tableId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$tableId) {
        Utility::ajaxResponseFalse('Table ID required');
    }

    // Load table
    $table = new WidgetTable($tableId);
    if (!$table->getId()) {
        Utility::ajaxResponseFalse('Table not found');
    }

    // Get filter values (for dashboard filters)
    $filters = isset($data['filters']) ? $data['filters'] : array();
    if (is_string($filters)) {
        $filters = json_decode($filters, true);
    }

    // Execute table query
    $tableData = $table->execute($filters ? $filters : array());
    $config = $table->getConfigArray();

    Utility::ajaxResponseTrue('Table data loaded', array(
        'tableData' => $tableData,
        'config' => $config,
        'name' => $table->getName()
    ));
}
