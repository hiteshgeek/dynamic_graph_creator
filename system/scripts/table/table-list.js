/**
 * Table List Page - Delete functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    var deleteModalElement = document.getElementById('delete-modal');
    if (!deleteModalElement) return;

    var deleteModal = new bootstrap.Modal(deleteModalElement);
    var tableIdToDelete = null;

    // Delete table buttons
    document.querySelectorAll('.delete-table-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            tableIdToDelete = this.dataset.id;
            deleteModalElement.querySelector('.table-name').textContent = this.dataset.name;
            deleteModal.show();
        });
    });

    // Confirm delete
    var confirmBtn = document.querySelector('.confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (tableIdToDelete) {
                Loading.show('Deleting table...');
                Ajax.post('delete_table', { id: tableIdToDelete }).then(function(result) {
                    Loading.hide();
                    deleteModal.hide();
                    if (result.success) {
                        Toast.success('Table deleted');
                        location.reload();
                    } else {
                        Toast.error(result.message || 'Failed to delete table');
                    }
                }).catch(function() {
                    Loading.hide();
                    deleteModal.hide();
                    Toast.error('Failed to delete table');
                });
            }
        });
    }
});
