<?php
    $rightContent = '<a href="?urlq=dashboard" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Dashboards"><i class="fas fa-th-large"></i></a>';
    $rightContent .= '<a href="?urlq=graph" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Graphs"><i class="fas fa-chart-bar"></i></a>';
    if (!empty($filters)) {
        $rightContent .= '<a href="?urlq=data-filters/create" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create Data Filter"><i class="fas fa-plus"></i></a>';
    }
    echo Utility::renderPageHeader([
        'title' => 'Data Filters',
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="container">
        <div id="filter-list" class="filter-list-page">
            <?php if (empty($filters)): ?>
            <?php echo Utility::renderEmptyState(
                'fa-filter',
                'No Data Filters Yet',
                'Create reusable filters for your graphs and dashboards',
                'Create Data Filter',
                '?urlq=data-filters/create',
                'green'
            ); ?>
            <?php else: ?>
            <div class="item-card-grid">
                <?php foreach ($filters as $filter): ?>
                <div class="item-card" data-filter-id="<?php echo $filter['dfid']; ?>">
                    <div class="item-card-content">
                        <div class="item-card-header">
                            <?php
                            $typeIcons = array(
                                'text' => 'font',
                                'number' => 'hashtag',
                                'date' => 'calendar',
                                'date_range' => 'calendar-week',
                                'select' => 'list',
                                'multi_select' => 'list-check',
                                'checkbox' => 'check-square',
                                'radio' => 'circle-dot',
                                'tokeninput' => 'tags'
                            );
                            $typeIcon = isset($typeIcons[$filter['filter_type']]) ? $typeIcons[$filter['filter_type']] : 'filter';
                            ?>
                            <span class="filter-type-icon <?php echo $filter['filter_type']; ?>">
                                <i class="fas fa-<?php echo $typeIcon; ?>"></i>
                            </span>
                            <span class="filter-type-badge <?php echo $filter['filter_type']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $filter['filter_type'])); ?>
                            </span>
                        </div>
                        <h3><?php echo htmlspecialchars($filter['filter_label']); ?></h3>
                        <div class="filter-key">
                            <code><?php echo htmlspecialchars($filter['filter_key']); ?></code>
                        </div>
                        <div class="item-card-meta">
                            <span class="meta-item data-source-icon <?php echo $filter['data_source']; ?>">
                                <?php if ($filter['data_source'] === 'query'): ?>
                                <i class="fas fa-database"></i> Query
                                <?php else: ?>
                                <i class="fas fa-list-ul"></i> Static
                                <?php endif; ?>
                            </span>
                            <?php if ($filter['is_required']): ?>
                            <span class="meta-item required">
                                <i class="fas fa-asterisk"></i> Required
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="item-card-actions">
                        <a href="?urlq=data-filters/edit/<?php echo $filter['dfid']; ?>" class="btn btn-icon btn-outline-design" data-bs-toggle="tooltip" title="Design Mode">
                            <i class="fas fa-paint-brush"></i>
                        </a>
                        <button type="button" class="btn btn-icon btn-outline-danger delete-filter-btn"
                                data-id="<?php echo $filter['dfid']; ?>"
                                data-label="<?php echo htmlspecialchars($filter['filter_label']); ?>"
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
                    <h5 class="modal-title">Delete Data Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the data filter "<span class="filter-name"></span>"?</p>
                    <p class="text-muted"><small>This will also remove it from any graphs using it.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-danger confirm-delete-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>

