<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $dashboard ? 'Edit Dashboard' : 'Create Dashboard'; ?> - Dynamic Graph Creator</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Google Sans Font -->
    <link href="https://fonts.googleapis.com/css2?family=Product+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <?php if ($css = Utility::getCss('common')): ?>
        <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
    <?php if ($css = Utility::getCss('dashboard')): ?>
        <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>

<body class="dashboard-builder-page">
    <?php
    $rightContent = '';
    if ($dashboard && $dashboard->getId()) {
        $rightContent .= '<div class="save-indicator saved" style="display: flex;"><i class="fas fa-check-circle"></i><span>Saved</span></div>';
        $rightContent .= '<a href="?urlq=dashboard/preview/' . $dashboard->getId() . '" class="btn btn-primary btn-sm btn-view-mode"><i class="fas fa-eye"></i> View Mode</a>';
        $rightContent .= '<div class="form-check form-switch text-switch text-switch-purple"><input class="form-check-input" type="checkbox" role="switch" id="toggle-layout-edit-switch"><div class="text-switch-track"><span class="text-switch-knob"></span><span class="text-switch-label label-text">Tweak</span></div></div>';
    } else {
        $rightContent .= '<div class="save-indicator" style="display: none;"><i class="fas fa-check-circle"></i><span>Saved</span></div>';
    }

    echo Utility::renderPageHeader([
        'title' => ($dashboard && $dashboard->getId()) ? $dashboard->getName() : 'Create Dashboard',
        'backUrl' => '?urlq=dashboard',
        'backLabel' => 'Dashboards',
        'titleEditable' => ($dashboard && $dashboard->getId()) ? true : false,
        'titleId' => 'dashboard-name-display',
        'rightContent' => $rightContent
    ]);
    ?>

    <div id="dashboard-builder"
        class="dashboard-builder"
        data-dashboard-id="<?php echo $dashboard ? $dashboard->getId() : ''; ?>"
        data-breakpoint="desktop">

        <div class="builder-body">
            <div class="builder-main">
                <div class="grid-editor">
                    <?php if ($dashboard && $dashboard->getId()): ?>
                        <div class="dashboard-sections"></div>
                    <?php else: ?>
                        <?php echo Utility::renderEmptyState(
                            'fa-th-large',
                            'Create Your Dashboard',
                            'Choose from our pre-designed templates to get started',
                            'Choose Template',
                            '#',
                            'blue',
                            'choose-template-btn'
                        ); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Selector Modal -->
    <div id="template-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-wrapper">
                        <h5 class="modal-title">Create New Dashboard</h5>
                        <span class="modal-subtitle text-muted">Choose a Layout</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="dashboard-name-input mb-4">
                        <label for="new-dashboard-name" class="form-label">Dashboard Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="new-dashboard-name" placeholder="Enter dashboard name" required>
                        <div class="invalid-feedback">Please enter a dashboard name</div>
                    </div>
                    <hr class="mb-4">
                    <div id="template-list">
                        <div class="loader">
                            <i class="fas fa-spinner fa-spin loader-spinner"></i>
                            <span class="loader-text">Loading templates...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div id="add-section-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-wrapper">
                        <h5 class="modal-title">Add New Section</h5>
                        <span class="modal-subtitle text-muted">Choose empty columns or select from templates</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Empty Columns Option - buttons generated dynamically by JS based on GRID_CONFIG.MAX_COLUMNS -->
                    <div class="add-section-empty-columns mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <label class="form-label mb-0 fw-semibold">Empty Columns:</label>
                            <div class="d-flex gap-2">
                                <!-- Buttons will be generated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    <hr class="mb-4">
                    <!-- Template List -->
                    <div id="add-section-template-list">
                        <div class="loader">
                            <i class="fas fa-spinner fa-spin loader-spinner"></i>
                            <span class="loader-text">Loading templates...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = Utility::getJs('common')): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
    <script src="system/scripts/src/Theme.js"></script>
    <?php if ($js = Utility::getJs('dashboard')): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <script>
        // Initialize dashboard builder when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('dashboard-builder');
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
    </script>
</body>

</html>