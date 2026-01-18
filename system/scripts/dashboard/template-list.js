/**
 * Template List Page - Delete functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Template delete modal
    var deleteTemplateModalElement = document.getElementById('delete-template-modal');
    if (deleteTemplateModalElement) {
        var deleteTemplateModal = new bootstrap.Modal(deleteTemplateModalElement);
        var templateIdToDelete = null;

        document.querySelectorAll('.delete-template-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                templateIdToDelete = this.dataset.id;
                deleteTemplateModalElement.querySelector('.template-name').textContent = this.dataset.name;
                deleteTemplateModal.show();
            });
        });

        var confirmTemplateDeleteBtn = document.querySelector('.confirm-template-delete-btn');
        if (confirmTemplateDeleteBtn) {
            confirmTemplateDeleteBtn.addEventListener('click', function() {
                if (templateIdToDelete) {
                    Loading.show('Deleting template...');
                    Ajax.post('delete_template', { id: templateIdToDelete }).then(function(result) {
                        Loading.hide();
                        deleteTemplateModal.hide();
                        if (result.success) {
                            Toast.success('Template deleted');
                            location.reload();
                        } else {
                            Toast.error(result.message || 'Failed to delete template');
                        }
                    }).catch(function() {
                        Loading.hide();
                        deleteTemplateModal.hide();
                        Toast.error('Failed to delete template');
                    });
                }
            });
        }
    }

    // Category delete modal
    var deleteCategoryModalElement = document.getElementById('delete-category-modal');
    if (deleteCategoryModalElement) {
        var deleteCategoryModal = new bootstrap.Modal(deleteCategoryModalElement);
        var categoryIdToDelete = null;

        document.querySelectorAll('.delete-category-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                categoryIdToDelete = this.dataset.id;
                deleteCategoryModalElement.querySelector('.category-name').textContent = this.dataset.name;
                deleteCategoryModal.show();
            });
        });

        var confirmCategoryDeleteBtn = document.querySelector('.confirm-category-delete-btn');
        if (confirmCategoryDeleteBtn) {
            confirmCategoryDeleteBtn.addEventListener('click', function() {
                if (categoryIdToDelete) {
                    Loading.show('Deleting category...');
                    Ajax.post('delete_category', { id: categoryIdToDelete }).then(function(result) {
                        Loading.hide();
                        deleteCategoryModal.hide();
                        if (result.success) {
                            Toast.success('Category deleted');
                            location.reload();
                        } else {
                            Toast.error(result.message || 'Failed to delete category');
                        }
                    }).catch(function() {
                        Loading.hide();
                        deleteCategoryModal.hide();
                        Toast.error('Failed to delete category');
                    });
                }
            });
        }
    }
});
