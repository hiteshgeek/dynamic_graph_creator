
<?php
// Set company start date for datepicker presets
echo DGCHelper::renderCompanyStartDateScript();

// Operations
$rightContent = '<div class="status-indicators"></div>';
$saveButtonClass = $graph ? 'btn-outline-warning' : 'btn-primary';
$rightContent .= '<button type="button" class="btn ' . $saveButtonClass . ' btn-sm save-graph-btn" data-save-btn><i class="fas fa-save"></i> ' . ($graph ? 'Save' : 'Create Graph') . '</button>';
if ($graph) {
    $rightContent .= '<a href="?urlq=widget-graph/view/' . $graph->getId() . '" class="btn btn-icon btn-outline-primary btn-view-mode" data-bs-toggle="tooltip" data-bs-placement="bottom" title="View Mode"><i class="fas fa-eye"></i></a>';
    $rightContent .= '<a href="?urlq=widget-graph/create" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create New Graph"><i class="fas fa-plus"></i></a>';
}
// Navigation links
$rightContent .= '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Home"><i class="fas fa-home"></i></a>';
if (DGCHelper::hasAdminAccess()) {
    $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
    $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
}
// Widget dropdown
$leftContent = DGCHelper::renderWidgetDropdown('graph');
echo DGCHelper::renderPageHeader([
    'title' => $graph ? 'Edit Graph' : 'Create Graph',
    'backUrl' => '?urlq=widget-graph',
    'backLabel' => 'Graphs',
    'leftContent' => $leftContent,
    'rightContent' => $rightContent
]);
?>

