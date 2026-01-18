<?php

/**
 * Dashboard Controller
 * Handles dashboard builder actions
 */

// Permission control for template ordering operations
// Set to true to allow category reordering, template reordering, and moving templates between categories
$allowTemplateOrdering = true;

$url = Utility::parseUrl();
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
        // Template management actions
        case 'create_template':
            createTemplate($_POST);
            break;
        case 'update_template':
            updateTemplate($_POST);
            break;
        case 'delete_template':
            deleteTemplate($_POST);
            break;
        case 'duplicate_template':
            duplicateTemplate($_POST);
            break;
        case 'save_template_structure':
            saveTemplateStructure($_POST);
            break;
        case 'get_template':
            getTemplate($_POST);
            break;
        case 'remove_template_section':
            removeTemplateSection($_POST);
            break;
        case 'delete_category':
            deleteCategory($_POST);
            break;
        case 'reorder_templates':
            reorderTemplates($_POST);
            break;
        case 'reorder_categories':
            reorderCategories($_POST);
            break;
        case 'move_template_category':
            moveTemplateCategory($_POST);
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
        showTemplateList();
        break;
    case 'template':
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
                Utility::redirect('dashboard/templates');
                break;
        }
        break;
    case 'list':
    default:
        showList();
        break;
}

/**
 * Show dashboard list
 */
function showList()
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('dashboard');

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('dashboard');
    $theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/dashboard-list.js');

    $theme->setPageTitle('Dashboards - Dynamic Graph Creator');

    // Get user's dashboards - TODO: Replace with actual user ID from session
    $userId = 1;
    $dashboards = DashboardInstance::getUserDashboards($userId);

    ob_start();
    require_once SystemConfig::templatesPath() . 'dashboard/dashboard-list.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Show dashboard builder
 */
function showBuilder($dashboardId = 0)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('dashboard');

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('dashboard');
    $theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/dashboard-builder.js');

    $dashboard = null;
    $templates = DashboardTemplate::getAllGrouped();

    if ($dashboardId) {
        $dashboard = new DashboardInstance($dashboardId);
        if (!$dashboard->getId()) {
            Utility::redirect('dashboard');
            return;
        }
    }

    $theme->setPageTitle(($dashboard ? 'Edit Dashboard' : 'Create Dashboard') . ' - Dynamic Graph Creator');

    ob_start();
    require_once SystemConfig::templatesPath() . 'dashboard/dashboard-builder.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Show dashboard preview
 */
function showPreview($dashboardId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('dashboard');

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('dashboard');
    $theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/dashboard-preview.js');

    $dashboard = new DashboardInstance($dashboardId);
    if (!$dashboard->getId()) {
        Utility::redirect('dashboard');
        return;
    }

    $theme->setPageTitle(htmlspecialchars($dashboard->getName()) . ' - Preview');

    ob_start();
    require_once SystemConfig::templatesPath() . 'dashboard/dashboard-preview.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
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
    // TODO: Get actual user ID from session
    $userId = 1;

    if (!$templateId) {
        Utility::ajaxResponseFalse('Template ID required');
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::ajaxResponseFalse('Template not found');
    }

    $instance = $template->createInstance($userId, $name);
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
    // TODO: Get actual user ID from session
    $userId = 1;

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
    if ($dashboardId && $dashboard->getUserId() != $userId) {
        Utility::ajaxResponseFalse('Unauthorized');
    }

    $dashboard->setName($name);
    $dashboard->setDescription(isset($data['description']) ? $data['description'] : '');
    $dashboard->setStructure($structure);
    $dashboard->setConfig($config);
    $dashboard->setUserId($userId);

    if ($dashboardId) {
        $dashboard->setUpdatedUid($userId);
        $success = $dashboard->update();
    } else {
        $dashboard->setCreatedUid($userId);
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

    // TODO: Verify user owns this dashboard
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
    // TODO: Get actual user ID from session
    $userId = 1;

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
    if ($dashboard->getUserId() != $userId) {
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

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('dashboard');

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'sortablejs/Sortable.min.js', 5);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('dashboard');
    $theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/template-list.js');

    $theme->setPageTitle('Dashboard Templates - Dynamic Graph Creator');

    $templates = DashboardTemplate::getAllCategoriesWithTemplates();

    ob_start();
    require_once SystemConfig::templatesPath() . 'dashboard/template-list.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Show template creator form
 */
function showTemplateCreator()
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('dashboard');

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize/autosize.min.js', 5);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('dashboard');
    $theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/template-editor.js');

    $theme->setPageTitle('Create Template - Dynamic Graph Creator');

    $pageTitle = 'Create Template';
    $template = null;
    $categories = DashboardTemplateCategory::getAll();

    ob_start();
    require_once SystemConfig::templatesPath() . 'dashboard/template-editor.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Show template editor form
 */
function showTemplateEditor($templateId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    if (!$templateId) {
        Utility::redirect('dashboard/templates');
        return;
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::redirect('dashboard/templates');
        return;
    }

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('dashboard');

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize/autosize.min.js', 5);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('dashboard');
    $theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/template-editor.js');

    $theme->setPageTitle('Edit Template - Dynamic Graph Creator');

    $pageTitle = 'Edit Template';
    $categories = DashboardTemplateCategory::getAll();

    ob_start();
    require_once SystemConfig::templatesPath() . 'dashboard/template-editor.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Show template builder (structure editor)
 */
function showTemplateBuilder($templateId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    if (!$templateId) {
        Utility::redirect('dashboard/templates');
        return;
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId() || !$template->getName()) {
        Utility::redirect('dashboard/templates');
        return;
    }

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('dashboard');

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize/autosize.min.js', 5);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('dashboard');
    $theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/template-builder.js');

    $theme->setPageTitle('Template Builder - ' . htmlspecialchars($template->getName()));

    $categories = DashboardTemplateCategory::getAll();

    ob_start();
    require_once SystemConfig::templatesPath() . 'dashboard/template-builder.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
}

/**
 * Show template preview
 */
function showTemplatePreview($templateId)
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    if (!$templateId) {
        Utility::redirect('dashboard/templates');
        return;
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId() || !$template->getName()) {
        Utility::redirect('dashboard/templates');
        return;
    }

    // Add page-specific CSS
    Utility::addModuleCss('common');
    Utility::addModuleCss('dashboard');

    // Add libraries
    $theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);

    // Add page-specific JS
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('dashboard');
    $theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/template-preview.js');

    $theme->setPageTitle('Template Preview - ' . htmlspecialchars($template->getName()));

    ob_start();
    require_once SystemConfig::templatesPath() . 'dashboard/template-preview.php';
    $content = ob_get_clean();

    $theme->setContent('full_main', $content);
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
    // TODO: Get actual user ID from session
    $userId = 1;

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
    // TODO: Get actual user ID from session
    $userId = 1;

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

    if ($result && $db->numRows($result) > 0) {
        $row = $db->fetchAssoc($result);
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
    // TODO: Get actual user ID from session
    $userId = 1;

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
    // TODO: Get actual user ID from session
    $userId = 1;

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
    // TODO: Get actual user ID from session
    $userId = 1;

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

    if ($result && $db->numRows($result) > 0) {
        $row = $db->fetchAssoc($result);
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
