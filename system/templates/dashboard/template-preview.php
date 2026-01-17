<?php
// Template preview component is used for thumbnail previews in template list, not here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php $name = $template->getName(); echo htmlspecialchars($name ? $name : 'Template'); ?> - Preview</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Google Sans Font -->
    <link href="https://fonts.googleapis.com/css2?family=Product+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <?php if ($css = Utility::getCss('common')): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
    <?php if ($css = Utility::getCss('dashboard')): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="template-preview-page">
    <?php
    $templateName = $template->getName();
    $templateName = $templateName ? $templateName : 'Template';

    $badges = [];
    if ($template->getIsSystem()) {
        $badges[] = ['label' => 'System', 'icon' => 'fa-lock', 'class' => 'badge-system'];
    }

    $rightContent = '<button class="btn btn-success btn-sm duplicate-template-btn" data-template-id="' . $template->getId() . '"><i class="fas fa-copy"></i> Duplicate Template</button>';
    if (!$template->getIsSystem()) {
        $rightContent .= '<a href="?urlq=dashboard/template/builder/' . $template->getId() . '" class="btn btn-design btn-sm btn-design-mode"><i class="fas fa-paint-brush"></i> Design Mode</a>';
        $rightContent .= '<button class="btn btn-danger btn-sm delete-template-btn" data-template-id="' . $template->getId() . '"><i class="fas fa-trash"></i> Delete Template</button>';
    }

    echo Utility::renderPageHeader([
        'title' => $templateName,
        'backUrl' => '?urlq=dashboard/templates',
        'backLabel' => 'Templates',
        'badges' => $badges,
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="container-fluid">
        <div id="template-preview" class="dashboard-preview" data-template-id="<?php echo $template->getId(); ?>">
            <div class="dashboard-sections">
                <?php
                $structure = $template->getStructureArray();
                if (isset($structure['sections']) && count($structure['sections']) > 0):
                    foreach ($structure['sections'] as $section):
                ?>
                    <div class="dashboard-section"
                        data-section-id="<?php echo htmlspecialchars($section['sid']); ?>"
                        style="grid-template-columns: <?php echo htmlspecialchars($section['gridTemplate']); ?>;">

                        <?php foreach ($section['areas'] as $area): ?>
                            <?php
                            // Check if area has sub-rows (nested structure)
                            $hasSubRows = isset($area['hasSubRows']) && $area['hasSubRows'] && isset($area['subRows']) && count($area['subRows']) > 0;
                            ?>

                            <?php if ($hasSubRows): ?>
                                <!-- Area with sub-rows -->
                                <?php
                                $rowHeights = array_map(function ($row) {
                                    return isset($row['height']) ? $row['height'] : '1fr';
                                }, $area['subRows']);
                                $rowHeightsStr = implode(' ', $rowHeights);
                                ?>
                                <div class="dashboard-area dashboard-area-nested"
                                    data-area-id="<?php echo htmlspecialchars($area['aid']); ?>"
                                    style="grid-column: span <?php echo isset($area['colSpan']) ? intval($area['colSpan']) : 1; ?>; grid-template-rows: <?php echo $rowHeightsStr; ?>;">

                                    <?php foreach ($area['subRows'] as $subRow): ?>
                                        <div class="dashboard-sub-row" data-row-id="<?php echo htmlspecialchars($subRow['rowId']); ?>">
                                            <?php
                                            $icon = isset($subRow['emptyState']['icon']) ? $subRow['emptyState']['icon'] : 'fa-plus-circle';
                                            $message = isset($subRow['emptyState']['message']) ? $subRow['emptyState']['message'] : 'Empty cell';
                                            echo Utility::renderDashboardCellEmpty($icon, $message);
                                            ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php else: ?>
                                <!-- Regular single area -->
                                <div class="dashboard-area"
                                    data-area-id="<?php echo htmlspecialchars($area['aid']); ?>"
                                    style="grid-column: span <?php echo isset($area['colSpan']) ? intval($area['colSpan']) : 1; ?>;">

                                    <?php
                                    $icon = isset($area['emptyState']['icon']) ? $area['emptyState']['icon'] : 'fa-plus-circle';
                                    $message = isset($area['emptyState']['message']) ? $area['emptyState']['message'] : 'Empty cell';
                                    echo Utility::renderDashboardCellEmpty($icon, $message);
                                    ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php
                endforeach;
            else:
                echo Utility::renderEmptyState(
                    'fa-th-large',
                    'This Template is Empty',
                    'No sections have been added to this template yet.<br>Use the "Design Mode" button above to add sections.'
                );
            endif;
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = Utility::getJs('common')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
    <script src="system/scripts/src/Theme.js"></script>
    <?php if ($js = Utility::getJs('dashboard')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <script>
        // Initialize TemplateManager for delete and duplicate buttons
        document.addEventListener('DOMContentLoaded', function() {
            if (window.TemplateManager) {
                TemplateManager.initTemplateList();
            }
        });
    </script>
</body>
</html>
