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
    <?php
    $rightContent = '';
    if ($graph) {
        $rightContent = '<a href="?urlq=graph/view/' . $graph->getId() . '" class="btn btn-primary btn-sm btn-view-mode"><i class="fas fa-eye"></i> View Mode</a>';
    }
    echo Utility::renderPageHeader([
        'title' => $graph ? $graph->getName() : 'Create Graph',
        'backUrl' => '?urlq=graph',
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="container container-full">
        <div id="graph-creator" class="graph-creator graph-creator-single-sidebar" data-graph-id="<?php echo $graph ? $graph->getId() : ''; ?>">

            <!-- Left Sidebar - Chart Type, Config & Filters -->
            <div class="graph-sidebar graph-sidebar-left" id="graph-sidebar-left">
                <div class="collapsible-panel" id="graph-collapsible-panel">
                    <!-- Immediately apply saved collapse state to prevent flash -->
                    <script>
                        (function() {
                            if (localStorage.getItem('graphCreatorSidebarCollapsed') === 'true') {
                                document.getElementById('graph-sidebar-left').classList.add('collapsed');
                                document.getElementById('graph-collapsible-panel').classList.add('collapsed');
                            }
                        })();
                    </script>
                    <div class="collapsible-header" data-toggle="collapse" data-target="left-panel">
                        <h3><i class="fas fa-sliders-h"></i> Options</h3>
                        <button type="button" class="collapse-btn">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                    <div class="collapsible-content" id="left-panel">
                        <!-- Sidebar Tabs -->
                        <div class="sidebar-tabs">
                            <button class="sidebar-tab active" data-tab="config">
                                <i class="fas fa-chart-bar"></i> Chart
                            </button>
                            <button class="sidebar-tab" data-tab="filters">
                                <i class="fas fa-filter"></i> Filters
                            </button>
                        </div>

                        <!-- Chart Tab Content -->
                        <div class="sidebar-tab-content active" id="sidebar-tab-config">
                            <!-- Chart Type Selector - Horizontal Scroll -->
                            <div class="chart-type-selector">
                                <div class="chart-type-scroll">
                                    <div class="chart-type-item <?php echo (!$graph || $graph->getGraphType() === 'bar') ? 'active' : ''; ?>" data-type="bar" title="Bar Chart">
                                        <i class="fas fa-chart-bar"></i>
                                        <span>Bar</span>
                                    </div>
                                    <div class="chart-type-item <?php echo ($graph && $graph->getGraphType() === 'line') ? 'active' : ''; ?>" data-type="line" title="Line Chart">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Line</span>
                                    </div>
                                    <div class="chart-type-item <?php echo ($graph && $graph->getGraphType() === 'pie') ? 'active' : ''; ?>" data-type="pie" title="Pie Chart">
                                        <i class="fas fa-chart-pie"></i>
                                        <span>Pie</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Config Panel -->
                            <div class="graph-config-panel"></div>
                        </div>

                        <!-- Filters Tab Content -->
                        <div class="sidebar-tab-content" id="sidebar-tab-filters">
                            <div class="sidebar-section filters-panel no-border">
                                <?php if (!empty($allFilters)): ?>
                                    <!-- Filter Selection View -->
                                    <div class="filter-selector-view" id="filter-selector-view">
                                        <div class="filter-selector-header">
                                            <span class="filter-selector-title">Available Filters</span>
                                            <span class="filter-selector-count">0 selected</span>
                                        </div>
                                        <div class="filter-selector-list">
                                            <?php foreach ($allFilters as $filter): ?>
                                                <?php
                                                $filterKey = $filter['filter_key'];
                                                $filterKeyClean = ltrim($filterKey, ':');
                                                ?>
                                                <label class="filter-selector-item" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                    <input type="checkbox" class="filter-selector-checkbox" value="<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                    <div class="filter-selector-info">
                                                        <span class="filter-selector-label"><?php echo htmlspecialchars($filter['filter_label']); ?></span>
                                                        <code class="filter-selector-key"><?php echo htmlspecialchars($filterKey); ?></code>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary filter-use-btn" id="filter-use-btn" disabled>
                                            <i class="fas fa-check"></i> Use Selected Filters
                                        </button>
                                    </div>

                                    <!-- Active Filters View (hidden initially) -->
                                    <div class="filter-active-view" id="filter-active-view" style="display: none;">
                                        <div class="filter-active-header">
                                            <span class="filter-active-title">Active Filters</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary filter-change-btn" id="filter-change-btn">
                                                <i class="fas fa-exchange-alt"></i> Change
                                            </button>
                                        </div>
                                        <div class="filters-list" id="graph-filters">
                                            <?php foreach ($allFilters as $filter): ?>
                                                <?php
                                                // Get filter options
                                                $filterObj = new Filter($filter['fid']);
                                                $options = $filterObj->getOptions();
                                                $filterType = $filter['filter_type'];
                                                $filterKey = $filter['filter_key'];
                                                $filterKeyClean = ltrim($filterKey, ':');
                                                $defaultValue = $filter['default_value'];
                                                ?>
                                                <div class="filter-input-item" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>" style="display: none;">
                                                    <div class="filter-input-header">
                                                        <label class="filter-input-label"><?php echo htmlspecialchars($filter['filter_label']); ?></label>
                                                        <code class="filter-placeholder" title="Use in query"><?php echo htmlspecialchars($filterKey); ?></code>
                                                    </div>

                                                    <?php if ($filterType === 'select'): ?>
                                                        <select class="form-control form-control-sm filter-input" name="<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                            <option value="">-- Select --</option>
                                                            <?php foreach ($options as $opt):
                                                                $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                                                                $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                                                                $selected = ($value == $defaultValue) ? 'selected' : '';
                                                            ?>
                                                                <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($label); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>

                                                    <?php elseif ($filterType === 'multi_select'): ?>
                                                        <div class="filter-multiselect-dropdown" data-filter-name="<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                            <div class="filter-multiselect-trigger">
                                                                <span class="filter-multiselect-placeholder">-- Select multiple --</span>
                                                                <i class="fas fa-chevron-down"></i>
                                                            </div>
                                                            <div class="filter-multiselect-options">
                                                                <?php foreach ($options as $opt):
                                                                    $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                                                                    $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                                                                ?>
                                                                    <label class="filter-multiselect-option">
                                                                        <input type="checkbox" name="<?php echo htmlspecialchars($filterKeyClean); ?>[]" value="<?php echo htmlspecialchars($value); ?>">
                                                                        <span><?php echo htmlspecialchars($label); ?></span>
                                                                    </label>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>

                                                    <?php elseif ($filterType === 'checkbox'): ?>
                                                        <div class="filter-checkbox-group">
                                                            <?php foreach ($options as $opt):
                                                                $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                                                                $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                                                            ?>
                                                                <label class="filter-checkbox">
                                                                    <input type="checkbox" name="<?php echo htmlspecialchars($filterKeyClean); ?>[]" value="<?php echo htmlspecialchars($value); ?>">
                                                                    <span><?php echo htmlspecialchars($label); ?></span>
                                                                </label>
                                                            <?php endforeach; ?>
                                                        </div>

                                                    <?php elseif ($filterType === 'radio'): ?>
                                                        <div class="filter-radio-group">
                                                            <?php foreach ($options as $opt):
                                                                $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                                                                $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                                                                $checked = ($value == $defaultValue) ? 'checked' : '';
                                                            ?>
                                                                <label class="filter-radio">
                                                                    <input type="radio" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($value); ?>" <?php echo $checked; ?>>
                                                                    <span><?php echo htmlspecialchars($label); ?></span>
                                                                </label>
                                                            <?php endforeach; ?>
                                                        </div>

                                                    <?php elseif ($filterType === 'date'): ?>
                                                        <input type="date" class="form-control form-control-sm filter-input" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($defaultValue); ?>">

                                                    <?php elseif ($filterType === 'date_range'): ?>
                                                        <div class="filter-date-range">
                                                            <input type="date" class="form-control form-control-sm filter-input" name="<?php echo htmlspecialchars($filterKeyClean); ?>_from" placeholder="From">
                                                            <span class="date-range-separator">to</span>
                                                            <input type="date" class="form-control form-control-sm filter-input" name="<?php echo htmlspecialchars($filterKeyClean); ?>_to" placeholder="To">
                                                        </div>

                                                    <?php elseif ($filterType === 'number'): ?>
                                                        <input type="number" class="form-control form-control-sm filter-input" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($defaultValue); ?>" placeholder="Enter number">

                                                    <?php else: /* text */ ?>
                                                        <input type="text" class="form-control form-control-sm filter-input" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($defaultValue); ?>" placeholder="Enter value">
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="filters-empty">
                                        <p>No filters available</p>
                                    </div>
                                <?php endif; ?>
                                <a href="?urlq=filters/add" class="btn btn-sm btn-outline-secondary filters-manage-btn">
                                    <i class="fas fa-plus"></i> Create Filter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center: Preview and Query -->
            <div class="graph-main">
                <!-- Save Bar (at top) -->
                <div class="graph-save-bar">
                    <div class="graph-name-wrapper">
                        <label class="graph-name-label">Graph Name <span class="required">*</span></label>
                        <input type="text" class="form-control form-control-sm graph-name-input" placeholder="Enter graph name" value="<?php echo $graph ? htmlspecialchars($graph->getName()) : ''; ?>" required>
                    </div>
                    <div class="save-buttons">
                        <a href="?urlq=graph" class="btn btn-secondary btn-sm">Cancel</a>
                        <button type="button" class="btn btn-primary btn-sm save-graph-btn">
                            <i class="fas fa-save"></i> Save Graph
                        </button>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="graph-preview-card">
                    <div class="graph-preview-header">
                        <h3>Preview</h3>
                        <div class="graph-preview-actions">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="export-chart" title="Save chart as PNG image">
                                <i class="fas fa-image"></i> Save Image
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-preview">
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
                                    <button type="button" class="btn btn-sm btn-outline-secondary copy-query-btn" title="Copy SQL">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary format-query-btn" title="Format SQL">
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
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = Utility::getJs('common')): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
    <script src="system/scripts/src/Theme.js"></script>
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