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
    <?php if ($css = GraphUtility::getCss()): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="page-header">
        <h1>Dynamic Graph Creator</h1>
    </div>

    <div class="container">
        <div class="graph-list-page">
            <div class="graph-list-header">
                <h2>Saved Graphs</h2>
                <a href="?urlq=graph/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Graph
                </a>
            </div>

            <div class="graph-list-card" id="graph-list">
                <?php if (empty($graphs)): ?>
                    <div class="graph-list-empty">
                        <i class="fas fa-chart-bar"></i>
                        <h3>No graphs yet</h3>
                        <p>Create your first graph to get started</p>
                        <a href="?urlq=graph/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Graph
                        </a>
                    </div>
                <?php else: ?>
                    <table class="graph-list-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($graphs as $g): ?>
                            <tr>
                                <td>
                                    <div class="graph-name-cell">
                                        <span class="graph-type-badge <?php echo $g->getGraphType(); ?>">
                                            <i class="fas fa-chart-<?php echo $g->getGraphType(); ?>"></i>
                                        </span>
                                        <div>
                                            <div class="graph-name"><?php echo htmlspecialchars($g->getName()); ?></div>
                                            <?php if ($g->getDescription()): ?>
                                            <div class="graph-description"><?php echo htmlspecialchars($g->getDescription()); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-capitalize"><?php echo $g->getGraphType(); ?></span>
                                </td>
                                <td>
                                    <span class="graph-date"><?php echo date('M j, Y', strtotime($g->getUpdatedTs())); ?></span>
                                </td>
                                <td>
                                    <div class="graph-actions">
                                        <a href="?urlq=graph/view/<?php echo $g->getId(); ?>" class="action-btn view-btn" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?urlq=graph/edit/<?php echo $g->getId(); ?>" class="action-btn edit-btn" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="action-btn delete-btn"
                                                data-id="<?php echo $g->getId(); ?>"
                                                data-name="<?php echo htmlspecialchars($g->getName()); ?>"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="delete-modal" id="delete-modal">
        <div class="delete-modal-content">
            <h3>Delete Graph</h3>
            <p>Are you sure you want to delete "<span class="graph-name"></span>"?</p>
            <p class="text-muted text-small">This action cannot be undone.</p>
            <div class="delete-modal-actions">
                <button type="button" class="btn btn-secondary cancel-delete">Cancel</button>
                <button type="button" class="btn btn-danger confirm-delete">Delete</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = GraphUtility::getJs()): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
</body>
</html>
