<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Template: <?php echo htmlspecialchars($template->getName()); ?> - Dynamic Graph Creator</title>

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
            <a href="?urlq=layout/templates" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1><?php echo htmlspecialchars($template->getName()); ?></h1>
            <?php if ($template->getIsSystem()): ?>
            <span class="badge badge-system">
                <i class="fas fa-lock"></i> System Template (Read-Only)
            </span>
            <?php endif; ?>
        </div>
        <div class="page-header-right">
            <?php if (!$template->getIsSystem()): ?>
            <div class="save-indicator saved" style="display: flex;">
                <i class="fas fa-check-circle"></i>
                <span>Saved</span>
            </div>
            <?php endif; ?>
            <a href="?urlq=layout/template/preview/<?php echo $template->getId(); ?>"
               class="btn btn-primary"
               title="Preview Template">
                <i class="fas fa-eye"></i> Preview Template
            </a>
        </div>
    </div>

    <div id="layout-builder"
         class="layout-builder"
         data-mode="template"
         data-template-id="<?php echo $template->getId(); ?>"
         data-is-system="<?php echo $template->getIsSystem() ? '1' : '0'; ?>">

        <div class="builder-body">
            <div class="builder-main">
                <div class="grid-editor">
                    <div class="layout-sections">
                        <div class="loading-message">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading template...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div id="add-section-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Number of Columns</label>
                        <select class="form-select" id="section-columns">
                            <option value="1">1 Column</option>
                            <option value="2" selected>2 Columns</option>
                            <option value="3">3 Columns</option>
                            <option value="4">4 Columns</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-add-section">Add Section</button>
                </div>
            </div>
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

    <script>
        // Initialize template builder when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('layout-builder');
            const templateId = parseInt(container.dataset.templateId);
            const isSystem = container.dataset.isSystem === '1';

            // Initialize layout builder in template mode
            if (typeof LayoutBuilder !== 'undefined') {
                window.layoutBuilderInstance = new LayoutBuilder(container, {
                    mode: 'template',
                    templateId: templateId,
                    isReadOnly: isSystem
                });
                window.layoutBuilderInstance.init();
            } else {
                console.error('LayoutBuilder not loaded. Make sure layout.js is included.');
            }
        });
    </script>
</body>
</html>
