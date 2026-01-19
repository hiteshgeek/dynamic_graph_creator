/**
 * Dashboard List Page - Delete functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    var deleteModalElement = document.getElementById('delete-modal');
    if (!deleteModalElement) return;

    var deleteModal = new bootstrap.Modal(deleteModalElement);
    var dashboardIdToDelete = null;

    // Delete dashboard buttons
    document.querySelectorAll('.delete-dashboard-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            dashboardIdToDelete = this.dataset.id;
            deleteModalElement.querySelector('.dashboard-name').textContent = this.dataset.name;
            deleteModal.show();
        });
    });

    // Confirm delete
    var confirmBtn = document.querySelector('.confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (dashboardIdToDelete) {
                Loading.show('Deleting dashboard...');
                Ajax.post('delete_dashboard', { id: dashboardIdToDelete }).then(function(result) {
                    Loading.hide();
                    deleteModal.hide();
                    if (result.success) {
                        Toast.success('Dashboard deleted');
                        location.reload();
                    } else {
                        Toast.error(result.message || 'Failed to delete dashboard');
                    }
                }).catch(function() {
                    Loading.hide();
                    deleteModal.hide();
                    Toast.error('Failed to delete dashboard');
                });
            }
        });
    }
});
