
<?php
// Set company start date for datepicker presets
echo DGCHelper::renderCompanyStartDateScript();

// Get table config
$config = $table ? $table->getConfigArray() : array();
$defaultConfig = WidgetTable::getDefaultConfig();

// Pagination settings
$paginationConfig = isset($config['pagination']) ? $config['pagination'] : $defaultConfig['pagination'];
$rowsPerPage = isset($paginationConfig['rowsPerPage']) ? $paginationConfig['rowsPerPage'] : 10;

// Style settings
$styleConfig = isset($config['style']) ? $config['style'] : $defaultConfig['style'];
$striped = isset($styleConfig['striped']) ? $styleConfig['striped'] : true;
$bordered = isset($styleConfig['bordered']) ? $styleConfig['bordered'] : true;
$hover = isset($styleConfig['hover']) ? $styleConfig['hover'] : true;
$density = isset($styleConfig['density']) ? $styleConfig['density'] : 'comfortable';

// Operations
$rightContent = '<div class="status-indicators"></div>';
$saveButtonClass = $table ? 'btn-outline-warning' : 'btn-primary';
$rightContent .= '<button type="button" class="btn ' . $saveButtonClass . ' btn-sm save-table-btn" data-save-btn><i class="fas fa-save"></i> ' . ($table ? 'Save' : 'Create Table') . '</button>';
if ($table) {
    $rightContent .= '<a href="?urlq=widget-table/view/' . $table->getId() . '" class="btn btn-icon btn-outline-primary btn-view-mode" data-bs-toggle="tooltip" data-bs-placement="bottom" title="View Mode"><i class="fas fa-eye"></i></a>';
    $rightContent .= '<a href="?urlq=widget-table/create" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create New Table"><i class="fas fa-plus"></i></a>';
}
// Navigation links
$rightContent .= '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Home"><i class="fas fa-home"></i></a>';
if (DGCHelper::hasAdminAccess()) {
    $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
    $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
}
// Widget dropdown
$leftContent = DGCHelper::renderWidgetDropdown('table');
echo DGCHelper::renderPageHeader([
    'title' => $table ? 'Edit Table' : 'Create Table',
    'backUrl' => '?urlq=widget-table',
    'backLabel' => 'Tables',
    'leftContent' => $leftContent,
    'rightContent' => $rightContent
]);
?>

