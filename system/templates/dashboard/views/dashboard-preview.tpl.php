
<?php
    // Set company start date for datepicker presets
    echo DGCHelper::renderCompanyStartDateScript();

    $badges = [];
    if ($dashboard->getIsSystem()) {
        $badges[] = ['label' => 'System', 'icon' => 'fa-lock', 'class' => 'badge-system'];
    }

    $rightContent = '';
    // Navigation links to Graph and Filter pages (admin only)
    if (DGCHelper::hasAdminAccess()) {
        $rightContent .= '<a href="?urlq=graph" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Graphs"><i class="fas fa-chart-bar"></i></a>';
        $rightContent .= '<a href="?urlq=filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
    }
    if (!$dashboard->getIsSystem()) {
        $rightContent .= '<a href="?urlq=dashboard/builder/' . $dashboard->getId() . '" class="btn btn-icon btn-outline-design btn-design-mode" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Design Mode"><i class="fas fa-paint-brush"></i></a>';
        $rightContent .= '<button class="btn btn-icon btn-outline-danger delete-dashboard-btn" data-dashboard-id="' . $dashboard->getId() . '" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete Dashboard"><i class="fas fa-trash"></i></button>';
    }

    echo DGCHelper::renderPageHeader([
        'title' => $dashboard->getName(),
        'backUrl' => '?urlq=home',
        'backLabel' => 'Dashboards',
        'badges' => $badges,
        'rightContent' => $rightContent
    ]);

    // Dashboard filter bar - dynamically get filters from all widgets in this dashboard
    $dashboardFilters = $dashboard->getAllFilterKeys();
    echo DGCHelper::renderDashboardFilterBar($dashboardFilters);
    ?>

    <div class="container-fluid">
        <div id="dashboard-preview" class="dashboard-preview" data-dashboard-id="<?php echo $dashboard->getId(); ?>">
            <div class="dashboard-sections">
                <?php
                $structure = $dashboard->getStructureArray();
                // Debug: Check structure
                // error_log('Dashboard structure: ' . print_r($structure, true));
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
                                                // Check if subRow has widget content
                                                $hasSubRowWidget = isset($subRow['content']) &&
                                                                   isset($subRow['content']['type']) &&
                                                                   $subRow['content']['type'] !== 'empty' &&
                                                                   !empty($subRow['content']['widgetId']);
                                                ?>
                                                <?php if ($hasSubRowWidget): ?>
                                                    <?php echo DGCHelper::renderDashboardWidgetContent($subRow['content']); ?>
                                                <?php else: ?>
                                                    <?php echo DGCHelper::renderDashboardCellEmpty('', '', true); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                <?php else: ?>
                                    <!-- Regular single area -->
                                    <?php
                                    // Get row height for non-nested areas (default 1fr = 150px)
                                    $areaRowHeight = isset($area['rowHeight']) ? intval($area['rowHeight']) : 1;
                                    $minHeightStyle = $areaRowHeight > 1 ? "min-height: " . ($areaRowHeight * 150) . "px;" : "";
                                    ?>
                                    <div class="dashboard-area"
                                        data-area-id="<?php echo htmlspecialchars($area['aid']); ?>"
                                        style="grid-column: span <?php echo isset($area['colSpan']) ? intval($area['colSpan']) : 1; ?>; <?php echo $minHeightStyle; ?>">

                                        <?php
                                        // Check if area has widget content (non-empty with widgetId)
                                        $hasWidget = isset($area['content']) &&
                                                     isset($area['content']['type']) &&
                                                     $area['content']['type'] !== 'empty' &&
                                                     !empty($area['content']['widgetId']);
                                        ?>
                                        <?php if ($hasWidget): ?>
                                            <?php echo DGCHelper::renderDashboardWidgetContent($area['content']); ?>
                                        <?php else: ?>
                                            <?php echo DGCHelper::renderDashboardCellEmpty('', '', true); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php
                    endforeach;
                else:
                    echo DGCHelper::renderEmptyState(
                        'fa-th-large',
                        'This Dashboard is Empty',
                        'No sections have been added to this dashboard yet.<br>Use the "Edit Dashboard" button above to add sections.'
                    );
                endif;
                ?>
            </div>
        </div>
    </div>

