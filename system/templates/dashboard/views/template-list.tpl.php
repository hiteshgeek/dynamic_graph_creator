
<?php
require_once __DIR__ . '/../../../includes/dashboard/template-preview-component.php';
?>
<script>
window.__collapsedCategories = JSON.parse(localStorage.getItem('collapsedTemplateCategories') || '[]');
window.__allowTemplateOrdering = <?php echo $allowTemplateOrdering ? 'true' : 'false'; ?>;
</script>
<?php
    $leftContent = '';
    if (!empty($templates)) {
        $leftContent = '<button type="button" class="btn btn-icon btn-outline-secondary" id="toggle-all-categories" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Collapse All"><i class="fas fa-compress-alt"></i></button>';
    }

    $rightContent = '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Dashboards"><i class="fas fa-th-large"></i></a>';
    $rightContent .= '<a href="?urlq=graph" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Graphs"><i class="fas fa-chart-line"></i></a>';
    if (!empty($templates)) {
        $rightContent .= '<a href="?urlq=dashboard/template/create" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create Template"><i class="fas fa-plus"></i></a>';
    }

    echo DGCHelper::renderPageHeader([
        'title' => 'Dashboard Templates',
        'leftContent' => $leftContent,
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="container-fluid">
        <div class="template-list-page">
            <?php if (empty($templates)): ?>
            <?php echo DGCHelper::renderEmptyState(
                'fa-clone',
                'No Templates Yet',
                'Create your first template to get started',
                'Create Template',
                '?urlq=dashboard/template/create',
                'purple'
            ); ?>
            <?php else: ?>
                <div id="category-list">
            <?php foreach ($templates as $categorySlug => $categoryData): ?>
                <div class="template-category-section" data-category-id="<?php echo $categoryData['category']['dtcid']; ?>">
                    <div class="category-header">
                        <button type="button" class="btn btn-sm btn-outline-secondary category-collapse-toggle" title="Toggle category">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="category-header-content">
                            <h2><?php echo htmlspecialchars($categoryData['category']['name']); ?></h2>
                            <span class="category-template-count"><?php echo count($categoryData['templates']); ?></span>
                            <?php if (!empty($categoryData['category']['is_system'])): ?>
                            <span class="category-system-badge" title="System Category">
                                <i class="fas fa-lock"></i>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($categoryData['category']['description'])): ?>
                            <p class="category-description"><?php echo htmlspecialchars($categoryData['category']['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="category-drag-handle" title="Drag to reorder category">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                    </div>
                    <div class="item-card-grid">
                        <?php if (empty($categoryData['templates'])): ?>
                        <!-- Empty Category State -->
                        <div class="item-card item-card-empty" data-category-id="<?php echo $categoryData['category']['dtcid']; ?>">
                            <div class="empty-category-content">
                                <div class="empty-category-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <h4>Empty Category</h4>
                                <p>Add templates to this category or remove it</p>
                                <div class="empty-category-actions">
                                    <a href="?urlq=dashboard/template/create" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add Template
                                    </a>
                                    <?php if (empty($categoryData['category']['is_system'])): ?>
                                    <button class="btn btn-outline-danger btn-sm delete-category-btn"
                                            data-category-id="<?php echo $categoryData['category']['dtcid']; ?>"
                                            data-category-name="<?php echo htmlspecialchars($categoryData['category']['name']); ?>">
                                        <i class="fas fa-trash"></i> Remove Category
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($categoryData['templates'] as $template): ?>
                        <div class="item-card" data-template-id="<?php echo $template['dtid']; ?>">
                            <div class="template-preview">
                                <?php
                                $structure = json_decode($template['structure'], true);
                                echo renderTemplatePreview($structure);
                                ?>
                            </div>
                            <div class="item-card-content">
                                <h3><?php echo htmlspecialchars($template['name']); ?></h3>
                                <?php if (!empty($template['description'])): ?>
                                <p class="item-card-description"><?php echo htmlspecialchars($template['description']); ?></p>
                                <?php endif; ?>
                                <?php if ($template['is_system']): ?>
                                <div class="item-card-tags">
                                    <span class="badge badge-system">
                                        <i class="fas fa-lock"></i> System
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="item-card-actions">
                                <button class="btn btn-icon btn-outline-primary"
                                        data-bs-toggle="tooltip"
                                        title="View Mode"
                                        onclick="window.location='?urlq=dashboard/template/preview/<?php echo $template['dtid']; ?>'">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (!$template['is_system']): ?>
                                <button class="btn btn-icon btn-outline-design"
                                        data-bs-toggle="tooltip"
                                        title="Design Mode"
                                        onclick="window.location='?urlq=dashboard/template/builder/<?php echo $template['dtid']; ?>'">
                                    <i class="fas fa-paint-brush"></i>
                                </button>
                                <?php endif; ?>
                                <button class="btn btn-icon btn-outline-success duplicate-template-btn"
                                        data-bs-toggle="tooltip"
                                        title="Duplicate"
                                        data-template-id="<?php echo $template['dtid']; ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <?php if (!$template['is_system']): ?>
                                <button class="btn btn-icon btn-outline-danger delete-template-btn"
                                        data-bs-toggle="tooltip"
                                        title="Delete"
                                        data-template-id="<?php echo $template['dtid']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                                <div class="template-drag-handle" data-bs-toggle="tooltip" title="Drag to reorder">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <script>
                (function() {
                    var id = '<?php echo $categoryData['category']['dtcid']; ?>';
                    if (window.__collapsedCategories && window.__collapsedCategories.indexOf(id) !== -1) {
                        document.querySelector('.template-category-section[data-category-id="' + id + '"]').classList.add('collapsed');
                    }
                })();
                </script>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

