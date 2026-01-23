
<?php
// Operations
$rightContent = '';
if (!empty($graphs)) {
    $rightContent .= '<a href="?urlq=graph/create" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create Graph"><i class="fas fa-plus"></i></a>';
}
// Navigation links
$rightContent .= '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Home"><i class="fas fa-home"></i></a>';
if (DGCHelper::hasAdminAccess()) {
    $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
    $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
}
echo DGCHelper::renderPageHeader([
    'title' => 'Graphs',
    'rightContent' => $rightContent
]);
?>

<div class="container">
    <div id="graph-list" class="graph-list-page">
        <?php if (empty($graphs)): ?>
        <?php echo DGCHelper::renderEmptyState(
            'fa-chart-bar',
            'No Graphs Yet',
            'Create your first graph to visualize your data',
            'Create Graph',
            '?urlq=graph/create',
            'orange'
        ); ?>
        <?php else: ?>
        <div class="item-card-grid">
            <?php foreach ($graphs as $g): ?>
            <div class="item-card" data-graph-id="<?php echo $g->getId(); ?>">
                <div class="item-card-content">
                    <div class="item-card-header">
                        <span class="graph-type-badge <?php echo $g->getGraphType(); ?>">
                            <i class="fas fa-chart-<?php echo $g->getGraphType(); ?>"></i>
                            <?php echo ucfirst($g->getGraphType()); ?>
                        </span>
                        <?php if (!empty($graphCategories[$g->getId()])): ?>
                            <?php echo DGCHelper::renderWidgetCategoryBadges($graphCategories[$g->getId()], 'md', true); ?>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($g->getName()); ?></h3>
                    <?php if ($g->getDescription()): ?>
                    <p class="item-card-description"><?php echo htmlspecialchars($g->getDescription()); ?></p>
                    <?php endif; ?>
                </div>
                <div class="item-card-actions">
                    <a href="?urlq=graph/view/<?php echo $g->getId(); ?>" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" title="View Mode">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="?urlq=graph/edit/<?php echo $g->getId(); ?>" class="btn btn-icon btn-outline-design" data-bs-toggle="tooltip" title="Design Mode">
                        <i class="fas fa-paint-brush"></i>
                    </a>
                    <button type="button" class="btn btn-icon btn-outline-danger delete-graph-btn"
                            data-id="<?php echo $g->getId(); ?>"
                            data-name="<?php echo htmlspecialchars($g->getName()); ?>"
                            data-bs-toggle="tooltip"
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Graph</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the graph "<span class="graph-name"></span>"?</p>
                <p class="text-muted"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>
</div>
