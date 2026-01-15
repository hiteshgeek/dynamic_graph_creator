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

<body class="template-builder-page">
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
                <button class="btn btn-warning" id="edit-template-details-btn">
                    <i class="fas fa-pencil"></i> Edit Details
                </button>
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
                        <!-- Loader will be added dynamically by JavaScript -->
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

    <!-- Edit Template Details Modal -->
    <div id="edit-template-details-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Template Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-template-name" class="form-label">Template Name *</label>
                        <input type="text"
                            class="form-control"
                            id="edit-template-name"
                            value="<?php echo htmlspecialchars($template->getName()); ?>"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-template-description" class="form-label">Description</label>
                        <textarea class="form-control"
                            id="edit-template-description"
                            rows="3"><?php echo htmlspecialchars($template->getDescription()); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-template-category" class="form-label">Category *</label>
                        <select class="form-select" id="edit-template-category" required>
                            <option value="columns" <?php echo $template->getCategory() === 'columns' ? 'selected' : ''; ?>>Columns</option>
                            <option value="mixed" <?php echo $template->getCategory() === 'mixed' ? 'selected' : ''; ?>>Mixed</option>
                            <option value="advanced" <?php echo $template->getCategory() === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                            <option value="custom" <?php echo $template->getCategory() === 'custom' ? 'selected' : ''; ?>>Custom</option>
                            <option value="_new_">+ Add New Category</option>
                        </select>
                    </div>
                    <div class="mb-3" id="new-category-input-group" style="display: none;">
                        <label for="edit-template-new-category" class="form-label">New Category Name *</label>
                        <input type="text"
                            class="form-control"
                            id="edit-template-new-category"
                            placeholder="Enter new category name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-template-details-btn">Save Changes</button>
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

            // Edit template details button
            const editDetailsBtn = document.getElementById('edit-template-details-btn');
            if (editDetailsBtn) {
                editDetailsBtn.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(document.getElementById('edit-template-details-modal'));
                    modal.show();
                });
            }

            // Handle category selection change
            const categorySelect = document.getElementById('edit-template-category');
            const newCategoryGroup = document.getElementById('new-category-input-group');
            const newCategoryInput = document.getElementById('edit-template-new-category');

            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    if (this.value === '_new_') {
                        newCategoryGroup.style.display = 'block';
                        newCategoryInput.required = true;
                        newCategoryInput.focus();
                    } else {
                        newCategoryGroup.style.display = 'none';
                        newCategoryInput.required = false;
                        newCategoryInput.value = '';
                    }
                });
            }

            // Save template details
            const saveDetailsBtn = document.getElementById('save-template-details-btn');
            if (saveDetailsBtn) {
                saveDetailsBtn.addEventListener('click', async function() {
                    const name = document.getElementById('edit-template-name').value.trim();
                    const description = document.getElementById('edit-template-description').value.trim();
                    let category = document.getElementById('edit-template-category').value;

                    // Check if creating new category
                    if (category === '_new_') {
                        const newCategory = newCategoryInput.value.trim();
                        if (!newCategory) {
                            Toast.error('Please enter a category name');
                            newCategoryInput.focus();
                            return;
                        }
                        category = newCategory.toLowerCase().replace(/\s+/g, '-');
                    }

                    if (!name) {
                        Toast.error('Template name is required');
                        return;
                    }

                    const originalBtnContent = saveDetailsBtn.innerHTML;
                    saveDetailsBtn.disabled = true;
                    saveDetailsBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                    try {
                        const result = await Ajax.post('update_template', {
                            id: templateId,
                            name: name,
                            description: description,
                            category: category
                        });

                        if (result.success) {
                            Toast.success('Template details updated');

                            // Update the page header title
                            document.querySelector('.page-header-left h1').textContent = name;

                            // Close modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('edit-template-details-modal'));
                            modal.hide();
                        } else {
                            Toast.error(result.message || 'Failed to update template details');
                        }
                    } catch (error) {
                        Toast.error('Failed to update template details');
                    } finally {
                        saveDetailsBtn.disabled = false;
                        saveDetailsBtn.innerHTML = originalBtnContent;
                    }
                });
            }
        });
    </script>
</body>

</html>