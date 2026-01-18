/**
 * Template Editor Page
 * Handles form validation and submission for template create/edit
 */
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
