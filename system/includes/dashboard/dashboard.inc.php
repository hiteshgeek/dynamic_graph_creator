<?php

/**
 * Layout Controller
 * Handles layout builder actions
 */

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
            saveLayout($_POST);
            break;
        case 'get_dashboard':
            getLayout($_POST);
            break;
        case 'delete_dashboard':
            deleteLayout($_POST);
            break;
        case 'update_layout_name':
            updateLayoutName($_POST);
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
    }
}

// Handle GET actions
switch ($action) {
    case 'builder':
        $layoutId = isset($url[2]) ? intval($url[2]) : 0;
        showBuilder($layoutId);
        break;
    case 'preview':
        $layoutId = isset($url[2]) ? intval($url[2]) : 0;
        showPreview($layoutId);
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
                Utility::redirect('layout/templates');
                break;
        }
        break;
    case 'list':
    default:
        showList();
        break;
}

/**
 * Show layout list
 */
function showList()
{
    // Get user's layouts - TODO: Replace with actual user ID from session
    $userId = 1;
    $layouts = DashboardInstance::getUserLayouts($userId);
    require_once SystemConfig::templatesPath() . 'dashboard/dashboard-list.php';
}

/**
 * Show layout builder
 */
function showBuilder($layoutId = 0)
{
    $layout = null;
    $templates = DashboardTemplate::getAllGrouped();

    if ($layoutId) {
        $layout = new DashboardInstance($layoutId);
        if (!$layout->getId()) {
            Utility::redirect('layout');
            return;
        }
    }

    require_once SystemConfig::templatesPath() . 'dashboard/dashboard-builder.php';
}

/**
 * Show layout preview
 */
function showPreview($layoutId)
{
    $layout = new DashboardInstance($layoutId);
    if (!$layout->getId()) {
        Utility::redirect('layout');
        return;
    }

    require_once SystemConfig::templatesPath() . 'dashboard/dashboard-preview.php';
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
 * Create new layout from template
 */
function createFromTemplate($data)
{
    $templateId = isset($data['template_id']) ? intval($data['template_id']) : 0;
    $name = isset($data['name']) ? $data['name'] : 'New Layout';
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
        Utility::ajaxResponseFalse('Failed to create layout');
    }

    Utility::ajaxResponseTrue('Layout created', array(
        'id' => $instance->getId(),
        'name' => $instance->getName()
    ));
}

/**
 * Save layout (create or update)
 */
function saveLayout($data)
{
    $layoutId = isset($data['layout_id']) ? intval($data['layout_id']) : 0;
    $name = isset($data['name']) ? $data['name'] : '';
    $structure = isset($data['structure']) ? $data['structure'] : '';
    $config = isset($data['config']) ? $data['config'] : '{}';
    // TODO: Get actual user ID from session
    $userId = 1;

    if (empty($name)) {
        Utility::ajaxResponseFalse('Layout name is required');
    }

    if (empty($structure)) {
        Utility::ajaxResponseFalse('Layout structure is required');
    }

    // Validate structure
    $structureArray = json_decode($structure, true);
    if (!$structureArray) {
        Utility::ajaxResponseFalse('Invalid JSON structure');
    }

    if (!DashboardBuilder::validateStructure($structureArray)) {
        Utility::ajaxResponseFalse('Invalid layout structure');
    }

    $layout = $layoutId ? new DashboardInstance($layoutId) : new DashboardInstance();

    // Check if user owns this layout (if editing)
    if ($layoutId && $layout->getUserId() != $userId) {
        Utility::ajaxResponseFalse('Unauthorized');
    }

    $layout->setName($name);
    $layout->setDescription(isset($data['description']) ? $data['description'] : '');
    $layout->setStructure($structure);
    $layout->setConfig($config);
    $layout->setUserId($userId);

    if ($layoutId) {
        $layout->setUpdatedUid($userId);
        $success = $layout->update();
    } else {
        $layout->setCreatedUid($userId);
        $success = $layout->insert();
    }

    if (!$success) {
        Utility::ajaxResponseFalse('Failed to save layout');
    }

    Utility::ajaxResponseTrue('Dashboard saved successfully', array(
        'id' => $layout->getId(),
        'name' => $layout->getName()
    ));
}

/**
 * Get layout by ID
 */
function getLayout($data)
{
    $layoutId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$layoutId) {
        Utility::ajaxResponseFalse('Invalid layout ID');
    }

    $layout = new DashboardInstance($layoutId);
    if (!$layout->getId()) {
        Utility::ajaxResponseFalse('Layout not found');
    }

    Utility::ajaxResponseTrue('Layout loaded', $layout->toArray());
}

/**
 * Delete layout
 */
