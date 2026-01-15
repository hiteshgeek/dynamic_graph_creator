<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $layout ? 'Edit Layout' : 'Create Layout'; ?> - Dynamic Graph Creator</title>

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
            <a href="?urlq=layout" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1><?php echo $layout ? 'Edit Layout' : 'Create Layout'; ?></h1>
        </div>
        <div class="page-header-right">
            <div class="responsive-toggle"></div>
            <div class="save-indicator saved">
                <i class="fas fa-check-circle"></i>
                <span>Saved</span>
            </div>
        </div>
    </div>

    <div id="layout-builder"
         class="layout-builder"
         data-layout-id="<?php echo $layout ? $layout->getId() : ''; ?>"
         data-breakpoint="desktop">

        <div class="builder-body">
            <div class="builder-sidebar">
                <?php if ($layout && $layout->getId()): ?>
                <div class="sidebar-section">
                    <h3>Sections</h3>
                    <button class="add-section-btn">
                        <i class="fas fa-plus"></i> Add Section
                    </button>
                </div>
                <?php else: ?>
                <div class="sidebar-section">
                    <h3>Get Started</h3>
                    <button class="choose-template-btn">
                        <i class="fas fa-th-large"></i> Choose Template
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="builder-main">
                <div class="grid-editor">
                    <div class="layout-sections">
                        <?php if ($layout): ?>
                        <div class="loading-message">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading layout...</p>
                        </div>
                        <?php else: ?>
                        <div class="welcome-message">
                            <i class="fas fa-th-large"></i>
                            <h2>Welcome to Layout Builder</h2>
                            <p>Select a template to start building your dashboard</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Selector Modal -->
    <div id="template-modal" class="layout-template-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Choose a Layout Template</h2>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="template-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading templates...</p>
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
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <select class="form-select" id="section-position">
                            <option value="bottom" selected>Bottom</option>
                            <option value="top">Top</option>
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
        // Initialize layout builder when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('layout-builder');
            const layoutId = container.dataset.layoutId || null;

            // Initialize layout builder
            if (typeof LayoutBuilder !== 'undefined') {
                window.layoutBuilderInstance = new LayoutBuilder(container, {
                    layoutId: layoutId ? parseInt(layoutId) : null,
                    mode: 'edit'
                });
                window.layoutBuilderInstance.init();
            } else {
                console.error('LayoutBuilder not loaded. Make sure layout.js is included.');
            }
        });
    </script>
</body>
</html>
