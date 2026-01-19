
<?php
    $templateName = $template->getName();
    $templateName = $templateName ? $templateName : 'Template';

    $badges = [];
    if ($template->getIsSystem()) {
        $badges[] = ['label' => 'System', 'icon' => 'fa-lock', 'class' => 'badge-system'];
    }

    $rightContent = '<button class="btn btn-icon btn-outline-success duplicate-template-btn" data-template-id="' . $template->getId() . '" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Duplicate Template"><i class="fas fa-copy"></i></button>';
    if (!$template->getIsSystem()) {
        $rightContent .= '<a href="?urlq=dashboard/template/builder/' . $template->getId() . '" class="btn btn-icon btn-outline-design btn-design-mode" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Design Mode"><i class="fas fa-paint-brush"></i></a>';
        $rightContent .= '<button class="btn btn-icon btn-outline-danger delete-template-btn" data-template-id="' . $template->getId() . '" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete Template"><i class="fas fa-trash"></i></button>';
    }

    echo DGCHelper::renderPageHeader([
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
                                // Calculate total fr for data-rows attribute
                                $totalFr = array_reduce($area['subRows'], function ($sum, $row) {
                                    return $sum + (isset($row['height']) ? intval($row['height']) : 1);
                                }, 0);
                                ?>
                                <div class="dashboard-area dashboard-area-nested"
                                    data-area-id="<?php echo htmlspecialchars($area['aid']); ?>"
                                    data-rows="<?php echo $totalFr; ?>"
                                    style="grid-column: span <?php echo isset($area['colSpan']) ? intval($area['colSpan']) : 1; ?>; grid-template-rows: <?php echo $rowHeightsStr; ?>;">

                                    <?php foreach ($area['subRows'] as $subRow): ?>
                                        <?php $rowFr = isset($subRow['height']) ? intval($subRow['height']) : 1; ?>
                                        <div class="dashboard-sub-row" data-row-id="<?php echo htmlspecialchars($subRow['rowId']); ?>" data-rows="<?php echo $rowFr ?: 1; ?>">
                                            <?php
                                            $icon = isset($subRow['emptyState']['icon']) ? $subRow['emptyState']['icon'] : 'fa-plus-circle';
                                            $message = isset($subRow['emptyState']['message']) ? $subRow['emptyState']['message'] : 'Empty cell';
                                            echo DGCHelper::renderDashboardCellEmpty($icon, $message);
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
                                    echo DGCHelper::renderDashboardCellEmpty($icon, $message);
                                    ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php
                endforeach;
            else:
                echo DGCHelper::renderEmptyState(
                    'fa-th-large',
                    'This Template is Empty',
                    'No sections have been added to this template yet.<br>Use the "Design Mode" button above to add sections.'
                );
            endif;
                ?>
            </div>
        </div>
    </div>