function deleteLayout($data)
{
    $layoutId = isset($data['id']) ? intval($data['id']) : 0;

    if (!$layoutId) {
        Utility::ajaxResponseFalse('Invalid layout ID');
    }

    // TODO: Verify user owns this layout
    if (!DashboardInstance::delete($layoutId)) {
        Utility::ajaxResponseFalse('Failed to delete layout');
    }

    Utility::ajaxResponseTrue('Dashboard deleted successfully');
}

/**
 * Update layout name
 */
function updateLayoutName($data)
{
    $layoutId = isset($data['id']) ? intval($data['id']) : 0;
    $name = isset($data['name']) ? trim($data['name']) : '';
    // TODO: Get actual user ID from session
    $userId = 1;

    if (!$layoutId) {
        Utility::ajaxResponseFalse('Invalid layout ID');
    }

    if (empty($name)) {
        Utility::ajaxResponseFalse('Layout name cannot be empty');
    }

    $layout = new DashboardInstance($layoutId);
    if (!$layout->getId()) {
        Utility::ajaxResponseFalse('Layout not found');
    }

    // Check if user owns this layout
    if ($layout->getUserId() != $userId) {
        Utility::ajaxResponseFalse('Unauthorized');
    }

    $layout->setName($name);
    $layout->setUpdatedUid($userId);

    if (!$layout->update()) {
        Utility::ajaxResponseFalse('Failed to update layout name');
    }

    Utility::ajaxResponseTrue('Layout name updated', array(
        'id' => $layout->getId(),
        'name' => $layout->getName()
    ));
}

/**
 * Update specific area content
 */
function updateAreaContent($data)
{
    $layoutId = isset($data['layout_id']) ? intval($data['layout_id']) : 0;
    $sectionId = isset($data['section_id']) ? $data['section_id'] : '';
    $areaId = isset($data['area_id']) ? $data['area_id'] : '';
    $content = isset($data['content']) ? $data['content'] : array();

    if (!$layoutId) {
        Utility::ajaxResponseFalse('Invalid layout ID');
    }

    $layout = new DashboardInstance($layoutId);
    if (!$layout->getId()) {
        Utility::ajaxResponseFalse('Layout not found');
    }

    // TODO: Verify user owns this layout

    if (!$layout->updateAreaContent($sectionId, $areaId, $content)) {
        Utility::ajaxResponseFalse('Failed to update content');
    }

    Utility::ajaxResponseTrue('Content updated successfully');
}

/**
 * Add new section
 */
