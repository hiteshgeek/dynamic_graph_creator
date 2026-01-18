/**
 * Graph List Page - Delete functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    var deleteModalElement = document.getElementById('delete-modal');
    if (!deleteModalElement) return;

    var deleteModal = new bootstrap.Modal(deleteModalElement);
    var graphIdToDelete = null;

    // Delete graph buttons
    document.querySelectorAll('.delete-graph-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            graphIdToDelete = this.dataset.id;
            deleteModalElement.querySelector('.graph-name').textContent = this.dataset.name;
            deleteModal.show();
        });
    });

    // Confirm delete
    var confirmBtn = document.querySelector('.confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (graphIdToDelete) {
                Loading.show('Deleting graph...');
                Ajax.post('delete_graph', { id: graphIdToDelete }).then(function(result) {
                    Loading.hide();
                    deleteModal.hide();
                    if (result.success) {
                        Toast.success('Graph deleted');
                        location.reload();
                    } else {
                        Toast.error(result.message || 'Failed to delete graph');
                    }
                }).catch(function() {
                    Loading.hide();
                    deleteModal.hide();
                    Toast.error('Failed to delete graph');
                });
            }
        });
    }
});
