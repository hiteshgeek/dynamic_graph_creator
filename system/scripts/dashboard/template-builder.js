/**
 * Template Builder Page
 * Handles initialization and template details editing
 */
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('dashboard-builder');
    if (!container) return;

    const templateId = parseInt(container.dataset.templateId);
    const isSystem = container.dataset.isSystem === '1';

    // Initialize autosize for textareas
    const editTemplateDescription = document.getElementById('edit-template-description');
    const newCategoryDescription = document.getElementById('new-category-description');
    if (editTemplateDescription) autosize(editTemplateDescription);
    if (newCategoryDescription) autosize(newCategoryDescription);

    // Initialize dashboard builder in template mode
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

    // Layout Edit Mode Toggle (Tweak Switch)
    const tweakSwitch = document.getElementById('toggle-layout-edit-switch');
    if (tweakSwitch && !isSystem) {
        // Check localStorage for saved state (default: tweak mode off)
        const tweakEnabled = localStorage.getItem('templateTweakEnabled') === 'true';

        // Apply initial state
        tweakSwitch.checked = tweakEnabled;
        if (!tweakEnabled) {
            container.classList.add('layout-edit-disabled');
        }

        tweakSwitch.addEventListener('change', function() {
            if (this.checked) {
                // Enable tweak mode (show layout controls)
                container.classList.remove('layout-edit-disabled');
                localStorage.setItem('templateTweakEnabled', 'true');
            } else {
                // Disable tweak mode (hide layout controls)
                container.classList.add('layout-edit-disabled');
                localStorage.setItem('templateTweakEnabled', 'false');
            }
        });
    }

    // Edit template details - category select and new category fields
    const categorySelect = document.getElementById('edit-template-category');
    const newCategoryFields = document.getElementById('new-category-fields');
    const newCategoryNameInput = document.getElementById('new-category-name');
    const newCategoryDescInput = document.getElementById('new-category-description');

    // Toggle new category fields visibility
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const isNewCategory = this.value === '__new__';
            newCategoryFields.style.display = isNewCategory ? 'block' : 'none';

            // Clear new category fields when switching away
            if (!isNewCategory) {
                newCategoryNameInput.value = '';
                newCategoryDescInput.value = '';
                newCategoryNameInput.classList.remove('is-invalid');
            }
        });
    }

    // Live validation for template name
    const editTemplateNameInput = document.getElementById('edit-template-name');
    if (editTemplateNameInput) {
        editTemplateNameInput.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            } else if (this.classList.contains('is-invalid') || this.value === '') {
                this.classList.add('is-invalid');
            }
        });
    }

    // Live validation for new category name
    if (newCategoryNameInput) {
        newCategoryNameInput.addEventListener('input', function() {
            if (categorySelect.value !== '__new__') return;

            const value = this.value.trim();
            const feedback = this.parentElement.querySelector('.invalid-feedback');

            if (!value) {
                this.classList.add('is-invalid');
                if (feedback) feedback.textContent = 'Category name is required';
            } else if (value.length < 2) {
                this.classList.add('is-invalid');
                if (feedback) feedback.textContent = 'Category name must be at least 2 characters';
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    // Edit template details button
    const editDetailsBtn = document.getElementById('edit-template-details-btn');
    if (editDetailsBtn) {
        editDetailsBtn.addEventListener('click', function() {
            // Reset new category fields when opening modal
            if (categorySelect.value !== '__new__') {
                newCategoryFields.style.display = 'none';
                newCategoryNameInput.value = '';
                newCategoryDescInput.value = '';
            }
            const modal = new bootstrap.Modal(document.getElementById('edit-template-details-modal'));
            modal.show();
        });
    }

    // Save template details
    const saveDetailsBtn = document.getElementById('save-template-details-btn');

    // Handle Enter key in modal inputs
    const editTemplateModal = document.getElementById('edit-template-details-modal');
    if (editTemplateModal) {
        editTemplateModal.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                const target = e.target;
                // Don't submit if in textarea (allow newlines)
                if (target.tagName === 'TEXTAREA') return;
                // Don't submit if in select
                if (target.tagName === 'SELECT') return;

                e.preventDefault();
                if (saveDetailsBtn && !saveDetailsBtn.disabled) {
                    saveDetailsBtn.click();
                }
            }
        });
    }

    if (saveDetailsBtn) {
        saveDetailsBtn.addEventListener('click', async function() {
            const nameInput = document.getElementById('edit-template-name');
            const name = nameInput.value.trim();
            const description = document.getElementById('edit-template-description').value.trim();
            const dtcid = document.getElementById('edit-template-category').value;

            // Validate all fields and collect errors
            let hasErrors = false;

            // Validate template name
            if (!name) {
                nameInput.classList.add('is-invalid');
                hasErrors = true;
            } else {
                nameInput.classList.remove('is-invalid');
            }

            // Validate new category name if creating new category
            if (dtcid === '__new__') {
                const newCatName = newCategoryNameInput.value.trim();
                const catFeedback = newCategoryNameInput.parentElement.querySelector('.invalid-feedback');
                if (!newCatName) {
                    newCategoryNameInput.classList.add('is-invalid');
                    if (catFeedback) catFeedback.textContent = 'Category name is required';
                    hasErrors = true;
                } else if (newCatName.length < 2) {
                    newCategoryNameInput.classList.add('is-invalid');
                    if (catFeedback) catFeedback.textContent = 'Category name must be at least 2 characters';
                    hasErrors = true;
                } else {
                    newCategoryNameInput.classList.remove('is-invalid');
                }
            }

            // Show single toast if there are errors
            if (hasErrors) {
                Toast.error('Please correct the errors in the form');
                return;
            }

            const originalBtnContent = saveDetailsBtn.innerHTML;
            saveDetailsBtn.disabled = true;
            saveDetailsBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            try {
                const postData = {
                    id: templateId,
                    name: name,
                    description: description,
                    dtcid: dtcid
                };

                // Add new category data if creating new category
                if (dtcid === '__new__') {
                    postData.new_category_name = newCategoryNameInput.value.trim();
                    postData.new_category_description = newCategoryDescInput.value.trim();
                }

                const result = await Ajax.post('update_template', postData);

                if (result.success) {
                    Toast.success('Template details updated');

                    // Update the page header title
                    document.querySelector('.page-header-left h1').textContent = name;

                    // If a new category was created, add it to the select and select it
                    if (result.data && result.data.new_category_id) {
                        const newOption = document.createElement('option');
                        newOption.value = result.data.new_category_id;
                        newOption.textContent = newCategoryNameInput.value.trim();
                        // Insert before the "Create New" option
                        const createNewOption = categorySelect.querySelector('option[value="__new__"]');
                        categorySelect.insertBefore(newOption, createNewOption);
                        categorySelect.value = result.data.new_category_id;
                        newCategoryFields.style.display = 'none';
                        newCategoryNameInput.value = '';
                        newCategoryDescInput.value = '';
                    }

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
