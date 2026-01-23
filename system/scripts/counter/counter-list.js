/**
 * Counter List Page - Delete functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    var deleteModalElement = document.getElementById('delete-modal');
    if (!deleteModalElement) return;

    var deleteModal = new bootstrap.Modal(deleteModalElement);
    var counterIdToDelete = null;

    // Delete counter buttons
    document.querySelectorAll('.delete-counter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            counterIdToDelete = this.dataset.id;
            deleteModalElement.querySelector('.counter-name').textContent = this.dataset.name;
            deleteModal.show();
        });
    });

    // Confirm delete
    var confirmBtn = document.querySelector('.confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (counterIdToDelete) {
                Loading.show('Deleting counter...');
                Ajax.post('delete_counter', { id: counterIdToDelete }).then(function(result) {
                    Loading.hide();
                    deleteModal.hide();
                    if (result.success) {
                        Toast.success('Counter deleted');
                        location.reload();
                    } else {
                        Toast.error(result.message || 'Failed to delete counter');
                    }
                }).catch(function() {
                    Loading.hide();
                    deleteModal.hide();
                    Toast.error('Failed to delete counter');
                });
            }
        });
    }
});
