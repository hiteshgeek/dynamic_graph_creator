<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($layout->getName()); ?> - Preview</title>

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

<body class="dashboard-preview-page">
    <div class="page-header">
        <div class="page-header-left">
            <a href="?urlq=dashboard" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1><?php echo htmlspecialchars($layout->getName()); ?></h1>
        </div>
        <div class="page-header-right">
            <a href="?urlq=dashboard/builder/<?php echo $layout->getId(); ?>" class="btn btn-warning">
                <i class="fas fa-pencil"></i> Edit Dashboard
            </a>
            <button class="btn btn-danger delete-layout-btn" data-layout-id="<?php echo $layout->getId(); ?>">
                <i class="fas fa-trash"></i> Delete Dashboard
            </button>
        </div>
    </div>

    <div class="container-fluid">
        <div id="dashboard-preview" class="dashboard-preview" data-layout-id="<?php echo $layout->getId(); ?>">
            <div class="dashboard-sections">
                <?php
                $structure = $layout->getStructureArray();
                // Debug: Check structure
                // error_log('Layout structure: ' . print_r($structure, true));
                if (isset($structure['sections']) && count($structure['sections']) > 0):
                    foreach ($structure['sections'] as $section):
                ?>
                        <div class="dashboard-section"
                            data-section-id="<?php echo htmlspecialchars($section['sid']); ?>"
                            style="grid-template-columns: <?php echo htmlspecialchars($section['gridTemplate']); ?>;">

                            <?php foreach ($section['areas'] as $area): ?>
                                <?php
                                // Check if area has sub-rows (nested structure)
                                $hasSubRows = isset($area['hasSubRows']) && $area['hasSubRows'] && isset($area['subRows']) && count($area['subRows']) > 0;
                                ?>

                                <?php if ($hasSubRows): ?>
                                    <!-- Area with sub-rows -->
                                    <?php
                                    $rowHeights = array_map(function ($row) {
                                        return isset($row['height']) ? $row['height'] : '1fr';
                                    }, $area['subRows']);
                                    $rowHeightsStr = implode(' ', $rowHeights);
                                    ?>
                                    <div class="dashboard-area dashboard-area-nested"
                                        data-area-id="<?php echo htmlspecialchars($area['aid']); ?>"
                                        style="grid-column: span <?php echo isset($area['colSpan']) ? intval($area['colSpan']) : 1; ?>; grid-template-rows: <?php echo $rowHeightsStr; ?>;">

                                        <?php foreach ($area['subRows'] as $subRow): ?>
                                            <div class="dashboard-sub-row" data-row-id="<?php echo htmlspecialchars($subRow['rowId']); ?>">
                                                <?php if (isset($subRow['content']) && $subRow['content']['type'] === 'empty'): ?>
                                                    <div class="empty-state">
                                                        <div class="empty-state-icon">
                                                            <i class="fas <?php echo isset($subRow['emptyState']['icon']) ? htmlspecialchars($subRow['emptyState']['icon']) : 'fa-plus-circle'; ?>"></i>
                                                        </div>
                                                        <div class="empty-state-message">
                                                            <?php echo isset($subRow['emptyState']['message']) ? htmlspecialchars($subRow['emptyState']['message']) : 'Add content here'; ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="area-content">
                                                        <p>Widget: <?php echo isset($subRow['content']['widgetType']) ? htmlspecialchars($subRow['content']['widgetType']) : 'Unknown'; ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                <?php else: ?>
                                    <!-- Regular single area -->
                                    <div class="dashboard-area"
                                        data-area-id="<?php echo htmlspecialchars($area['aid']); ?>"
                                        style="grid-column: span <?php echo isset($area['colSpan']) ? intval($area['colSpan']) : 1; ?>;">

                                        <?php if (isset($area['content']) && $area['content']['type'] === 'empty'): ?>
                                            <div class="empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="fas <?php echo isset($area['emptyState']['icon']) ? htmlspecialchars($area['emptyState']['icon']) : 'fa-plus-circle'; ?>"></i>
                                                </div>
                                                <div class="empty-state-message">
                                                    <?php echo isset($area['emptyState']['message']) ? htmlspecialchars($area['emptyState']['message']) : 'Add content here'; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="area-content">
                                                <p>Widget: <?php echo isset($area['content']['widgetType']) ? htmlspecialchars($area['content']['widgetType']) : 'Unknown'; ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php
                    endforeach;
                else:
                    ?>
                    <!-- Empty state when no sections -->
                    <div class="dashboard-empty-sections">
                        <div class="empty-sections-content">
                            <i class="fas fa-th-large"></i>
                            <h3>This Dashboard is Empty</h3>
                            <p>No sections have been added to this dashboard yet.<br>Use the "Edit Dashboard" button above to add sections.</p>
                        </div>
                    </div>
                <?php
                endif;
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = Utility::getJs('common')): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
    <?php if ($js = Utility::getJs('dashboard')): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <script>
        // Handle delete layout
        document.querySelector('.delete-layout-btn')?.addEventListener('click', async function() {
            const layoutId = this.dataset.layoutId;

            const confirmed = await ConfirmDialog.delete('Are you sure you want to delete this dashboard?', 'Confirm Delete');
            if (!confirmed) return;

            // Show loading state
            const deleteBtn = this;
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

            Ajax.post('delete_layout', {
                    id: layoutId
                })
                .then(result => {
                    if (result.success) {
                        Toast.success('Dashboard deleted successfully');
                        // Redirect to layout list after a short delay
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
    </script>
</body>

</html>