/**
 * Dashboard Builder Page
 * Handles initialization and dashboard details editing
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add page-specific body class for CSS targeting
    document.body.classList.add('dashboard-builder-page');

    const container = document.getElementById('dashboard-builder');
    if (!container) return;

    const dashboardId = container.dataset.dashboardId || null;

    // Initialize autosize for description textareas
    const newDashboardDescription = document.getElementById('new-dashboard-description');
    const editDashboardDescription = document.getElementById('edit-dashboard-description');
    if (newDashboardDescription) autosize(newDashboardDescription);
    if (editDashboardDescription) autosize(editDashboardDescription);

    // Initialize Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    // Initialize dashboard builder
    if (typeof DashboardBuilder !== 'undefined') {
        window.dashboardBuilderInstance = new DashboardBuilder(container, {
            dashboardId: dashboardId ? parseInt(dashboardId) : null,
            mode: 'edit'
        });
        window.dashboardBuilderInstance.init();
    } else {
        console.error('DashboardBuilder not loaded. Make sure dashboard.js is included.');
    }

    // Handle dashboard details editing via modal
    const editDetailsBtn = document.getElementById('edit-dashboard-details-btn');
    const editDetailsModal = document.getElementById('edit-dashboard-details-modal');
    const saveDetailsBtn = document.getElementById('save-dashboard-details-btn');
    const nameDisplay = document.getElementById('dashboard-name-display');

    if (editDetailsBtn && editDetailsModal && dashboardId) {
        const modal = new bootstrap.Modal(editDetailsModal);
        const nameInput = document.getElementById('edit-dashboard-name');
        const descriptionInput = document.getElementById('edit-dashboard-description');

        // Open modal on edit button click
        editDetailsBtn.addEventListener('click', function() {
            modal.show();
        });

        // Focus on name input when modal is fully shown
        editDetailsModal.addEventListener('shown.bs.modal', function() {
            if (nameInput) {
                nameInput.focus();
                nameInput.select();
            }
        });

        // Handle Enter key in name input
        if (nameInput) {
            nameInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveDetailsBtn.click();
                }
            });
        }

        // Save details
        if (saveDetailsBtn) {
            saveDetailsBtn.addEventListener('click', function() {
                const newName = nameInput.value.trim();
                const newDescription = descriptionInput ? descriptionInput.value.trim() : '';

                if (!newName) {
                    nameInput.classList.add('is-invalid');
                    Toast.error('Dashboard name cannot be empty');
                    return;
                }

                nameInput.classList.remove('is-invalid');

                // Show saving state
                const originalBtnContent = saveDetailsBtn.innerHTML;
                saveDetailsBtn.disabled = true;
                saveDetailsBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                Ajax.post('update_dashboard_details', {
                        id: dashboardId,
                        name: newName,
                        description: newDescription
                    })
                    .then(result => {
                        if (result.success) {
                            Toast.success('Dashboard details updated');
                            // Update page title
                            if (nameDisplay) {
                                nameDisplay.textContent = newName;
                            }
                            document.title = newName + ' - Edit Dashboard';

                            // Update description tooltip (convert newlines to <br> for multiline support)
                            const tooltipEl = document.querySelector('.description-tooltip');
                            const descriptionHtml = newDescription.replace(/\n/g, '<br>');
                            if (newDescription) {
                                if (tooltipEl) {
                                    // Update existing tooltip
                                    const tooltip = bootstrap.Tooltip.getInstance(tooltipEl);
                                    if (tooltip) {
                                        tooltip.setContent({ '.tooltip-inner': descriptionHtml });
                                    }
                                } else {
                                    // Create new tooltip element with HTML support
                                    const editor = document.querySelector('.dashboard-name-editor');
                                    const newTooltip = document.createElement('span');
                                    newTooltip.className = 'description-tooltip';
                                    newTooltip.setAttribute('data-bs-toggle', 'tooltip');
                                    newTooltip.setAttribute('data-bs-placement', 'bottom');
                                    newTooltip.setAttribute('data-bs-html', 'true');
                                    newTooltip.setAttribute('title', descriptionHtml);
                                    newTooltip.innerHTML = '<i class="fas fa-info-circle"></i>';
                                    editor.insertBefore(newTooltip, editor.querySelector('#edit-dashboard-details-btn'));
                                    new bootstrap.Tooltip(newTooltip);
                                }
                            } else if (tooltipEl) {
                                // Remove tooltip if description is empty
                                const tooltip = bootstrap.Tooltip.getInstance(tooltipEl);
                                if (tooltip) tooltip.dispose();
                                tooltipEl.remove();
                            }

                            modal.hide();
                        } else {
                            Toast.error(result.message || 'Failed to update dashboard details');
                        }
                    })
                    .catch(error => {
                        Toast.error('Failed to update dashboard details');
                    })
                    .finally(() => {
                        saveDetailsBtn.disabled = false;
                        saveDetailsBtn.innerHTML = originalBtnContent;
                    });
            });
        }
    }

    // Layout Edit Mode Toggle (Tweak Switch)
    // Initial state is set by inline script in template to prevent flash
    const tweakSwitch = document.getElementById('toggle-layout-edit-switch');
    if (tweakSwitch) {
        tweakSwitch.addEventListener('change', function() {
            if (this.checked) {
                // Enable tweak mode (show layout controls)
                container.classList.remove('layout-edit-disabled');
                localStorage.setItem('dashboardTweakEnabled', 'true');
                document.cookie = 'dashboardTweakEnabled=true;path=/;max-age=31536000';
            } else {
                // Disable tweak mode (hide layout controls)
                container.classList.add('layout-edit-disabled');
                localStorage.setItem('dashboardTweakEnabled', 'false');
                document.cookie = 'dashboardTweakEnabled=false;path=/;max-age=31536000';
            }
        });
    }
});
