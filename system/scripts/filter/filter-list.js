/**
 * Filter List Page - Delete functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    var deleteModalElement = document.getElementById('delete-modal');
    if (!deleteModalElement) return;

    var deleteModal = new bootstrap.Modal(deleteModalElement);
    var filterIdToDelete = null;

    // Delete filter buttons
    document.querySelectorAll('.delete-filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterIdToDelete = this.dataset.id;
            deleteModalElement.querySelector('.filter-name').textContent = this.dataset.name;
            deleteModal.show();
        });
    });

    // Confirm delete
    var confirmBtn = document.querySelector('.confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (filterIdToDelete) {
                Loading.show('Deleting filter...');
                Ajax.post('delete_filter', { id: filterIdToDelete }).then(function(result) {
                    Loading.hide();
                    deleteModal.hide();
                    if (result.success) {
                        Toast.success('Filter deleted');
                        location.reload();
                    } else {
                        Toast.error(result.message || 'Failed to delete filter');
                    }
                }).catch(function() {
                    Loading.hide();
                    deleteModal.hide();
                    Toast.error('Failed to delete filter');
                });
            }
        });
    }
});
