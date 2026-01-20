
<?php
// Set company start date for datepicker presets
echo DGCHelper::renderCompanyStartDateScript();

$rightContent = '';

// Add admin links (Graphs, Filters) if user has permission
if (DGCHelper::hasAdminAccess()) {
    $rightContent .= '<a href="?urlq=graph" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Graphs"><i class="fas fa-chart-bar"></i></a>';
    $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
}

if ($dashboard && $dashboard->getId()) {
    $rightContent .= '<div class="save-indicator saved" style="display: flex;"><i class="fas fa-check-circle"></i><span>Saved</span></div>';
    $rightContent .= '<a href="?urlq=dashboard/preview/' . $dashboard->getId() . '" class="btn btn-icon btn-outline-primary btn-view-mode" data-bs-toggle="tooltip" data-bs-placement="bottom" title="View Mode"><i class="fas fa-eye"></i></a>';
    $rightContent .= '<div class="form-check form-switch text-switch text-switch-purple"><input class="form-check-input" type="checkbox" role="switch" id="toggle-layout-edit-switch"><div class="text-switch-track"><span class="text-switch-knob"></span><span class="text-switch-label label-text">Tweak</span></div></div>';
} else {
    $rightContent .= '<div class="save-indicator" style="display: none;"><i class="fas fa-check-circle"></i><span>Saved</span></div>';
}

echo DGCHelper::renderPageHeader([
    'title' => ($dashboard && $dashboard->getId()) ? $dashboard->getName() : 'Create Dashboard',
    'backUrl' => '?urlq=dashboard',
    'backLabel' => 'Dashboards',
    'titleEditable' => ($dashboard && $dashboard->getId()) ? true : false,
    'titleId' => 'dashboard-name-display',
    'titleDescription' => ($dashboard && $dashboard->getId()) ? $dashboard->getDescription() : '',
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
                    <?php echo DGCHelper::renderEmptyState(
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
                <div class="dashboard-name-input mb-3">
                    <label for="new-dashboard-name" class="form-label">Dashboard Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="new-dashboard-name" placeholder="Enter dashboard name" required>
                    <div class="invalid-feedback">Please enter a dashboard name</div>
                </div>
                <div class="dashboard-description-input mb-4">
                    <label for="new-dashboard-description" class="form-label">Description</label>
                    <textarea class="form-control" id="new-dashboard-description" rows="1" placeholder="Enter dashboard description (optional)"></textarea>
                </div>
                <hr class="dashboard-form-divider mb-4">
                <div id="template-list">
                    <div class="loader">
                        <div class="spinner"></div>
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
                        <div class="spinner"></div>
                        <span class="loader-text">Loading templates...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($dashboard && $dashboard->getId()): ?>
    <!-- Edit Dashboard Details Modal -->
    <div id="edit-dashboard-details-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Dashboard Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-dashboard-name" class="form-label">Dashboard Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-dashboard-name" value="<?php echo htmlspecialchars($dashboard->getName()); ?>" required>
                        <div class="invalid-feedback">Please enter a dashboard name</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-dashboard-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-dashboard-description" rows="1" placeholder="Enter dashboard description (optional)"><?php echo htmlspecialchars($dashboard->getDescription()); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-outline-warning" id="save-dashboard-details-btn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?></div>
