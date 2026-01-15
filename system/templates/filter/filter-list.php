<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filters - Dynamic Graph Creator</title>

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
    <?php if ($css = Utility::getCss('filter')): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="page-header">
        <div class="page-header-left">
            <h1>Filters</h1>
        </div>
        <div class="page-header-right">
            <a href="?urlq=dashboard" class="btn btn-secondary">
                <i class="fas fa-th-large"></i> Dashboards
            </a>
            <a href="?urlq=graph" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> Graphs
            </a>
            <a href="?urlq=filters/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Filter
            </a>
        </div>
    </div>

    <div class="container">
        <div id="filter-list" class="filter-list-page">
            <?php if (empty($filters)): ?>
            <div class="filter-empty-state">
                <div class="empty-state-content">
                    <div class="empty-state-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <h3>No Filters Yet</h3>
                    <p>Create reusable filters for your graphs and dashboards</p>
                    <a href="?urlq=filters/add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Filter
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="filter-grid">
                <?php foreach ($filters as $filter): ?>
                <div class="filter-card" data-filter-id="<?php echo $filter['fid']; ?>">
                    <div class="filter-card-content">
                        <div class="filter-card-header">
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
                        <div class="filter-meta">
                            <span class="meta-item">
                                <?php if ($filter['data_source'] === 'query'): ?>
                                <i class="fas fa-database"></i> Query
                                <?php else: ?>
                                <i class="fas fa-list"></i> Static
                                <?php endif; ?>
                            </span>
                            <?php if ($filter['is_required']): ?>
                            <span class="meta-item required">
                                <i class="fas fa-asterisk"></i> Required
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="filter-card-actions">
                        <a href="?urlq=filters/edit/<?php echo $filter['fid']; ?>" class="btn-icon btn-warning" title="Edit">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <button type="button" class="btn-icon btn-danger delete-filter-btn"
                                data-id="<?php echo $filter['fid']; ?>"
                                data-label="<?php echo htmlspecialchars($filter['filter_label']); ?>"
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
    <div class="modal-overlay" id="delete-modal">
        <div class="modal-container modal-sm">
            <div class="modal-header">
                <h3>Delete Filter</h3>
                <button type="button" class="modal-close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the filter "<span class="filter-name"></span>"?</p>
                <p class="text-muted"><small>This will also remove it from any graphs using it.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel-btn">Cancel</button>
                <button type="button" class="btn btn-danger confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = Utility::getJs('common')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
    <?php if ($js = Utility::getJs('filter')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
</body>
</html>