function addSection($data)
{
    $layoutId = isset($data['layout_id']) ? intval($data['layout_id']) : 0;
    $position = isset($data['position']) ? $data['position'] : 'bottom'; // top or bottom
    $columns = isset($data['columns']) ? intval($data['columns']) : 1;

    if (!$layoutId) {
        Utility::ajaxResponseFalse('Invalid layout ID');
    }

    $layout = new DashboardInstance($layoutId);
    if (!$layout->getId()) {
        Utility::ajaxResponseFalse('Layout not found');
    }

    // TODO: Verify user owns this layout

    // Create empty section
    $sectionData = DashboardBuilder::createEmptySection($columns);

    if (!$layout->addSection($sectionData, $position)) {
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
    $layoutId = isset($data['layout_id']) ? intval($data['layout_id']) : 0;
    $templateId = isset($data['template_id']) ? intval($data['template_id']) : 0;
    $position = isset($data['position']) ? $data['position'] : 'bottom';

    if (!$layoutId) {
        Utility::ajaxResponseFalse('Invalid layout ID');
    }

    if (!$templateId) {
        Utility::ajaxResponseFalse('Invalid template ID');
    }

    $layout = new DashboardInstance($layoutId);
    if (!$layout->getId()) {
        Utility::ajaxResponseFalse('Layout not found');
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
        $sectionData['sid'] = 's' . time() . rand(1000, 9999);

        // Add section at the specified position
        if (!$layout->addSection($sectionData, $position)) {
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
    $layoutId = isset($data['layout_id']) ? intval($data['layout_id']) : 0;
    $sectionId = isset($data['section_id']) ? $data['section_id'] : '';

    if (!$layoutId) {
        Utility::ajaxResponseFalse('Invalid layout ID');
    }

    if (empty($sectionId)) {
        Utility::ajaxResponseFalse('Section ID is required');
    }

    $layout = new DashboardInstance($layoutId);
    if (!$layout->getId()) {
        Utility::ajaxResponseFalse('Layout not found');
    }

    // TODO: Verify user owns this layout

    if (!$layout->removeSection($sectionId)) {
        Utility::ajaxResponseFalse('Failed to remove section');
    }

    Utility::ajaxResponseTrue('Section removed successfully');
}

/**
 * Reorder sections (drag-drop)
 */
function reorderSections($data)
{
    $layoutId = isset($data['layout_id']) ? intval($data['layout_id']) : 0;
    $order = isset($data['order']) ? $data['order'] : array(); // Array of section IDs in new order

    // Decode if it's a JSON string
    if (is_string($order)) {
        $order = json_decode($order, true);
    }

    if (!$layoutId) {
        Utility::ajaxResponseFalse('Invalid layout ID');
    }

    if (empty($order) || !is_array($order)) {
        Utility::ajaxResponseFalse('Order array is required');
    }

    $layout = new DashboardInstance($layoutId);
    if (!$layout->getId()) {
        Utility::ajaxResponseFalse('Layout not found');
    }

    // TODO: Verify user owns this layout

    if (!$layout->reorderSections($order)) {
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
    $templates = DashboardTemplate::getAllGrouped();
    require_once SystemConfig::templatesPath() . 'dashboard/template-list.php';
}

/**
 * Show template creator form
 */
function showTemplateCreator()
{
    $pageTitle = 'Create Template';
    $template = null;
    $categories = DashboardTemplateCategory::getAll();
    require_once SystemConfig::templatesPath() . 'dashboard/template-editor.php';
}

/**
 * Show template editor form
 */
function showTemplateEditor($templateId)
{
    if (!$templateId) {
        Utility::redirect('layout/templates');
        return;
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId()) {
        Utility::redirect('layout/templates');
        return;
    }

    $pageTitle = 'Edit Template';
    $categories = DashboardTemplateCategory::getAll();
    require_once SystemConfig::templatesPath() . 'dashboard/template-editor.php';
}

/**
 * Show template builder (structure editor)
 */
function showTemplateBuilder($templateId)
{
    if (!$templateId) {
        Utility::redirect('layout/templates');
        return;
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId() || !$template->getName()) {
        Utility::redirect('layout/templates');
        return;
    }

    $categories = DashboardTemplateCategory::getAll();
    require_once SystemConfig::templatesPath() . 'dashboard/template-builder.php';
}

/**
 * Show template preview
 */
function showTemplatePreview($templateId)
{
    if (!$templateId) {
        Utility::redirect('layout/templates');
        return;
    }

    $template = new DashboardTemplate($templateId);
    if (!$template->getId() || !$template->getName()) {
        Utility::redirect('layout/templates');
        return;
    }

    require_once SystemConfig::templatesPath() . 'dashboard/template-preview.php';
}

/**
 * Create new template
 */
function createTemplate($data)
{
    $name = isset($data['name']) ? trim($data['name']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $ltcid = isset($data['ltcid']) ? intval($data['ltcid']) : null;
    // TODO: Get actual user ID from session
    $userId = 1;

    if (empty($name)) {
        Utility::ajaxResponseFalse('Template name is required');
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
    $template->setLtcid($ltcid);
    $template->setStructure($structure);
    $template->setIsSystem(0); // User template
    $template->setCreatedUid($userId);

    if (!$template->insert()) {
        Utility::ajaxResponseFalse('Failed to create template');
    }

    Utility::ajaxResponseTrue('Template created successfully', array(
        'ltid' => $template->getId(),
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
    $ltcid = isset($data['ltcid']) && $data['ltcid'] !== '' ? intval($data['ltcid']) : null;
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

    $template->setName($name);
    $template->setDescription($description);
    $template->setLtcid($ltcid);
    $template->setUpdatedUid($userId);

    if (!$template->update()) {
        Utility::ajaxResponseFalse('Failed to update template');
    }

    Utility::ajaxResponseTrue('Template updated successfully', array(
        'ltid' => $template->getId(),
        'name' => $template->getName()
    ));
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

    // Check if template is in use by any layouts
    $db = Rapidkart::getInstance()->getDB();
    $sql = "SELECT COUNT(*) as count FROM " . SystemTables::DB_TBL_DASHBOARD_INSTANCE . "
            WHERE ltid = '::ltid' AND lisid != 3";
    $result = $db->query($sql, array('::ltid' => $templateId));

    if ($result && $db->numRows($result) > 0) {
        $row = $db->fetchAssoc($result);
        if ($row && $row['count'] > 0) {
            Utility::ajaxResponseFalse(
                'Cannot delete template. It is being used by ' . $row['count'] . ' layout(s)'
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
    $newTemplate->setLtcid($sourceTemplate->getLtcid());
    $newTemplate->setThumbnail($sourceTemplate->getThumbnail());
    $newTemplate->setStructure($sourceTemplate->getStructure());
    $newTemplate->setIsSystem(0); // Always user template
    $newTemplate->setCreatedUid($userId);

    if (!$newTemplate->insert()) {
        Utility::ajaxResponseFalse('Failed to duplicate template');
    }

    Utility::ajaxResponseTrue('Template duplicated successfully', array(
        'ltid' => $newTemplate->getId(),
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
        'ltid' => $template->getId()
    ));
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
        'ltid' => $template->getId(),
        'name' => $template->getName(),
        'description' => $template->getDescription(),
        'ltcid' => $template->getLtcid(),
        'structure' => $template->getStructure(),
        'is_system' => $template->getIsSystem()
    ));
}
