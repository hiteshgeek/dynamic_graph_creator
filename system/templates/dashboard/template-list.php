<?php
require_once __DIR__ . '/../../includes/dashboard/template-preview-component.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Templates - Dynamic Graph Creator</title>

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
            <h1>Dashboard Templates</h1>
        </div>
        <div class="page-header-right">
            <a href="?urlq=dashboard" class="btn btn-secondary">
                <i class="fas fa-th-large"></i> Dashboards
            </a>
            <a href="?urlq=graph" class="btn btn-secondary">
                <i class="fas fa-chart-line"></i> Graphs
            </a>
            <?php if (!empty($templates)): ?>
            <a href="?urlq=dashboard/template/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Template
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container-fluid">
        <div class="template-list-page">
            <?php if (empty($templates)): ?>
            <?php echo Utility::renderEmptyState(
                'fa-clone',
                'No Templates Yet',
                'Create your first template to get started',
                'Create Template',
                '?urlq=dashboard/template/create',
                'purple'
            ); ?>
            <?php else: ?>
                <?php foreach ($templates as $categorySlug => $categoryData): ?>
                <div class="template-category-section">
                    <div class="category-header">
                        <h2><?php echo htmlspecialchars($categoryData['category']['name']); ?></h2>
                        <?php if (!empty($categoryData['category']['description'])): ?>
                        <p class="category-description"><?php echo htmlspecialchars($categoryData['category']['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="template-grid">
                        <?php if (empty($categoryData['templates'])): ?>
                        <!-- Empty Category State -->
                        <div class="template-card template-card-empty" data-category-id="<?php echo $categoryData['category']['dtcid']; ?>">
                            <div class="empty-category-content">
                                <div class="empty-category-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <h4>No Templates Yet</h4>
                                <p>This category is empty. Create your first template or delete this category.</p>
                                <div class="empty-category-actions">
                                    <a href="?urlq=dashboard/template/create" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Create Template
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm delete-category-btn"
                                            data-category-id="<?php echo $categoryData['category']['dtcid']; ?>"
                                            data-category-name="<?php echo htmlspecialchars($categoryData['category']['name']); ?>">
                                        <i class="fas fa-trash"></i> Delete Category
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($categoryData['templates'] as $template): ?>
                        <div class="template-card" data-template-id="<?php echo $template['dtid']; ?>">
                            <div class="template-preview">
                                <?php
                                $structure = json_decode($template['structure'], true);
                                echo renderTemplatePreview($structure);
                                ?>
                            </div>
                            <div class="template-info">
                                <h4><?php echo htmlspecialchars($template['name']); ?></h4>
                                <?php if (!empty($template['description'])): ?>
                                <p><?php echo htmlspecialchars($template['description']); ?></p>
                                <?php endif; ?>
                                <?php if ($template['is_system']): ?>
                                <div class="template-meta">
                                    <span class="badge badge-system">
                                        <i class="fas fa-lock"></i> System
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="template-card-actions">
                                <button class="btn-icon btn-primary"
                                        title="Preview"
                                        onclick="window.location='?urlq=dashboard/template/preview/<?php echo $template['dtid']; ?>'">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (!$template['is_system']): ?>
                                <button class="btn-icon btn-warning"
                                        title="Edit Structure"
                                        onclick="window.location='?urlq=dashboard/template/builder/<?php echo $template['dtid']; ?>'">
                                    <i class="fas fa-pencil"></i>
                                </button>
                                <?php endif; ?>
                                <button class="btn-icon btn-success duplicate-template-btn"
                                        title="Duplicate"
                                        data-template-id="<?php echo $template['dtid']; ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <?php if (!$template['is_system']): ?>
                                <button class="btn-icon btn-danger delete-template-btn"
                                        title="Delete"
                                        data-template-id="<?php echo $template['dtid']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
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
</body>
</html>
