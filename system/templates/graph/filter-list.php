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
    <?php if ($css = GraphUtility::getCss()): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="page-header">
        <div class="page-header-left">
            <h1>Dynamic Graph Creator</h1>
            <div class="breadcrumb">
                <i class="fas fa-chevron-right"></i>
                <a href="?urlq=graph">Graphs</a>
                <i class="fas fa-chevron-right"></i>
                <span>Filters</span>
            </div>
        </div>
        <div class="page-header-right">
            <a href="?urlq=graph" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Graphs
            </a>
            <a href="?urlq=graph/filters/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Filter
            </a>
        </div>
    </div>

    <div class="container">
        <div id="filter-list" class="filter-list-page">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h2>All Filters</h2>
                        <span class="text-muted"><?php echo count($filters); ?> filter<?php echo count($filters) !== 1 ? 's' : ''; ?></span>
                    </div>
                </div>

                <?php if (empty($filters)): ?>
                <div class="filter-empty-state">
                    <i class="fas fa-filter"></i>
                    <p>No filters created yet</p>
                    <span>Click "Add Filter" to create your first filter</span>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Key</th>
                                <th>Type</th>
                                <th>Data Source</th>
                                <th>Required</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filters as $filter): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($filter['filter_label']); ?></td>
                                <td><code><?php echo htmlspecialchars($filter['filter_key']); ?></code></td>
                                <td>
                                    <span class="badge badge-<?php echo $filter['filter_type']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $filter['filter_type'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($filter['data_source'] === 'query'): ?>
                                    <span class="text-primary"><i class="fas fa-database"></i> Query</span>
                                    <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-list"></i> Static</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($filter['is_required']): ?>
                                    <i class="fas fa-check text-success"></i>
                                    <?php else: ?>
                                    <i class="fas fa-minus text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?urlq=graph/filters/edit/<?php echo $filter['fid']; ?>" class="btn btn-sm btn-outline" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline delete-filter-btn" data-id="<?php echo $filter['fid']; ?>" data-label="<?php echo htmlspecialchars($filter['filter_label']); ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
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
    <?php if ($js = GraphUtility::getJs()): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var deleteModal = document.getElementById('delete-modal');
        var filterIdToDelete = null;

        // Delete filter buttons
        document.querySelectorAll('.delete-filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                filterIdToDelete = this.dataset.id;
                deleteModal.querySelector('.filter-name').textContent = this.dataset.label;
                openModal(deleteModal);
            });
        });

        // Confirm delete
        document.querySelector('.confirm-delete-btn').addEventListener('click', function() {
            if (filterIdToDelete) {
                Loading.show('Deleting filter...');
                Ajax.post('delete_filter', { id: filterIdToDelete }).then(function(result) {
                    Loading.hide();
                    if (result.success) {
                        Toast.success('Filter deleted');
                        location.reload();
                    } else {
                        Toast.error(result.message || 'Failed to delete filter');
                    }
                }).catch(function() {
                    Loading.hide();
                    Toast.error('Failed to delete filter');
                });
            }
        });

        // Modal close buttons
        document.querySelectorAll('.modal-close-btn, .modal-cancel-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                closeModal(this.closest('.modal-overlay'));
            });
        });

        function openModal(modal) {
            modal.classList.add('active');
        }

        function closeModal(modal) {
            modal.classList.remove('active');
        }
    });
    </script>
</body>
</html>
