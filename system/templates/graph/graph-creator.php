<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $graph ? 'Edit' : 'Create'; ?> Graph - Dynamic Graph Creator</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Google Sans Font -->
    <link href="https://fonts.googleapis.com/css2?family=Product+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

    <!-- Custom CSS -->
    <?php if ($css = GraphUtility::getCss()): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="page-header">
        <div class="breadcrumb">
            <a href="?urlq=graph">Graphs</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo $graph ? 'Edit' : 'Create'; ?> Graph</span>
        </div>
        <h1><?php echo $graph ? 'Edit Graph' : 'Create New Graph'; ?></h1>
    </div>

    <div class="container container-full">
        <div id="graph-creator" class="graph-creator" data-graph-id="<?php echo $graph ? $graph->getId() : ''; ?>">

            <!-- Left: Graph Type Selector (Collapsible) -->
            <div class="graph-sidebar graph-sidebar-left">
                <div class="graph-type-selector collapsible-panel">
                    <div class="collapsible-header" data-toggle="collapse" data-target="type-panel">
                        <h3><i class="fas fa-chart-bar"></i> Chart Type</h3>
                        <button type="button" class="collapse-btn">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                    <div class="collapsible-content" id="type-panel">
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
                </div>
            </div>

            <!-- Center: Preview and Query -->
            <div class="graph-main">
                <!-- Preview Card -->
                <div class="graph-preview-card">
                    <div class="graph-preview-header">
                        <h3>Preview</h3>
                        <button type="button" class="btn btn-sm btn-outline" id="refresh-preview">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="graph-preview-container"></div>
                </div>

                <!-- Query Section -->
                <div class="graph-query-section">
                    <div class="query-tabs">
                        <button class="query-tab active" data-tab="query">SQL Query</button>
                        <button class="query-tab" data-tab="filters">Filters</button>
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
                                    <button type="button" class="btn btn-sm btn-primary test-query-btn">
                                        <i class="fas fa-play"></i> Test Query
                                    </button>
                                </div>
                            </div>
                            <div class="query-test-result" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Filters Tab -->
                    <div class="query-tab-content" id="tab-filters">
                        <div class="filter-manager"></div>
                    </div>

                    <!-- Mapping Tab -->
                    <div class="query-tab-content" id="tab-mapping">
                        <div class="data-mapper"></div>
                    </div>
                </div>

                <!-- Save Bar -->
                <div class="graph-save-bar">
                    <input type="text" class="form-control graph-name-input" placeholder="Enter graph name" value="<?php echo $graph ? htmlspecialchars($graph->getName()) : ''; ?>">
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
    <?php if ($js = GraphUtility::getJs()): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <!-- Initialize with existing data -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        document.querySelectorAll('.query-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.query-tab').forEach(function(t) {
                    t.classList.remove('active');
                });
                document.querySelectorAll('.query-tab-content').forEach(function(c) {
                    c.classList.remove('active');
                });

                this.classList.add('active');
                document.getElementById('tab-' + this.dataset.tab).classList.add('active');
            });
        });

        // Collapsible panels
        document.querySelectorAll('.collapsible-header').forEach(function(header) {
            header.addEventListener('click', function(e) {
                if (e.target.closest('.collapse-btn') || e.target === header) {
                    var panel = this.closest('.collapsible-panel');
                    var sidebar = this.closest('.graph-sidebar');
                    panel.classList.toggle('collapsed');
                    sidebar.classList.toggle('collapsed');

                    // Trigger chart resize after animation
                    setTimeout(function() {
                        if (window.graphCreator && window.graphCreator.preview) {
                            window.graphCreator.preview.resize();
                        }
                    }, 350);
                }
            });
        });

        // Set existing filters if editing
        <?php if ($graph && !empty($filters)): ?>
        setTimeout(function() {
            if (window.graphCreator && window.graphCreator.filterManager) {
                window.graphCreator.filterManager.setFilters(<?php echo json_encode($filters); ?>);
            }
        }, 100);
        <?php endif; ?>

        // Set existing config if editing
        <?php if ($graph): ?>
        setTimeout(function() {
            if (window.graphCreator && window.graphCreator.configPanel) {
                window.graphCreator.configPanel.setConfig(<?php echo $graph->getConfig(); ?>);
            }
        }, 100);
        <?php endif; ?>
    });
    </script>
</body>
</html>
