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
        case 'save_layout':
            saveLayout($_POST);
            break;
        case 'get_layout':
            getLayout($_POST);
            break;
        case 'delete_layout':
            deleteLayout($_POST);
            break;
        case 'update_area_content':
            updateAreaContent($_POST);
            break;
        case 'add_section':
            addSection($_POST);
            break;
        case 'remove_section':
            removeSection($_POST);
            break;
        case 'reorder_sections':
            reorderSections($_POST);
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
    $layouts = LayoutInstance::getUserLayouts($userId);
    require_once SystemConfig::templatesPath() . 'layout/layout-list.php';
}

/**
 * Show layout builder
 */
function showBuilder($layoutId = 0)
{
    $layout = null;
    $templates = LayoutTemplate::getAllGrouped();

    if ($layoutId) {
        $layout = new LayoutInstance($layoutId);
        if (!$layout->getId()) {
            Utility::redirect('layout');
            return;
        }
    }

    require_once SystemConfig::templatesPath() . 'layout/layout-builder.php';
}

/**
 * Show layout preview
 */
function showPreview($layoutId)
{
    $layout = new LayoutInstance($layoutId);
    if (!$layout->getId()) {
        Utility::redirect('layout');
        return;
    }

    require_once SystemConfig::templatesPath() . 'layout/layout-preview.php';
}

/**
 * Get all templates grouped by category
 */
function getTemplates($data)
{
    $templates = LayoutTemplate::getAllGrouped();
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

    $template = new LayoutTemplate($templateId);
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

    if (!LayoutBuilder::validateStructure($structureArray)) {
        Utility::ajaxResponseFalse('Invalid layout structure');
    }

    $layout = $layoutId ? new LayoutInstance($layoutId) : new LayoutInstance();

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

    Utility::ajaxResponseTrue('Layout saved successfully', array(
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

    $layout = new LayoutInstance($layoutId);
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
    if (!LayoutInstance::delete($layoutId)) {
        Utility::ajaxResponseFalse('Failed to delete layout');
    }

    Utility::ajaxResponseTrue('Layout deleted successfully');
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

    $layout = new LayoutInstance($layoutId);
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

    $layout = new LayoutInstance($layoutId);
    if (!$layout->getId()) {
        Utility::ajaxResponseFalse('Layout not found');
    }

    // TODO: Verify user owns this layout

    // Create empty section
    $sectionData = LayoutBuilder::createEmptySection($columns);

    if (!$layout->addSection($sectionData, $position)) {
        Utility::ajaxResponseFalse('Failed to add section');
    }

    Utility::ajaxResponseTrue('Section added successfully', array(
        'section' => $sectionData
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

    $layout = new LayoutInstance($layoutId);
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

    if (!$layoutId) {
        Utility::ajaxResponseFalse('Invalid layout ID');
    }

    if (empty($order) || !is_array($order)) {
        Utility::ajaxResponseFalse('Order array is required');
    }

    $layout = new LayoutInstance($layoutId);
    if (!$layout->getId()) {
        Utility::ajaxResponseFalse('Layout not found');
    }

    // TODO: Verify user owns this layout

    if (!$layout->reorderSections($order)) {
        Utility::ajaxResponseFalse('Failed to reorder sections');
    }

    Utility::ajaxResponseTrue('Sections reordered successfully');
}
