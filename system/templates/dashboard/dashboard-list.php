<?php
    $rightContent = '<a href="?urlq=dashboard/templates" class="btn btn-secondary btn-sm"><i class="fas fa-clone"></i> Templates</a>';
    $rightContent .= '<a href="?urlq=graph" class="btn btn-secondary btn-sm"><i class="fas fa-chart-line"></i> Graphs</a>';
    if (!empty($dashboards)) {
        $rightContent .= '<a href="?urlq=dashboard/builder" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Create Dashboard</a>';
    }
    echo Utility::renderPageHeader([
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
            <?php echo Utility::renderEmptyState(
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
                        <div class="item-card-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock"></i>
                                <?php echo date('M d, Y', strtotime($dashboard->getUpdatedTs())); ?>
                            </span>
                        </div>
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
                           class="btn-icon btn-primary"
                           title="View Mode">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if (!$dashboard->getIsSystem()): ?>
                        <a href="?urlq=dashboard/builder/<?php echo $dashboard->getId(); ?>"
                           class="btn-icon btn-design"
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

