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

    <!-- Autosize for textarea auto-expand -->
    <script src="https://cdn.jsdelivr.net/npm/autosize@6.0.1/dist/autosize.min.js"></script>

    <!-- Daterangepicker Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

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
    $rightContent = '<div class="status-indicators"></div>';
    $rightContent .= '<button type="button" class="btn btn-outline-primary btn-sm save-graph-btn"><i class="fas fa-save"></i> Save Graph</button>';
    if ($graph) {
        $rightContent .= '<a href="?urlq=graph/view/' . $graph->getId() . '" class="btn btn-primary btn-sm btn-view-mode"><i class="fas fa-eye"></i> View Mode</a>';
    }
    echo Utility::renderPageHeader([
        'title' => $graph ? $graph->getName() : 'Create Graph',
        'backUrl' => '?urlq=graph',
        'backLabel' => 'Graphs',
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="container container-full">
        <div id="graph-creator" class="graph-creator graph-creator-single-sidebar" data-graph-id="<?php echo $graph ? $graph->getId() : ''; ?>">

            <!-- Left Sidebar - Graph Info, Chart Type, Config & Filters -->
            <div class="graph-sidebar graph-sidebar-left" id="graph-sidebar-left">
                <div class="sidebar-card" id="graph-sidebar-card">
                    <!-- Immediately apply saved collapse state to prevent flash -->
                    <script>
                        (function() {
                            if (localStorage.getItem('graphCreatorSidebarCollapsed') === 'true') {
                                document.getElementById('graph-sidebar-left').classList.add('collapsed');
                                document.getElementById('graph-sidebar-card').classList.add('collapsed');
                            }
                        })();
                    </script>

                    <!-- Sidebar Header with Title and Collapse Button -->
                    <div class="sidebar-card-header" data-toggle="collapse">
                        <h3 class="sidebar-card-title"><i class="fas fa-cog"></i> Graph Settings</h3>
                        <button type="button" class="collapse-btn">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>

                    <!-- Graph Info Section -->
                    <div class="sidebar-section graph-info-section">
                        <div class="graph-info-wrapper">
                            <div class="graph-name-wrapper">
                                <label class="graph-name-label" for="graph-name-input">Graph Name <span class="required">*</span></label>
                                <input type="text" class="form-control graph-name-input" id="graph-name-input" placeholder="Enter graph name" value="<?php echo $graph ? htmlspecialchars($graph->getName()) : ''; ?>" required>
                            </div>
                            <div class="graph-description-wrapper">
                                <label class="graph-description-label" for="graph-description-input">Description</label>
                                <textarea class="form-control graph-description-input" id="graph-description-input" placeholder="Enter graph description (optional)" rows="1"><?php echo $graph ? htmlspecialchars($graph->getDescription()) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Options Section -->
                    <div class="sidebar-section options-section">
                        <div class="sidebar-section-header">
                            <h3><i class="fas fa-sliders-h"></i> Options</h3>
                        </div>
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
                                                <div class="filter-selector-item" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                    <div class="form-check">
                                                        <input class="form-check-input filter-selector-checkbox" type="checkbox" value="<?php echo htmlspecialchars($filterKeyClean); ?>" id="filter-<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                        <label class="form-check-label" for="filter-<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                            <span class="filter-selector-label"><?php echo htmlspecialchars($filter['filter_label']); ?></span>
                                                            <code class="filter-selector-key"><?php echo htmlspecialchars($filterKey); ?></code>
                                                        </label>
                                                    </div>
                                                </div>
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
                                                // Get filter config for inline display
                                                $filterConfig = $filterObj->getFilterConfig();
                                                $filterConfigArr = $filterConfig ? json_decode($filterConfig, true) : array();
                                                $isInline = isset($filterConfigArr['inline']) && $filterConfigArr['inline'];
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
                                                        <div class="dropdown filter-multiselect-dropdown" data-filter-name="<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                            <button class="btn btn-outline-secondary dropdown-toggle filter-multiselect-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                                <span class="filter-multiselect-placeholder">-- Select multiple --</span>
                                                            </button>
                                                            <div class="dropdown-menu filter-multiselect-options">
                                                                <div class="filter-multiselect-header">
                                                                    <div class="filter-multiselect-actions">
                                                                        <button type="button" class="btn btn-link btn-sm multiselect-select-all">All</button>
                                                                        <span class="filter-multiselect-divider">|</span>
                                                                        <button type="button" class="btn btn-link btn-sm multiselect-select-none">None</button>
                                                                    </div>
                                                                    <input type="text" class="form-control form-control-sm multiselect-search" placeholder="Search...">
                                                                </div>
                                                                <?php foreach ($options as $index => $opt):
                                                                    $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                                                                    $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                                                                    $isSelected = is_array($opt) && isset($opt['is_selected']) && $opt['is_selected'];
                                                                    $optId = 'multiselect-' . $filterKeyClean . '-' . $index;
                                                                ?>
                                                                    <div class="dropdown-item filter-multiselect-option">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" name="<?php echo htmlspecialchars($filterKeyClean); ?>[]" value="<?php echo htmlspecialchars($value); ?>" id="<?php echo $optId; ?>" <?php echo $isSelected ? 'checked' : ''; ?>>
                                                                            <label class="form-check-label" for="<?php echo $optId; ?>"><?php echo htmlspecialchars($label); ?></label>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>

                                                    <?php elseif ($filterType === 'checkbox'): ?>
                                                        <div class="filter-checkbox-group<?php echo $isInline ? ' inline' : ''; ?>">
                                                            <?php foreach ($options as $index => $opt):
                                                                $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                                                                $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                                                                $isSelected = is_array($opt) && isset($opt['is_selected']) && $opt['is_selected'];
                                                                $optId = 'checkbox-' . $filterKeyClean . '-' . $index;
                                                            ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="<?php echo htmlspecialchars($filterKeyClean); ?>[]" value="<?php echo htmlspecialchars($value); ?>" id="<?php echo $optId; ?>" <?php echo $isSelected ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="<?php echo $optId; ?>"><?php echo htmlspecialchars($label); ?></label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>

                                                    <?php elseif ($filterType === 'radio'): ?>
                                                        <div class="filter-radio-group<?php echo $isInline ? ' inline' : ''; ?>">
                                                            <?php foreach ($options as $index => $opt):
                                                                $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                                                                $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                                                                $isSelected = is_array($opt) && isset($opt['is_selected']) && $opt['is_selected'];
                                                                $checked = $isSelected || ($value == $defaultValue) ? 'checked' : '';
                                                                $optId = 'radio-' . $filterKeyClean . '-' . $index;
                                                            ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($value); ?>" id="<?php echo $optId; ?>" <?php echo $checked; ?>>
                                                                    <label class="form-check-label" for="<?php echo $optId; ?>"><?php echo htmlspecialchars($label); ?></label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>

                                                    <?php elseif ($filterType === 'date'): ?>
                                                        <input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($defaultValue); ?>" data-picker-type="single" placeholder="Select date" autocomplete="off">

                                                    <?php elseif ($filterType === 'date_range'): ?>
                                                        <input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="<?php echo htmlspecialchars($filterKeyClean); ?>" data-picker-type="range" placeholder="Select date range" autocomplete="off">

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
                                <?php if ($canCreateFilter): ?>
                                    <a href="?urlq=filters/add" class="btn btn-sm btn-outline-secondary filters-manage-btn">
                                        <i class="fas fa-plus"></i> Create Filter
                                    </a>
                                <?php endif; ?>
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
                        <h3><i class="fas fa-chart-bar"></i> Preview</h3>
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

                <!-- Data Mapping Section -->
                <div class="graph-section">
                    <div class="data-mapper"></div>
                </div>

                <!-- SQL Query Section -->
                <div class="graph-section">
                    <div class="graph-section-header">
                        <h3><i class="fas fa-database"></i> SQL Query</h3>
                    </div>
                    <div class="query-builder">
                        <textarea class="query-editor" placeholder="SELECT category, SUM(amount) as total FROM sales WHERE date >= :date_from GROUP BY category"><?php echo $graph ? htmlspecialchars($graph->getQuery()) : ''; ?></textarea>
                        <!-- Copy, Format, Test buttons are generated by CodeMirrorEditor -->
                        <div class="query-test-result" style="display: none;"></div>
                    </div>
                </div>

                <!-- Placeholder Settings Section -->
                <div class="graph-section placeholder-settings-section" style="display: none;">
                    <div class="graph-section-header">
                        <h3><i class="fas fa-link"></i> Placeholder Settings</h3>
                        <small class="text-muted">Configure behavior when filter values are empty</small>
                    </div>
                    <div class="placeholder-settings-content">
                        <table class="placeholder-settings-table">
                            <thead>
                                <tr>
                                    <th>Placeholder</th>
                                    <th>Linked Filter</th>
                                    <th>Allow Empty</th>
                                </tr>
                            </thead>
                            <tbody id="placeholder-settings-body">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                        <p class="placeholder-settings-hint text-muted">
                            <i class="fas fa-info-circle"></i>
                            When "Allow Empty" is checked, empty filter values will match all records. Otherwise, an error will be shown.
                        </p>
                    </div>
                </div>

                <!-- Graph Data Section (populated after query test) -->
                <div class="graph-section graph-data-section" style="display: none;">
                    <div class="graph-section-header">
                        <h3><i class="fas fa-table"></i> Graph Data</h3>
                    </div>
                    <div class="graph-data-content"></div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Daterangepicker JS (after jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

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