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
            <?php if ($layout && $layout->getId()): ?>
            <div class="layout-name-editor">
                <h1 id="layout-name-display"><?php echo htmlspecialchars($layout->getName()); ?></h1>
                <input type="text"
                       id="layout-name-input"
                       class="form-control layout-name-input"
                       value="<?php echo htmlspecialchars($layout->getName()); ?>"
                       placeholder="Layout Name"
                       style="display: none;">
                <button id="edit-name-btn" class="btn-icon btn-warning" title="Edit Name">
                    <i class="fas fa-edit"></i>
                </button>
                <button id="save-name-btn" class="btn-icon btn-success" title="Save Name" style="display: none;">
                    <i class="fas fa-check"></i>
                </button>
                <button id="cancel-name-btn" class="btn-icon btn-secondary" title="Cancel" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php else: ?>
            <h1>Create Layout</h1>
            <?php endif; ?>
        </div>
        <div class="page-header-right">
            <?php if ($layout && $layout->getId()): ?>
            <div class="save-indicator saved" style="display: flex;">
                <i class="fas fa-check-circle"></i>
                <span>Saved</span>
            </div>
            <a href="?urlq=layout/preview/<?php echo $layout->getId(); ?>"
               class="btn btn-primary"
               title="View Layout">
                <i class="fas fa-eye"></i> View Layout
            </a>
            <?php else: ?>
            <div class="save-indicator" style="display: none;">
                <i class="fas fa-check-circle"></i>
                <span>Saved</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="layout-builder"
         class="layout-builder"
         data-layout-id="<?php echo $layout ? $layout->getId() : ''; ?>"
         data-breakpoint="desktop">

        <div class="builder-body">
            <div class="builder-main">
                <div class="grid-editor">
                    <?php if ($layout && $layout->getId()): ?>
                    <div class="layout-sections">
                        <div class="loading-message">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading layout...</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="choose-template-card">
                        <div class="choose-template-content">
                            <i class="fas fa-th-large"></i>
                            <h2>Create Your Dashboard</h2>
                            <p>Choose from our pre-designed templates to get started</p>
                            <button class="choose-template-btn">
                                <i class="fas fa-th-large"></i> Choose Template
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
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

            // Handle layout name editing
            const nameDisplay = document.getElementById('layout-name-display');
            const nameInput = document.getElementById('layout-name-input');
            const editBtn = document.getElementById('edit-name-btn');
            const saveBtn = document.getElementById('save-name-btn');
            const cancelBtn = document.getElementById('cancel-name-btn');

            if (nameDisplay && nameInput && editBtn && saveBtn && cancelBtn && layoutId) {
                // Edit button - show input, hide display
                editBtn.addEventListener('click', function() {
                    nameDisplay.style.display = 'none';
                    nameInput.style.display = 'block';
                    nameInput.focus();
                    nameInput.select();
                    editBtn.style.display = 'none';
                    saveBtn.style.display = 'inline-flex';
                    cancelBtn.style.display = 'inline-flex';
                });

                // Cancel button - restore display, hide input
                cancelBtn.addEventListener('click', function() {
                    nameInput.value = nameInput.defaultValue;
                    nameInput.style.display = 'none';
                    nameDisplay.style.display = 'block';
                    editBtn.style.display = 'inline-flex';
                    saveBtn.style.display = 'none';
                    cancelBtn.style.display = 'none';
                });

                // Save button - update name
                saveBtn.addEventListener('click', function() {
                    const newName = nameInput.value.trim();
                    if (!newName) {
                        Toast.error('Layout name cannot be empty');
                        return;
                    }

                    // Show saving state
                    saveBtn.disabled = true;
                    cancelBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    Ajax.post('update_layout_name', { id: layoutId, name: newName })
                        .then(result => {
                            if (result.success) {
                                Toast.success('Layout name updated');
                                // Update display text and default value
                                nameDisplay.textContent = newName;
                                nameInput.defaultValue = newName;
                                // Switch back to display mode
                                nameInput.style.display = 'none';
                                nameDisplay.style.display = 'block';
                                editBtn.style.display = 'inline-flex';
                                saveBtn.style.display = 'none';
                                cancelBtn.style.display = 'none';
                                saveBtn.innerHTML = '<i class="fas fa-check"></i>';
                                saveBtn.disabled = false;
                                cancelBtn.disabled = false;
                            } else {
                                Toast.error(result.message || 'Failed to update layout name');
                                saveBtn.innerHTML = '<i class="fas fa-check"></i>';
                                saveBtn.disabled = false;
                                cancelBtn.disabled = false;
                            }
                        })
                        .catch(error => {
                            Toast.error('Failed to update layout name');
                            saveBtn.innerHTML = '<i class="fas fa-check"></i>';
                            saveBtn.disabled = false;
                            cancelBtn.disabled = false;
                        });
                });

                // Handle Enter key to save
                nameInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        saveBtn.click();
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        cancelBtn.click();
                    }
                });
            }
        });
    </script>
</body>
</html>
