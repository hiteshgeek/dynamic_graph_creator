<?php

/**
 * LayoutBuilder - Helper utilities for layout manipulation
 *
 * @author Dynamic Graph Creator
 */
class LayoutBuilder
{
    /**
     * Generate CSS Grid template from structure
     */
    public static function generateGridTemplate($section)
    {
        if (!isset($section['areas']) || !is_array($section['areas'])) {
            return '1fr';
        }

        $template = array();
        foreach ($section['areas'] as $area) {
            if (isset($area['colSpanFr'])) {
                $template[] = $area['colSpanFr'];
            }
        }

        return !empty($template) ? implode(' ', $template) : '1fr';
    }

    /**
     * Validate layout structure
     */
    public static function validateStructure($structure)
    {
        // Check if structure is array
        if (!is_array($structure)) {
            return false;
        }

        // Check if sections exist
        if (!isset($structure['sections']) || !is_array($structure['sections'])) {
            return false;
        }

        // Validate each section
        foreach ($structure['sections'] as $section) {
            if (!isset($section['sid']) || !isset($section['areas'])) {
                return false;
            }

            if (!is_array($section['areas'])) {
                return false;
            }

            // Validate each area
            foreach ($section['areas'] as $area) {
                if (!isset($area['aid']) || !isset($area['colSpanFr'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Merge template structure with user content
     */
    public static function mergeTemplateWithContent($templateStructure, $userContent)
    {
        $merged = is_string($templateStructure) ? json_decode($templateStructure, true) : $templateStructure;
        $content = is_string($userContent) ? json_decode($userContent, true) : $userContent;

        if (!$merged || !$content) {
            return is_string($templateStructure) ? $templateStructure : json_encode($templateStructure);
        }

        // Merge section by section
        if (isset($content['sections']) && is_array($content['sections'])) {
            foreach ($merged['sections'] as $sIndex => &$section) {
                if (isset($content['sections'][$sIndex])) {
                    foreach ($section['areas'] as $aIndex => &$area) {
                        if (isset($content['sections'][$sIndex]['areas'][$aIndex]['content'])) {
                            $area['content'] = $content['sections'][$sIndex]['areas'][$aIndex]['content'];
                        }
                    }
                }
            }
        }

        return json_encode($merged);
    }

    /**
     * Generate responsive breakpoint rules
     */
    public static function generateResponsiveCSS($structure)
    {
        if (!isset($structure['responsive'])) {
            return '';
        }

        $css = array();
        $mobile = isset($structure['responsive']['mobile']['breakpoint']) ?
                  $structure['responsive']['mobile']['breakpoint'] : 768;
        $tablet = isset($structure['responsive']['tablet']['breakpoint']) ?
                  $structure['responsive']['tablet']['breakpoint'] : 1024;

        // Mobile breakpoint
        $css[] = "@media (max-width: {$mobile}px) {";
        $css[] = "  .layout-section { grid-template-columns: 1fr !important; }";
        $css[] = "  .layout-area { grid-column: span 1 !important; }";
        $css[] = "}";

        // Tablet breakpoint
        $css[] = "@media (min-width: " . ($mobile + 1) . "px) and (max-width: {$tablet}px) {";
        $css[] = "  .layout-section[data-columns='3'], .layout-section[data-columns='4'] {";
        $css[] = "    grid-template-columns: repeat(2, 1fr) !important;";
        $css[] = "  }";
        $css[] = "}";

        return implode("\n", $css);
    }

    /**
     * Generate unique section ID
     */
    public static function generateSectionId()
    {
        return 's' . uniqid();
    }

    /**
     * Generate unique area ID
     */
    public static function generateAreaId()
    {
        return 'a' . uniqid();
    }

    /**
     * Create empty section template
     */
    public static function createEmptySection($columns = 1)
    {
        $areas = array();
        $gridTemplate = array();

        for ($i = 0; $i < $columns; $i++) {
            $areas[] = array(
                'aid' => self::generateAreaId(),
                'colSpan' => 1,
                'colSpanFr' => '1fr',
                'rowSpan' => 1,
                'minWidth' => '300px',
                'responsive' => array(
                    'mobile' => array('colSpan' => 1, 'order' => $i + 1),
                    'tablet' => array('colSpan' => 1, 'order' => $i + 1),
                    'desktop' => array('colSpan' => 1, 'order' => $i + 1)
                ),
                'content' => array(
                    'type' => 'empty',
                    'widgetId' => null,
                    'widgetType' => null,
                    'config' => array()
                ),
                'emptyState' => array(
                    'enabled' => true,
                    'icon' => 'fa-plus-circle',
                    'message' => 'Add content here'
                )
            );
            $gridTemplate[] = '1fr';
        }

        return array(
            'sid' => self::generateSectionId(),
            'type' => 'row',
            'height' => 'auto',
            'heightFr' => 1,
            'minHeight' => '200px',
            'areas' => $areas,
            'gridTemplate' => implode(' ', $gridTemplate),
            'gap' => '16px'
        );
    }

    /**
     * Extract section IDs from structure
     */
    public static function extractSectionIds($structure)
    {
        $ids = array();

        if (isset($structure['sections']) && is_array($structure['sections'])) {
            foreach ($structure['sections'] as $section) {
                if (isset($section['sid'])) {
                    $ids[] = $section['sid'];
                }
            }
        }

        return $ids;
    }

    /**
     * Count total areas in layout
     */
    public static function countAreas($structure)
    {
        $count = 0;

        if (isset($structure['sections']) && is_array($structure['sections'])) {
            foreach ($structure['sections'] as $section) {
                if (isset($section['areas']) && is_array($section['areas'])) {
                    $count += count($section['areas']);
                }
            }
        }

        return $count;
    }

    /**
     * Get section by ID
     */
    public static function getSectionById($structure, $sectionId)
    {
        if (!isset($structure['sections'])) {
            return null;
        }

        foreach ($structure['sections'] as $section) {
            if (isset($section['sid']) && $section['sid'] === $sectionId) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Get area by ID
     */
    public static function getAreaById($structure, $sectionId, $areaId)
    {
        $section = self::getSectionById($structure, $sectionId);

        if (!$section || !isset($section['areas'])) {
            return null;
        }

        foreach ($section['areas'] as $area) {
            if (isset($area['aid']) && $area['aid'] === $areaId) {
                return $area;
            }
        }

        return null;
    }
}
