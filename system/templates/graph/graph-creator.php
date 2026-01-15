<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphs - <?php echo $graph ? 'Edit' : 'Create'; ?> Graph - Dynamic Graph Creator</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Google Sans Font -->
    <link href="https://fonts.googleapis.com/css2?family=Product+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

    <!-- CodeMirror for SQL highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/material.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/sql/sql.min.js"></script>

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
            <a href="?urlq=graph" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1><?php echo $graph ? 'Edit: ' . htmlspecialchars($graph->getName()) : 'Create Graph'; ?></h1>
        </div>
    </div>

    <div class="container container-full">
        <div id="graph-creator" class="graph-creator" data-graph-id="<?php echo $graph ? $graph->getId() : ''; ?>">

            <!-- Left Sidebar -->
            <div class="graph-sidebar graph-sidebar-left">
                <div class="collapsible-panel">
                    <div class="collapsible-header" data-toggle="collapse" data-target="left-panel">
                        <h3><i class="fas fa-cog"></i> Options</h3>
                        <button type="button" class="collapse-btn">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                    <div class="collapsible-content" id="left-panel">
                        <!-- Chart Type Section -->
                        <div class="sidebar-section graph-type-selector">
                            <h4><i class="fas fa-chart-bar"></i> Chart Type</h4>
                            <div class="graph-type-list">
                                <div class="graph-type-item <?php echo (!$graph || $graph->getGraphType() === 'bar') ? 'active' : ''; ?>" data-type="bar">
                                    <span class="graph-type-icon"><i class="fas fa-chart-bar"></i></span>
                                    <span class="graph-type-label">Bar Chart</span>
                                </div>
                                <div class="graph-type-item <?php echo ($graph && $graph->getGraphType() === 'line') ? 'active' : ''; ?>" data-type="line">
                                    <span class="graph-type-icon"><i class="fas fa-chart-line"></i></span>
                                    <span class="graph-type-label">Line Chart</span>
                                </div>
                                <div class="graph-type-item <?php echo ($graph && $graph->getGraphType() === 'pie') ? 'active' : ''; ?>" data-type="pie">
                                    <span class="graph-type-icon"><i class="fas fa-chart-pie"></i></span>
                                    <span class="graph-type-label">Pie Chart</span>
                                </div>
                            </div>
                        </div>

                        <!-- Filters Section (only show if graph exists) -->
                        <?php if ($graph): ?>
                        <div class="sidebar-section filters-panel">
                            <h4><i class="fas fa-filter"></i> Filters</h4>
                            <?php if (!empty($filters)): ?>
                                <div class="filters-list">
                                    <?php foreach ($filters as $filter): ?>
                                        <div class="filter-display-item">
                                            <span class="filter-display-label"><?php echo htmlspecialchars($filter['filter_label']); ?></span>
                                            <span class="filter-display-type"><?php echo ucfirst(str_replace('_', ' ', $filter['filter_type'])); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="filters-empty">
                                    <p>No filters defined</p>
                                </div>
                            <?php endif; ?>
                            <a href="?urlq=filters/add" class="btn btn-sm btn-outline filters-manage-btn">
                                <i class="fas fa-plus"></i> Add Filter
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Center: Preview and Query -->
            <div class="graph-main">
                <!-- Preview Card -->
                <div class="graph-preview-card">
                    <div class="graph-preview-header">
                        <h3>Preview</h3>
                        <div class="graph-preview-actions">
                            <button type="button" class="btn btn-sm btn-outline" id="export-chart" title="Save chart as PNG image">
                                <i class="fas fa-image"></i> Save Image
                            </button>
                            <button type="button" class="btn btn-sm btn-outline" id="refresh-preview">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="graph-preview-container"></div>
                </div>

                <!-- Query Section -->
                <div class="graph-query-section">
                    <div class="query-tabs">
                        <button class="query-tab active" data-tab="query">SQL Query</button>
                        <button class="query-tab" data-tab="mapping">Data Mapping</button>
                    </div>

                    <!-- Query Tab -->
                    <div class="query-tab-content active" id="tab-query">
                        <div class="query-builder">
                            <div class="query-editor-wrapper">
                                <textarea class="query-editor" placeholder="SELECT category, SUM(amount) as total FROM sales WHERE date >= :date_from GROUP BY category"><?php echo $graph ? htmlspecialchars($graph->getQuery()) : ''; ?></textarea>
                            </div>
                            <div class="query-toolbar">
                                <div class="query-toolbar-left">
                                    <span class="query-hint">Use <code>:placeholder</code> for filter values</span>
                                </div>
                                <div class="query-toolbar-right">
                                    <button type="button" class="btn btn-sm btn-outline copy-query-btn" title="Copy SQL">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline format-query-btn" title="Format SQL">
                                        <i class="fas fa-align-left"></i> Format SQL
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary test-query-btn">
                                        <i class="fas fa-play"></i> Test Query
                                    </button>
                                </div>
                            </div>
                            <div class="query-test-result" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Mapping Tab -->
                    <div class="query-tab-content" id="tab-mapping">
                        <div class="data-mapper"></div>
                    </div>
                </div>

                <!-- Save Bar -->
                <div class="graph-save-bar">
                    <div class="graph-name-wrapper">
                        <label class="graph-name-label">Graph Name <span class="required">*</span></label>
                        <input type="text" class="form-control graph-name-input" placeholder="Enter graph name" value="<?php echo $graph ? htmlspecialchars($graph->getName()) : ''; ?>" required>
                    </div>
                    <div class="save-buttons">
                        <a href="?urlq=graph" class="btn btn-secondary">Cancel</a>
                        <button type="button" class="btn btn-primary save-graph-btn">
                            <i class="fas fa-save"></i> Save Graph
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Config Panel (Collapsible) -->
            <div class="graph-sidebar graph-sidebar-right">
                <div class="collapsible-panel">
                    <div class="collapsible-header" data-toggle="collapse" data-target="config-panel">
                        <button type="button" class="collapse-btn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <h3><i class="fas fa-sliders-h"></i> Configuration</h3>
                    </div>
                    <div class="collapsible-content" id="config-panel">
                        <div class="graph-config-panel"></div>
                    </div>
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

    <?php if ($graph): ?>
        <!-- Set existing config if editing -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (window.graphCreator && window.graphCreator.configPanel) {
                        window.graphCreator.configPanel.setConfig(<?php echo $graph->getConfig(); ?>);
                    }
                }, 100);
            });
        </script>
    <?php endif; ?>
</body>

</html>