<div class="container container-full">
    <div id="graph-creator" class="graph-creator graph-creator-single-sidebar" data-graph-id="<?php echo $graph ? $graph->getId() : ''; ?>" data-graph-config="<?php echo $graph ? htmlspecialchars($graph->getConfig()) : ''; ?>" data-mandatory-filters="<?php echo htmlspecialchars(json_encode(isset($mandatoryFilterKeys) ? $mandatoryFilterKeys : array())); ?>" data-widget-type="graph">

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
                    <form id="graph-form">
                        <input type="hidden" id="graph-id" value="<?php echo $graph ? $graph->getId() : ''; ?>">
                        <div class="graph-info-wrapper">
                            <div class="graph-name-wrapper">
                                <label class="graph-name-label" for="graph-name-input">Graph Name <span class="required">*</span></label>
                                <input type="text" class="form-control graph-name-input" id="graph-name-input" placeholder="Enter graph name" value="<?php echo $graph ? htmlspecialchars($graph->getName()) : ''; ?>" required>
                                <div class="invalid-feedback">Graph name is required</div>
                            </div>
                            <div class="graph-description-wrapper">
                                <label class="graph-description-label" for="graph-description-input">Description</label>
                                <textarea class="form-control graph-description-input" id="graph-description-input" placeholder="Enter graph description (optional)" rows="1"><?php echo $graph ? htmlspecialchars($graph->getDescription()) : ''; ?></textarea>
                            </div>
                            <div class="graph-categories-wrapper" id="graph-categories-wrapper">
                                <label class="graph-categories-label">Categories <span class="required">*</span></label>
                                <div class="category-chips-container" id="category-chips">
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <?php
                                            $isSelected = isset($selectedCategoryIds) && in_array($cat['wcid'], $selectedCategoryIds);
                                            $activeClass = $isSelected ? 'active' : '';
                                            $activeStyle = $isSelected ? 'background-color: ' . htmlspecialchars($cat['color']) . '; border-color: ' . htmlspecialchars($cat['color']) . '; color: #fff;' : '';
                                            ?>
                                            <button type="button"
                                                    class="btn category-chip <?php echo $activeClass; ?>"
                                                    data-category-id="<?php echo $cat['wcid']; ?>"
                                                    data-color="<?php echo htmlspecialchars($cat['color']); ?>"
                                                    style="<?php echo $activeStyle; ?>">
                                                <?php if (!empty($cat['icon'])): ?>
                                                    <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">No categories available</p>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="selected-categories" name="categories" value="<?php echo isset($selectedCategoryIds) ? htmlspecialchars(json_encode($selectedCategoryIds)) : '[]'; ?>">
                                <div class="invalid-feedback" id="categories-error">At least one category is required</div>
                            </div>
                        </div>
                    </form>
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
                        <div class="chart-type-selector" role="radiogroup" aria-label="Chart type">
                            <div class="chart-type-scroll">
                                <div class="chart-type-item <?php echo (!$graph || $graph->getGraphType() === 'bar') ? 'active' : ''; ?>" data-type="bar" title="Bar Chart" tabindex="0" role="radio" aria-checked="<?php echo (!$graph || $graph->getGraphType() === 'bar') ? 'true' : 'false'; ?>">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Bar</span>
                                </div>
                                <div class="chart-type-item <?php echo ($graph && $graph->getGraphType() === 'line') ? 'active' : ''; ?>" data-type="line" title="Line Chart" tabindex="0" role="radio" aria-checked="<?php echo ($graph && $graph->getGraphType() === 'line') ? 'true' : 'false'; ?>">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Line</span>
                                </div>
                                <div class="chart-type-item <?php echo ($graph && $graph->getGraphType() === 'pie') ? 'active' : ''; ?>" data-type="pie" title="Pie Chart" tabindex="0" role="radio" aria-checked="<?php echo ($graph && $graph->getGraphType() === 'pie') ? 'true' : 'false'; ?>">
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
                                            $filterKeyDisplay = '::' . $filterKeyClean;
                                            $filterType = $filter['filter_type'];
                                            $isDateRangeSelector = in_array($filterType, array('date_range', 'main_datepicker'));
                                            $isMandatory = isset($mandatoryFilterKeys) && in_array($filterKeyClean, $mandatoryFilterKeys);
                                            ?>
                                            <div class="filter-selector-item<?php echo $isMandatory ? ' mandatory' : ''; ?>" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>" data-mandatory="<?php echo $isMandatory ? '1' : '0'; ?>">
                                                <?php if ($isMandatory): ?>
                                                    <input type="hidden" class="filter-selector-checkbox" value="<?php echo htmlspecialchars($filterKeyClean); ?>" data-checked="true">
                                                    <div class="mandatory-filter-display">
                                                        <i class="fas fa-lock mandatory-icon"></i>
                                                        <span class="filter-selector-label"><?php echo htmlspecialchars($filter['filter_label']); ?></span>
                                                        <span class="mandatory-badge">Mandatory</span>
                                                    </div>
                                                <?php else: ?>
                                                    <label class="dgc-checkbox filter-selector-checkbox-wrapper">
                                                        <input type="checkbox" class="filter-selector-checkbox" value="<?php echo htmlspecialchars($filterKeyClean); ?>" id="filter-<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                        <span class="filter-selector-label"><?php echo htmlspecialchars($filter['filter_label']); ?></span>
                                                    </label>
                                                <?php endif; ?>
                                                <div class="filter-selector-keys">
                                                    <?php if ($isDateRangeSelector): ?>
                                                        <code class="placeholder-key">::<?php echo htmlspecialchars($filterKeyClean); ?>_from</code>
                                                        <code class="placeholder-key">::<?php echo htmlspecialchars($filterKeyClean); ?>_to</code>
                                                    <?php else: ?>
                                                        <code class="placeholder-key"><?php echo htmlspecialchars($filterKeyDisplay); ?></code>
                                                    <?php endif; ?>
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
                                            // Get filter options from the already-loaded data (options is included in toArray())
                                            $options = isset($filter['options']) ? $filter['options'] : array();
                                            $filterType = $filter['filter_type'];
                                            $filterKey = $filter['filter_key'];
                                            $filterKeyClean = ltrim($filterKey, ':');
                                            $filterKeyDisplay = '::' . $filterKeyClean;
                                            $defaultValue = isset($filter['default_value']) ? $filter['default_value'] : '';
                                            // Get filter config for inline display
                                            $filterConfig = isset($filter['filter_config']) ? $filter['filter_config'] : '';
                                            $filterConfigArr = $filterConfig ? json_decode($filterConfig, true) : array();
                                            $isInline = isset($filterConfigArr['inline']) && $filterConfigArr['inline'];
                                            $isMandatoryFilter = isset($mandatoryFilterKeys) && in_array($filterKeyClean, $mandatoryFilterKeys);
                                            ?>
                                            <?php
                                            $isDateRange = in_array($filterType, array('date_range', 'main_datepicker'));
                                            ?>
                                            <div class="filter-input-item<?php echo $isMandatoryFilter ? ' mandatory' : ''; ?>" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>" data-filter-type="<?php echo htmlspecialchars($filterType); ?>" data-mandatory="<?php echo $isMandatoryFilter ? '1' : '0'; ?>" style="display: none;">
                                                <div class="filter-input-header">
                                                    <label class="filter-input-label"><?php echo htmlspecialchars($filter['filter_label']); ?></label>
                                                    <?php if ($isMandatoryFilter): ?>
                                                        <span class="mandatory-badge">Mandatory</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="filter-placeholders">
                                                    <?php if ($isDateRange): ?>
                                                        <code class="placeholder-key">::<?php echo htmlspecialchars($filterKeyClean); ?>_from</code>
                                                        <code class="placeholder-key">::<?php echo htmlspecialchars($filterKeyClean); ?>_to</code>
                                                    <?php else: ?>
                                                        <code class="placeholder-key"><?php echo htmlspecialchars($filterKeyDisplay); ?></code>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if ($filterType === 'select'):
                                                    // Find selected option for placeholder
                                                    $selectedLabel = '-- Select --';
                                                    foreach ($options as $opt) {
                                                        $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                                                        $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                                                        if ($value == $defaultValue) {
                                                            $selectedLabel = $label;
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                    <!-- Searchable dropdown with radio buttons -->
                                                    <div class="dropdown filter-select-dropdown" data-filter-name="<?php echo htmlspecialchars($filterKeyClean); ?>">
                                                        <button class="btn btn-outline-secondary dropdown-toggle filter-select-trigger btn-sm" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                            <span class="filter-select-placeholder"><?php echo htmlspecialchars($selectedLabel); ?></span>
                                                        </button>
                                                        <div class="dropdown-menu filter-select-options">
                                                            <div class="filter-select-header">
                                                                <input type="text" class="form-control form-control-sm select-search" placeholder="Search...">
                                                            </div>
                                                            <div class="dropdown-item filter-select-option" data-value="">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="" id="select-<?php echo htmlspecialchars($filterKeyClean); ?>-none"<?php echo empty($defaultValue) ? ' checked' : ''; ?>>
                                                                    <label class="form-check-label" for="select-<?php echo htmlspecialchars($filterKeyClean); ?>-none">-- Select --</label>
                                                                </div>
                                                            </div>
                                                            <?php foreach ($options as $index => $opt):
                                                                $value = is_array($opt) ? (isset($opt['value']) ? $opt['value'] : $opt[0]) : $opt;
                                                                $label = is_array($opt) ? (isset($opt['label']) ? $opt['label'] : (isset($opt[1]) ? $opt[1] : $value)) : $opt;
                                                                $isSelected = ($value == $defaultValue);
                                                                $optId = 'select-' . $filterKeyClean . '-' . $index;
                                                            ?>
                                                                <div class="dropdown-item filter-select-option" data-value="<?php echo htmlspecialchars($value); ?>">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($value); ?>" id="<?php echo $optId; ?>"<?php echo $isSelected ? ' checked' : ''; ?>>
                                                                        <label class="form-check-label" for="<?php echo $optId; ?>"><?php echo htmlspecialchars($label); ?></label>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <input type="hidden" class="filter-input" name="<?php echo htmlspecialchars($filterKeyClean); ?>" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($defaultValue); ?>">
                                                    </div>

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

                                                <?php elseif ($filterType === 'main_datepicker'): ?>
                                                    <input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="<?php echo htmlspecialchars($filterKeyClean); ?>" data-picker-type="main" placeholder="Select date range" autocomplete="off">

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
                                <a href="?urlq=data-filter/create" class="btn btn-sm btn-outline-secondary filters-manage-btn">
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
                        <!-- Export dropdown rendered by GraphExporter.js -->
                        <div id="export-chart-container"></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-preview">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="graph-preview-container">
                    <?php if ($graph): ?>
                        <?php echo DGCHelper::renderChartSkeleton($graph->getGraphType()); ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Data Mapping Section -->
            <div class="split-row data-mapping-row">
                <div class="split-col">
                    <div class="data-mapper"></div>
                </div>
                <div class="split-col sample-data-col" style="display: none;">
                    <div class="sample-data-container"></div>
                </div>
            </div>

            <!-- SQL Query & Placeholder Settings Row -->
            <div class="split-row">
                <!-- SQL Query Section with Bootstrap Tabs -->
                <div class="graph-section split-col query-section">
                    <ul class="nav nav-tabs dgc-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="sql-query-tab" data-bs-toggle="tab" data-bs-target="#sql-query-pane" type="button" role="tab">
                                <i class="fas fa-database"></i> SQL Query
                            </button>
                        </li>
                        <li class="nav-item" role="presentation" id="test-query-tab-item" style="display: none;">
                            <button class="nav-link" id="test-query-tab" data-bs-toggle="tab" data-bs-target="#test-query-pane" type="button" role="tab">
                                <i class="fas fa-play-circle"></i> Generated Query
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content dgc-tab-content">
                        <!-- SQL Query Tab -->
                        <div class="tab-pane fade show active" id="sql-query-pane" role="tabpanel">
                            <div class="query-builder" data-mandatory-filters="<?php echo htmlspecialchars(json_encode(isset($mandatoryFilters) ? $mandatoryFilters : array())); ?>">
                                <textarea class="query-editor" placeholder="SELECT category, SUM(amount) as total FROM sales WHERE date >= :date_from GROUP BY category"><?php echo $graph ? htmlspecialchars($graph->getQuery()) : ''; ?></textarea>
                                <!-- Copy, Format, Test buttons are generated by CodeMirrorEditor -->
                                <div class="query-test-result" style="display: none;"></div>
                            </div>
                        </div>
                        <!-- Test Query Tab (populated by JavaScript after test) -->
                        <div class="tab-pane fade" id="test-query-pane" role="tabpanel">
                            <div class="test-query-content"></div>
                        </div>
                    </div>
                </div>

                <!-- Placeholder Settings Section -->
                <div class="graph-section split-col placeholder-settings-section" style="display: none;">
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
            </div>
        </div>

    </div>
</div>
