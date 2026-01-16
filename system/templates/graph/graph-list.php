<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphs - Dynamic Graph Creator</title>

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
    <?php if ($css = Utility::getCss('graph')): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="page-header">
        <div class="page-header-left">
            <h1>Graphs</h1>
        </div>
        <div class="page-header-right">
            <a href="?urlq=dashboard" class="btn btn-secondary">
                <i class="fas fa-th-large"></i> Dashboards
            </a>
            <a href="?urlq=filters" class="btn btn-secondary">
                <i class="fas fa-filter"></i> Filters
            </a>
            <?php if (!empty($graphs)): ?>
            <a href="?urlq=graph/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Graph
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div id="graph-list" class="graph-list-page">
            <?php if (empty($graphs)): ?>
            <?php echo Utility::renderEmptyState(
                'fa-chart-bar',
                'No Graphs Yet',
                'Create your first graph to visualize your data',
                'Create Graph',
                '?urlq=graph/create',
                'orange'
            ); ?>
            <?php else: ?>
            <div class="graph-grid">
                <?php foreach ($graphs as $g): ?>
                <div class="graph-card" data-graph-id="<?php echo $g->getId(); ?>">
                    <div class="graph-card-content">
                        <div class="graph-card-header">
                            <span class="graph-type-icon <?php echo $g->getGraphType(); ?>">
                                <i class="fas fa-chart-<?php echo $g->getGraphType(); ?>"></i>
                            </span>
                            <span class="graph-type-badge <?php echo $g->getGraphType(); ?>">
                                <?php echo ucfirst($g->getGraphType()); ?>
                            </span>
                        </div>
                        <h3><?php echo htmlspecialchars($g->getName()); ?></h3>
                        <?php if ($g->getDescription()): ?>
                        <p class="graph-description"><?php echo htmlspecialchars($g->getDescription()); ?></p>
                        <?php endif; ?>
                        <div class="graph-meta">
                            <span class="meta-item">
                                <i class="fas fa-clock"></i>
                                <?php echo date('M d, Y', strtotime($g->getUpdatedTs())); ?>
                            </span>
                        </div>
                    </div>
                    <div class="graph-card-actions">
                        <a href="?urlq=graph/view/<?php echo $g->getId(); ?>" class="btn-icon btn-primary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="?urlq=graph/edit/<?php echo $g->getId(); ?>" class="btn-icon btn-warning" title="Edit">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <button type="button" class="btn-icon btn-danger delete-graph-btn"
                                data-id="<?php echo $g->getId(); ?>"
                                data-name="<?php echo htmlspecialchars($g->getName()); ?>"
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
        <div class="modal-dialog modal-dialog-centered">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger confirm-delete-btn">Delete</button>
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
    <?php if ($js = Utility::getJs('graph')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var deleteModalElement = document.getElementById('delete-modal');
        var deleteModal = new bootstrap.Modal(deleteModalElement);
        var graphIdToDelete = null;

        // Delete graph buttons
        document.querySelectorAll('.delete-graph-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                graphIdToDelete = this.dataset.id;
                deleteModalElement.querySelector('.graph-name').textContent = this.dataset.name;
                deleteModal.show();
            });
        });

        // Confirm delete
        document.querySelector('.confirm-delete-btn').addEventListener('click', function() {
            if (graphIdToDelete) {
                Loading.show('Deleting graph...');
                Ajax.post('delete_graph', { id: graphIdToDelete }).then(function(result) {
                    Loading.hide();
                    deleteModal.hide();
                    if (result.success) {
                        Toast.success('Graph deleted');
                        location.reload();
                    } else {
                        Toast.error(result.message || 'Failed to delete graph');
                    }
                }).catch(function() {
                    Loading.hide();
                    deleteModal.hide();
                    Toast.error('Failed to delete graph');
                });
            }
        });
    });
    </script>
</body>
</html>
