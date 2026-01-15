<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Template: <?php echo htmlspecialchars($template->getName() ?? 'Template'); ?> - Dynamic Graph Creator</title>

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

<body class="template-builder-page">
    <div class="page-header">
        <div class="page-header-left">
            <a href="?urlq=dashboard/templates" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1><?php echo htmlspecialchars($template->getName() ?? 'Template'); ?></h1>
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
            <a href="?urlq=dashboard/template/preview/<?php echo $template->getId(); ?>"
                class="btn btn-primary"
                title="Preview Template">
                <i class="fas fa-eye"></i> Preview Template
            </a>
        </div>
    </div>

    <div id="dashboard-builder"
        class="dashboard-builder"
        data-mode="template"
        data-template-id="<?php echo $template->getId(); ?>"
        data-is-system="<?php echo $template->getIsSystem() ? '1' : '0'; ?>">

        <div class="builder-body">
            <div class="builder-main">
                <div class="grid-editor">
                    <div class="dashboard-sections">
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
                            value="<?php echo htmlspecialchars($template->getName() ?? ''); ?>"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-template-description" class="form-label">Description</label>
                        <textarea class="form-control"
                            id="edit-template-description"
                            rows="3"><?php echo htmlspecialchars($template->getDescription() ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-template-category" class="form-label">Category</label>
                        <select class="form-select" id="edit-template-category">
                            <option value="">None (Uncategorized)</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category->getId(); ?>"
                                    <?php echo ($template->getLtcid() == $category->getId()) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category->getName()); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
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
    <?php if ($js = Utility::getJs('dashboard')): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <script>
        // Initialize template builder when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('dashboard-builder');
            const templateId = parseInt(container.dataset.templateId);
            const isSystem = container.dataset.isSystem === '1';

            // Initialize layout builder in template mode
            if (typeof DashboardBuilder !== 'undefined') {
                window.dashboardBuilderInstance = new DashboardBuilder(container, {
                    mode: 'template',
                    templateId: templateId,
                    isReadOnly: isSystem
                });
                window.dashboardBuilderInstance.init();
            } else {
                console.error('DashboardBuilder not loaded. Make sure dashboard.js is included.');
            }

            // Edit template details button
            const editDetailsBtn = document.getElementById('edit-template-details-btn');
            if (editDetailsBtn) {
                editDetailsBtn.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(document.getElementById('edit-template-details-modal'));
                    modal.show();
                });
            }

            // Save template details
            const saveDetailsBtn = document.getElementById('save-template-details-btn');
            if (saveDetailsBtn) {
                saveDetailsBtn.addEventListener('click', async function() {
                    const name = document.getElementById('edit-template-name').value.trim();
                    const description = document.getElementById('edit-template-description').value.trim();
                    const ltcid = document.getElementById('edit-template-category').value;

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
                            ltcid: ltcid
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