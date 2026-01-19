/**
 * Dashboard Preview Page - Delete functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add page-specific body class for CSS targeting
    document.body.classList.add('dashboard-preview-page');

    // Handle delete dashboard using ConfirmDialog
    const deleteBtn = document.querySelector('.delete-dashboard-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async function() {
            const dashboardId = this.dataset.dashboardId;

            const confirmed = await ConfirmDialog.delete('Are you sure you want to delete this dashboard?', 'Confirm Delete');
            if (!confirmed) return;

            // Show loading state
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

            Ajax.post('delete_dashboard', {
                    id: dashboardId
                })
                .then(result => {
                    if (result.success) {
                        Toast.success('Dashboard deleted successfully');
                        // Redirect to dashboard list after a short delay
                        setTimeout(() => {
                            window.location.href = '?urlq=dashboard';
                        }, 500);
                    } else {
                        Toast.error(result.message || 'Failed to delete dashboard');
                        // Restore button state on error
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete Dashboard';
                    }
                })
                .catch(error => {
                    Toast.error('Failed to delete dashboard');
                    // Restore button state on error
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete Dashboard';
                });
        });
    }
});