<div class="container container-full">
    <div id="table-creator" class="graph-creator graph-creator-single-sidebar"
         data-table-id="<?php echo $table ? $table->getId() : ''; ?>"
         data-table-config="<?php echo $table ? htmlspecialchars($table->getConfig()) : ''; ?>"
         data-mandatory-filters="<?php echo htmlspecialchars(json_encode(isset($mandatoryFilterKeys) ? $mandatoryFilterKeys : array())); ?>"
         data-widget-type="table">

        <!-- Left Sidebar - Table Info, Config & Filters -->
        <div class="graph-sidebar graph-sidebar-left" id="table-sidebar-left">
            <div class="sidebar-card" id="table-sidebar-card">
                <!-- Immediately apply saved collapse state to prevent flash -->
                <script>
                    (function() {
                        if (localStorage.getItem('tableCreatorSidebarCollapsed') === 'true') {
                            document.getElementById('table-sidebar-left').classList.add('collapsed');
                            document.getElementById('table-sidebar-card').classList.add('collapsed');
                        }
                    })();
                </script>

                <!-- Sidebar Header with Title and Collapse Button -->
                <div class="sidebar-card-header" data-toggle="collapse">
                    <h3 class="sidebar-card-title"><i class="fas fa-cog"></i> Table Settings</h3>
                    <button type="button" class="collapse-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>

                <!-- Table Info Section -->
                <div class="sidebar-section graph-info-section">
                    <form id="table-form">
                        <input type="hidden" id="table-id" value="<?php echo $table ? $table->getId() : ''; ?>">
                        <div class="graph-info-wrapper">
                            <div class="graph-name-wrapper">
                                <label class="graph-name-label" for="table-name-input">Table Name <span class="required">*</span></label>
                                <input type="text" class="form-control graph-name-input" id="table-name-input" placeholder="Enter table name" value="<?php echo $table ? htmlspecialchars($table->getName()) : ''; ?>" required>
                                <div class="invalid-feedback">Table name is required</div>
                            </div>
                            <div class="graph-description-wrapper">
                                <label class="graph-description-label" for="table-description-input">Description</label>
                                <textarea class="form-control graph-description-input" id="table-description-input" placeholder="Enter table description (optional)" rows="1"><?php echo $table ? htmlspecialchars($table->getDescription()) : ''; ?></textarea>
                            </div>
                            <div class="graph-categories-wrapper" id="table-categories-wrapper">
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
                            <i class="fas fa-palette"></i> Design
                        </button>
                        <button class="sidebar-tab" data-tab="filters">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>

                    <!-- Design Tab Content -->
                    <div class="sidebar-tab-content active" id="sidebar-tab-config">
                        <!-- Rows Per Page -->
                        <div class="config-section">
                            <label class="config-label">Rows per page</label>
                            <select class="form-select form-select-sm" id="rows-per-page">
                                <?php foreach ($rowsPerPageOptions as $opt): ?>
                                    <option value="<?php echo $opt['value']; ?>" <?php echo $rowsPerPage == $opt['value'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($opt['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Style Options -->
                        <div class="config-section">
                            <label class="config-label">Table Style</label>
                            <div class="dgc-checkbox-group">
                                <label class="dgc-checkbox">
                                    <input type="checkbox" id="style-striped" <?php echo $striped ? 'checked' : ''; ?>>
                                    <span>Striped rows</span>
                                </label>
                                <label class="dgc-checkbox">
                                    <input type="checkbox" id="style-bordered" <?php echo $bordered ? 'checked' : ''; ?>>
                                    <span>Bordered</span>
                                </label>
                                <label class="dgc-checkbox">
                                    <input type="checkbox" id="style-hover" <?php echo $hover ? 'checked' : ''; ?>>
                                    <span>Row hover effect</span>
                                </label>
                            </div>
                        </div>

                        <!-- Density -->
                        <div class="config-section">
                            <label class="config-label">Density</label>
                            <div class="dgc-radio-group">
                                <?php foreach ($densityOptions as $opt): ?>
                                    <label class="dgc-radio">
                                        <input type="radio" name="density" id="density-<?php echo $opt['value']; ?>" value="<?php echo $opt['value']; ?>" <?php echo $density === $opt['value'] ? 'checked' : ''; ?>>
                                        <span><?php echo htmlspecialchars($opt['label']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
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
                                    <div class="filters-list" id="table-filters">
                                        <?php foreach ($allFilters as $filter): ?>
                                            <?php
                                            $options = isset($filter['options']) ? $filter['options'] : array();
                                            $filterType = $filter['filter_type'];
                                            $filterKey = $filter['filter_key'];
                                            $filterKeyClean = ltrim($filterKey, ':');
                                            $filterKeyDisplay = '::' . $filterKeyClean;
                                            $defaultValue = isset($filter['default_value']) ? $filter['default_value'] : '';
                                            $filterConfig = isset($filter['filter_config']) ? $filter['filter_config'] : '';
                                            $filterConfigArr = $filterConfig ? json_decode($filterConfig, true) : array();
                                            $isInline = isset($filterConfigArr['inline']) && $filterConfigArr['inline'];
                                            $isMandatoryFilter = isset($mandatoryFilterKeys) && in_array($filterKeyClean, $mandatoryFilterKeys);
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

                                                <?php if ($filterType === 'select'): ?>
                                                    <?php
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

                                                <?php elseif ($filterType === 'date'): ?>
                                                    <input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($defaultValue); ?>" data-picker-type="single" placeholder="Select date" autocomplete="off">

                                                <?php elseif ($filterType === 'date_range'): ?>
                                                    <input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="<?php echo htmlspecialchars($filterKeyClean); ?>" data-picker-type="range" placeholder="Select date range" autocomplete="off">

                                                <?php elseif ($filterType === 'main_datepicker'): ?>
                                                    <input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="<?php echo htmlspecialchars($filterKeyClean); ?>" data-picker-type="main" placeholder="Select date range" autocomplete="off">

                                                <?php elseif ($filterType === 'number'): ?>
                                                    <input type="number" class="form-control form-control-sm filter-input" name="<?php echo htmlspecialchars($filterKeyClean); ?>" value="<?php echo htmlspecialchars($defaultValue); ?>" placeholder="Enter number">

                                                <?php else: ?>
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
                    <h3><i class="fas fa-table"></i> Preview</h3>
                    <div class="graph-preview-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-preview">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="graph-preview-container table-preview-container">
                    <div class="table-preview-wrapper<?php echo $table ? ' is-loading' : ''; ?>" id="table-preview-wrapper">
                        <div class="table-preview-empty" id="table-preview-empty">
                            <i class="fas fa-table"></i>
                            <p>Run a query to see table preview</p>
                        </div>
                        <div class="table-preview-content" id="table-preview-content" style="display: none;">
                            <!-- Table will be rendered here by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Query Results Section (shown after testing query) -->
            <div class="graph-section sample-data-col" style="display: none;">
                <div class="sample-data-container"></div>
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
                                <textarea class="query-editor" placeholder="SELECT id, name, email, created_at FROM users WHERE created_at >= ::date_from AND created_at <= ::date_to"><?php echo $table ? htmlspecialchars($table->getQuery()) : ''; ?></textarea>
                                <div class="query-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Query should return multiple rows. Use placeholders like <code>::filter_key</code> for dynamic filtering.
                                </div>
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
