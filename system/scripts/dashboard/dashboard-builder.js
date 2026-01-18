/**
 * Dashboard Builder Page
 * Handles initialization and name editing functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add page-specific body class for CSS targeting
    document.body.classList.add('dashboard-builder-page');

    const container = document.getElementById('dashboard-builder');
    if (!container) return;

    const dashboardId = container.dataset.dashboardId || null;

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

    // Handle dashboard name editing
    const nameDisplay = document.getElementById('dashboard-name-display');
    const nameInput = document.getElementById('dashboard-name-input');
    const editBtn = document.getElementById('edit-name-btn');
    const saveBtn = document.getElementById('save-name-btn');
    const cancelBtn = document.getElementById('cancel-name-btn');

    if (nameDisplay && nameInput && editBtn && saveBtn && cancelBtn && dashboardId) {
        // Edit button - show input, hide display
        editBtn.addEventListener('click', function() {
            nameDisplay.style.display = 'none';
            nameInput.style.display = 'block';
            nameInput.focus();
            nameInput.select();
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-flex';
            cancelBtn.style.display = 'inline-flex';
        });

        // Cancel button - restore display, hide input
        cancelBtn.addEventListener('click', function() {
            nameInput.value = nameInput.defaultValue;
            nameInput.style.display = 'none';
            nameDisplay.style.display = 'block';
            editBtn.style.display = 'inline-flex';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        });

        // Save button - update name
        saveBtn.addEventListener('click', function() {
            const newName = nameInput.value.trim();
            if (!newName) {
                Toast.error('Dashboard name cannot be empty');
                return;
            }

            // Show saving state
            saveBtn.disabled = true;
            cancelBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            Ajax.post('update_dashboard_name', {
                    id: dashboardId,
                    name: newName
                })
                .then(result => {
                    if (result.success) {
                        Toast.success('Dashboard name updated');
                        // Update display text and default value
                        nameDisplay.textContent = newName;
                        nameInput.defaultValue = newName;
                        // Switch back to display mode
                        nameInput.style.display = 'none';
                        nameDisplay.style.display = 'block';
                        editBtn.style.display = 'inline-flex';
                        saveBtn.style.display = 'none';
                        cancelBtn.style.display = 'none';
                        saveBtn.innerHTML = '<i class="fas fa-check"></i>';
                        saveBtn.disabled = false;
                        cancelBtn.disabled = false;
                    } else {
                        Toast.error(result.message || 'Failed to update dashboard name');
                        saveBtn.innerHTML = '<i class="fas fa-check"></i>';
                        saveBtn.disabled = false;
                        cancelBtn.disabled = false;
                    }
                })
                .catch(error => {
                    Toast.error('Failed to update dashboard name');
                    saveBtn.innerHTML = '<i class="fas fa-check"></i>';
                    saveBtn.disabled = false;
                    cancelBtn.disabled = false;
                });
        });

        // Handle Enter key to save
        nameInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveBtn.click();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                cancelBtn.click();
            }
        });
    }

    // Layout Edit Mode Toggle (Tweak Switch)
    const tweakSwitch = document.getElementById('toggle-layout-edit-switch');
    if (tweakSwitch) {
        // Check localStorage for saved state (default: tweak mode off)
        const tweakEnabled = localStorage.getItem('dashboardTweakEnabled') === 'true';

        // Apply initial state
        tweakSwitch.checked = tweakEnabled;
        if (!tweakEnabled) {
            container.classList.add('layout-edit-disabled');
        }

        tweakSwitch.addEventListener('change', function() {
            if (this.checked) {
                // Enable tweak mode (show layout controls)
                container.classList.remove('layout-edit-disabled');
                localStorage.setItem('dashboardTweakEnabled', 'true');
            } else {
                // Disable tweak mode (hide layout controls)
                container.classList.add('layout-edit-disabled');
                localStorage.setItem('dashboardTweakEnabled', 'false');
            }
        });
    }
});
