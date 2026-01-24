
<?php
// Operations
$rightContent = '';
if (!empty($tables)) {
    $rightContent .= '<a href="?urlq=widget-table/create" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create Table"><i class="fas fa-plus"></i></a>';
}
// Navigation links
$rightContent .= '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Home"><i class="fas fa-home"></i></a>';
if (DGCHelper::hasAdminAccess()) {
    $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
    $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
}
// Widget dropdown
$leftContent = DGCHelper::renderWidgetDropdown('table');
echo DGCHelper::renderPageHeader([
    'title' => 'Tables',
    'leftContent' => $leftContent,
    'rightContent' => $rightContent
]);
?>

<div class="container">
    <div id="table-list" class="table-list-page">
        <?php if (empty($tables)): ?>
        <?php echo DGCHelper::renderEmptyState(
            'fa-table',
            'No Tables Yet',
            'Create your first table to display data in a structured format',
            'Create Table',
            '?urlq=widget-table/create',
            'purple'
        ); ?>
        <?php else: ?>
        <div class="item-card-grid">
            <?php foreach ($tables as $t): ?>
            <?php
            $config = $t->getConfigArray();
            $defaultConfig = Table::getDefaultConfig();
            $style = isset($config['style']) ? $config['style'] : $defaultConfig['style'];
            ?>
            <div class="item-card table-card" data-table-id="<?php echo $t->getId(); ?>">
                <div class="item-card-content">
                    <div class="item-card-header">
                        <span class="table-type-badge">
                            <i class="fas fa-table"></i>
                            Table
                        </span>
                        <?php if (!empty($tableCategories[$t->getId()])): ?>
                            <?php echo DGCHelper::renderWidgetCategoryBadges($tableCategories[$t->getId()], 'md', true); ?>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($t->getName()); ?></h3>
                    <?php if ($t->getDescription()): ?>
                    <p class="item-card-description"><?php echo htmlspecialchars($t->getDescription()); ?></p>
                    <?php endif; ?>
                </div>
                <div class="item-card-actions">
                    <a href="?urlq=widget-table/view/<?php echo $t->getId(); ?>" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" title="View Mode">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="?urlq=widget-table/edit/<?php echo $t->getId(); ?>" class="btn btn-icon btn-outline-design" data-bs-toggle="tooltip" title="Design Mode">
                        <i class="fas fa-paint-brush"></i>
                    </a>
                    <button type="button" class="btn btn-icon btn-outline-danger delete-table-btn"
                            data-id="<?php echo $t->getId(); ?>"
                            data-name="<?php echo htmlspecialchars($t->getName()); ?>"
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
                <h5 class="modal-title">Delete Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the table "<span class="table-name"></span>"?</p>
                <p class="text-muted"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>
</div>
