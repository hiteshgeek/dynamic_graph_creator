<?php
require_once __DIR__ . '/../../includes/layout/template-preview-component.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Templates - Dynamic Graph Creator</title>

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
    <?php if ($css = Utility::getCss('layout')): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="page-header">
        <div class="page-header-left">
            <div class="nav-tabs">
                <a href="?urlq=layout" class="nav-tab">
                    <i class="fas fa-layer-group"></i> My Layouts
                </a>
                <a href="?urlq=layout/templates" class="nav-tab active">
                    <i class="fas fa-th-large"></i> Templates
                </a>
            </div>
        </div>
        <div class="page-header-right">
            <a href="?urlq=layout/template/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Template
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="template-list-page">
            <?php if (empty($templates)): ?>
            <div class="layout-empty-sections">
                <div class="empty-sections-content">
                    <i class="fas fa-th-large"></i>
                    <h3>No Templates Found</h3>
                    <p>Start by creating your first custom template</p>
                    <a href="?urlq=layout/template/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Template
                    </a>
                </div>
            </div>
            <?php else: ?>
                <?php foreach ($templates as $category => $categoryTemplates): ?>
                <div class="template-category-section">
                    <h2><?php echo ucfirst($category); ?></h2>
                    <div class="template-grid">
                        <?php foreach ($categoryTemplates as $template): ?>
                        <div class="template-card" data-template-id="<?php echo $template['ltid']; ?>">
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
                            </div>
                            <div class="template-card-actions">
                                <button class="btn-icon btn-primary"
                                        title="Preview"
                                        onclick="window.location='?urlq=layout/template/preview/<?php echo $template['ltid']; ?>'">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon btn-warning"
                                        title="Edit Structure"
                                        onclick="window.location='?urlq=layout/template/builder/<?php echo $template['ltid']; ?>'">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-success"
                                        title="Duplicate"
                                        data-template-id="<?php echo $template['ltid']; ?>"
                                        class="duplicate-template-btn">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn-icon btn-danger"
                                        title="Delete"
                                        data-template-id="<?php echo $template['ltid']; ?>"
                                        class="delete-template-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
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
    <?php if ($js = Utility::getJs('layout')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
</body>
</html>
