<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Dynamic Graph Creator</title>

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
            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <form id="template-editor-form">
                            <div class="mb-3">
                                <label for="template-name" class="form-label">Template Name *</label>
                                <input type="text"
                                       class="form-control"
                                       id="template-name"
                                       name="name"
                                       placeholder="Enter template name"
                                       value="<?php echo $template ? htmlspecialchars($template->getName()) : ''; ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="template-description" class="form-label">Description</label>
                                <textarea class="form-control"
                                          id="template-description"
                                          name="description"
                                          rows="3"
                                          placeholder="Enter template description (optional)"><?php echo $template ? htmlspecialchars($template->getDescription()) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="template-category" class="form-label">Category</label>
                                <select class="form-select" id="template-category" name="ltcid">
                                    <option value="">None (Uncategorized)</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category->getId(); ?>"
                                            <?php echo ($template && $template->getLtcid() == $category->getId()) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category->getName()); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if ($template && $template->getId()): ?>
                            <input type="hidden" name="id" value="<?php echo $template->getId(); ?>">
                            <?php endif; ?>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="?urlq=layout/templates" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-check"></i> <?php echo $template && $template->getId() ? 'Update' : 'Create & Edit Structure'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
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
        // Handle form submission
        document.getElementById('template-editor-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = document.getElementById('submit-btn');
            const templateId = form.querySelector('input[name="id"]')?.value;
            const action = templateId ? 'update_template' : 'create_template';

            // Get form data
            const formData = new FormData(form);
            const data = {
                submit: action
            };
            for (const [key, value] of formData.entries()) {
                data[key] = value;
            }

            // Show loading state
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (templateId ? 'Updating...' : 'Creating...');

            try {
                const result = await Ajax.post(action, data);

                if (result.success) {
                    Toast.success(result.message || (templateId ? 'Template updated' : 'Template created'));

                    // Redirect based on action
                    if (result.data && result.data.redirect) {
                        setTimeout(() => {
                            window.location.href = result.data.redirect;
                        }, 500);
                    } else if (templateId) {
                        // If updating, redirect back to templates
                        setTimeout(() => {
                            window.location.href = '?urlq=layout/templates';
                        }, 500);
                    }
                } else {
                    Toast.error(result.message || 'Failed to save template');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                }
            } catch (error) {
                Toast.error('Failed to save template');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
            }
        });
    </script>
</body>
</html>
