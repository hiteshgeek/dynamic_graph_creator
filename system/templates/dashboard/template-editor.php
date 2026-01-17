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

    <!-- Autosize for textarea auto-expand -->
    <script src="https://cdn.jsdelivr.net/npm/autosize@6.0.1/dist/autosize.min.js"></script>

    <!-- Custom CSS -->
    <?php if ($css = Utility::getCss('common')): ?>
        <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
    <?php if ($css = Utility::getCss('dashboard')): ?>
        <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>

<body>
    <?php
    $rightContent = '<button type="submit" form="template-editor-form" class="btn btn-sm btn-outline-primary" id="submit-btn">';
    $rightContent .= '<i class="fas fa-save"></i> ' . ($template ? 'Save' : 'Create Template') . '</button>';
    echo Utility::renderPageHeader([
        'title' => $pageTitle,
        'backUrl' => '?urlq=dashboard/templates',
        'backLabel' => 'Templates',
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-0">
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
                                <select class="form-select select-with-create" id="template-category" name="dtcid">
                                    <option value="">None (Uncategorized)</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category->getId(); ?>"
                                            <?php echo ($template && $template->getDtcid() == $category->getId()) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category->getName()); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__new__" class="option-create-new">+ Create New Category</option>
                                </select>
                            </div>

                            <!-- New Category Fields (hidden by default) -->
                            <div id="new-category-fields" class="new-category-fields" style="display: none;">
                                <div class="mb-3">
                                    <label for="new-category-name" class="form-label">Category Name *</label>
                                    <input type="text"
                                        class="form-control"
                                        id="new-category-name"
                                        name="new_category_name"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="new-category-description" class="form-label">Category Description</label>
                                    <textarea class="form-control"
                                        id="new-category-description"
                                        name="new_category_description"
                                        rows="2"
                                        placeholder="Enter category description (optional)"></textarea>
                                </div>
                            </div>

                            <?php if ($template && $template->getId()): ?>
                                <input type="hidden" name="id" value="<?php echo $template->getId(); ?>">
                            <?php endif; ?>

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
    <script src="system/scripts/src/Theme.js"></script>
    <?php if ($js = Utility::getJs('dashboard')): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <script>
        // Initialize form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('template-editor-form');
            const submitBtn = document.getElementById('submit-btn');
            const templateId = form.querySelector('input[name="id"]')?.value;
            const action = templateId ? 'update_template' : 'create_template';

            const categorySelect = document.getElementById('template-category');
            const newCategoryFields = document.getElementById('new-category-fields');
            const newCategoryNameInput = document.getElementById('new-category-name');
            const newCategoryDescInput = document.getElementById('new-category-description');

            // Initialize autosize for textareas
            const templateDescription = document.getElementById('template-description');
            if (templateDescription) autosize(templateDescription);
            if (newCategoryDescInput) autosize(newCategoryDescInput);

            // Toggle new category fields visibility
            categorySelect.addEventListener('change', function() {
                const isNewCategory = this.value === '__new__';
                newCategoryFields.style.display = isNewCategory ? 'block' : 'none';

                // Clear new category fields and errors when switching away
                if (!isNewCategory) {
                    newCategoryNameInput.value = '';
                    newCategoryDescInput.value = '';
                    newCategoryNameInput.classList.remove('is-invalid');
                    const feedback = newCategoryNameInput.parentElement.querySelector('.invalid-feedback');
                    if (feedback) feedback.textContent = '';
                }
            });

            // Custom validation for new category name (only when creating new category)
            const validateNewCategoryName = () => {
                if (categorySelect.value !== '__new__') {
                    return true; // Skip validation if not creating new category
                }
                const value = newCategoryNameInput.value.trim();
                if (!value) {
                    return 'Category name is required';
                }
                if (value.length < 2) {
                    return 'Category name must be at least 2 characters';
                }
                return true;
            };

            const validator = new FormValidator(form, {
                rules: {
                    name: {
                        required: true,
                        minLength: 2,
                        maxLength: 255
                    },
                    new_category_name: {
                        custom: validateNewCategoryName
                    }
                },
                messages: {
                    name: {
                        required: 'Template name is required',
                        minLength: 'Template name must be at least 2 characters'
                    }
                },
                toastMessage: 'Please correct the errors in the form',
                onSubmit: async (data) => {
                    // Additional validation for new category
                    if (categorySelect.value === '__new__') {
                        const catNameResult = validateNewCategoryName();
                        if (catNameResult !== true) {
                            newCategoryNameInput.classList.add('is-invalid');
                            let feedback = newCategoryNameInput.parentElement.querySelector('.invalid-feedback');
                            if (!feedback) {
                                feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                newCategoryNameInput.parentElement.appendChild(feedback);
                            }
                            feedback.textContent = catNameResult;
                            newCategoryNameInput.focus();
                            Toast.error('Please correct the errors in the form');
                            return;
                        }
                    }

                    // Show loading state
                    const originalBtnContent = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

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
                                    window.location.href = '?urlq=dashboard/templates';
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
                }
            });

            // Live validation for new category name field
            newCategoryNameInput.addEventListener('blur', function() {
                if (categorySelect.value === '__new__') {
                    const result = validateNewCategoryName();
                    if (result !== true) {
                        this.classList.add('is-invalid');
                        let feedback = this.parentElement.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            this.parentElement.appendChild(feedback);
                        }
                        feedback.textContent = result;
                    } else {
                        this.classList.remove('is-invalid');
                    }
                }
            });

            newCategoryNameInput.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    const result = validateNewCategoryName();
                    if (result === true) {
                        this.classList.remove('is-invalid');
                    }
                }
            });
        });
    </script>
</body>

</html>