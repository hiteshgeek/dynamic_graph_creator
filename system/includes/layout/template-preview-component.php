<?php
/**
 * Shared Template Preview Component
 * Renders template structure preview using consistent HTML structure
 *
 * @param array $structure The decoded template structure array
 * @return string HTML markup for template preview
 */
function renderTemplatePreview($structure) {
    if ($structure && isset($structure['sections'])) {
        $html = '<div class="template-preview-grid">';
        foreach ($structure['sections'] as $section) {
            $html .= '<div class="preview-section" style="display: grid; grid-template-columns: ' . htmlspecialchars($section['gridTemplate']) . '; gap: 2px;">';
            if (isset($section['areas'])) {
                foreach ($section['areas'] as $area) {
                    // Check if area has sub-rows
                    if (isset($area['hasSubRows']) && $area['hasSubRows'] && isset($area['subRows']) && count($area['subRows']) > 0) {
                        $rowHeights = array_map(function($row) {
                            return isset($row['height']) ? $row['height'] : '1fr';
                        }, $area['subRows']);
                        $rowHeightsStr = implode(' ', $rowHeights);
                        $html .= '<div class="preview-area-nested" style="display: grid; grid-template-rows: ' . $rowHeightsStr . '; gap: 2px;">';
                        foreach ($area['subRows'] as $subRow) {
                            $html .= '<div class="preview-sub-row"></div>';
                        }
                        $html .= '</div>';
                    } else {
                        // Regular area
                        $html .= '<div class="preview-area"></div>';
                    }
                }
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    } else {
        return '<div class="template-preview-fallback"><i class="fas fa-th-large"></i></div>';
    }
}
