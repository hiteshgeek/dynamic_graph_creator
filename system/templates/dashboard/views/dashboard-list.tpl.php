
<?php
    $rightContent = '';
    // Only show admin links (Templates, Graphs, Filters) for authorized users
    if (DGCHelper::hasAdminAccess()) {
        $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
        $rightContent .= '<a href="?urlq=graph" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Graphs"><i class="fas fa-chart-line"></i></a>';
        $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
    }
    if (!empty($dashboards)) {
        $rightContent .= '<a href="?urlq=dashboard/builder" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create Dashboard"><i class="fas fa-plus"></i></a>';
    }
    echo DGCHelper::renderPageHeader([
        'title' => 'Dashboards',
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="container">
        <div id="dashboard-list" class="dashboard-list-page">
            <!-- Card header commented out - redundant with page header
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h2>My Dashboards</h2>
                        <span class="text-muted"><?php echo count($dashboards); ?> dashboard<?php echo count($dashboards) !== 1 ? 's' : ''; ?></span>
                    </div>
                </div>
            </div>
            -->

            <?php if (empty($dashboards)): ?>
            <?php echo DGCHelper::renderEmptyState(
                'fa-th-large',
                'No Dashboards Yet',
                'Create your first dashboard to visualize your data',
                'Create Dashboard',
                '?urlq=dashboard/builder',
                'blue'
            ); ?>
            <?php else: ?>
            <div class="item-card-grid">
                <?php foreach ($dashboards as $dashboard): ?>
                <div class="item-card" data-dashboard-id="<?php echo $dashboard->getId(); ?>">
                    <div class="item-card-content">
                        <h3><?php echo htmlspecialchars($dashboard->getName()); ?></h3>
                        <?php if ($dashboard->getDescription()): ?>
                        <p class="item-card-description"><?php echo htmlspecialchars($dashboard->getDescription()); ?></p>
                        <?php endif; ?>
                        <?php if ($dashboard->getIsSystem()): ?>
                        <div class="item-card-tags">
                            <span class="badge badge-system">
                                <i class="fas fa-lock"></i> System
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="item-card-actions">
                        <a href="?urlq=dashboard/preview/<?php echo $dashboard->getId(); ?>"
                           class="btn btn-icon btn-outline-primary"
                           data-bs-toggle="tooltip"
                           title="View Mode">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if (!$dashboard->getIsSystem()): ?>
                        <a href="?urlq=dashboard/builder/<?php echo $dashboard->getId(); ?>"
                           class="btn btn-icon btn-outline-design"
                           data-bs-toggle="tooltip"
                           title="Design Mode">
                            <i class="fas fa-paint-brush"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

