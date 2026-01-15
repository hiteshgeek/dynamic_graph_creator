<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboards - Dynamic Graph Creator</title>

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
<body>
    <div class="page-header">
        <div class="page-header-left">
            <h1>Dashboards</h1>
        </div>
        <div class="page-header-right">
            <a href="?urlq=dashboard/templates" class="btn btn-secondary">
                <i class="fas fa-clone"></i> Templates
            </a>
            <a href="?urlq=graph" class="btn btn-secondary">
                <i class="fas fa-chart-line"></i> Graphs
            </a>
            <a href="?urlq=dashboard/builder" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Dashboard
            </a>
        </div>
    </div>

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
            <div class="dashboard-empty-state">
                <i class="fas fa-th-large"></i>
                <p>No dashboards created yet</p>
                <span>Click "Create Dashboard" to build your first dashboard</span>
            </div>
            <?php else: ?>
            <div class="dashboard-grid">
                <?php foreach ($dashboards as $dashboard): ?>
                <div class="dashboard-card" data-dashboard-id="<?php echo $dashboard->getId(); ?>">
                    <div class="dashboard-card-content">
                        <h3><?php echo htmlspecialchars($dashboard->getName()); ?></h3>
                        <div class="dashboard-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock"></i>
                                <?php echo date('M d, Y', strtotime($dashboard->getUpdatedTs())); ?>
                            </span>
                        </div>
                        <div class="dashboard-tags">
                            <?php if ($dashboard->getIsSystem()): ?>
                            <span class="badge badge-system">
                                <i class="fas fa-lock"></i> System
                            </span>
                            <?php else: ?>
                            <span class="badge badge-custom">Custom</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="dashboard-card-actions">
                        <a href="?urlq=dashboard/preview/<?php echo $dashboard->getId(); ?>"
                           class="btn-icon btn-primary"
                           title="View Dashboard">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if (!$dashboard->getIsSystem()): ?>
                        <a href="?urlq=dashboard/builder/<?php echo $dashboard->getId(); ?>"
                           class="btn-icon btn-warning"
                           title="Edit Dashboard">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = Utility::getJs('common')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
    <?php if ($js = Utility::getJs('dashboard')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <script>
        // Dashboard list page - no additional functionality needed
    </script>
</body>
</html>
