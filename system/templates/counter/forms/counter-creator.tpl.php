
<?php
// Set company start date for datepicker presets
echo DGCHelper::renderCompanyStartDateScript();

// Get counter config
$config = $counter ? $counter->getConfigArray() : array();
$defaultConfig = WidgetCounter::getDefaultConfig();
$icon = isset($config['icon']) && $config['icon'] ? $config['icon'] : $defaultConfig['icon'];
$color = isset($config['color']) && $config['color'] ? $config['color'] : $defaultConfig['color'];
$format = isset($config['format']) && $config['format'] ? $config['format'] : $defaultConfig['format'];
$prefix = isset($config['prefix']) ? $config['prefix'] : $defaultConfig['prefix'];
$suffix = isset($config['suffix']) ? $config['suffix'] : $defaultConfig['suffix'];
$decimals = isset($config['decimals']) ? $config['decimals'] : $defaultConfig['decimals'];

// Operations
$rightContent = '<div class="status-indicators"></div>';
$saveButtonClass = $counter ? 'btn-outline-warning' : 'btn-primary';
$rightContent .= '<button type="button" class="btn ' . $saveButtonClass . ' btn-sm save-counter-btn" data-save-btn><i class="fas fa-save"></i> ' . ($counter ? 'Save' : 'Create Counter') . '</button>';
if ($counter) {
    $rightContent .= '<a href="?urlq=widget-counter/view/' . $counter->getId() . '" class="btn btn-icon btn-outline-primary btn-view-mode" data-bs-toggle="tooltip" data-bs-placement="bottom" title="View Mode"><i class="fas fa-eye"></i></a>';
    $rightContent .= '<a href="?urlq=widget-counter/create" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create New Counter"><i class="fas fa-plus"></i></a>';
}
// Navigation links
$rightContent .= '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Home"><i class="fas fa-home"></i></a>';
if (DGCHelper::hasAdminAccess()) {
    $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
    $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
}
// Widget dropdown
$leftContent = DGCHelper::renderWidgetDropdown('counter');
echo DGCHelper::renderPageHeader([
    'title' => $counter ? 'Edit Counter' : 'Create Counter',
    'backUrl' => '?urlq=widget-counter',
    'backLabel' => 'Counters',
    'leftContent' => $leftContent,
    'rightContent' => $rightContent
]);
?>

<div class="container container-full">
    <div id="counter-creator" class="graph-creator graph-creator-single-sidebar"
         data-counter-id="<?php echo $counter ? $counter->getId() : ''; ?>"
         data-counter-config="<?php echo $counter ? htmlspecialchars($counter->getConfig()) : ''; ?>"
         data-mandatory-filters="<?php echo htmlspecialchars(json_encode(isset($mandatoryFilterKeys) ? $mandatoryFilterKeys : array())); ?>"
         data-widget-type="counter">

        <!-- Left Sidebar - Counter Info, Config & Filters -->
        <div class="graph-sidebar graph-sidebar-left" id="counter-sidebar-left">
            <div class="sidebar-card" id="counter-sidebar-card">
                <!-- Immediately apply saved collapse state to prevent flash -->
                <script>
                    (function() {
                        if (localStorage.getItem('counterCreatorSidebarCollapsed') === 'true') {
                            document.getElementById('counter-sidebar-left').classList.add('collapsed');
                            document.getElementById('counter-sidebar-card').classList.add('collapsed');
                        }
                    })();
                </script>

                <!-- Sidebar Header with Title and Collapse Button -->
                <div class="sidebar-card-header" data-toggle="collapse">
                    <h3 class="sidebar-card-title"><i class="fas fa-cog"></i> Counter Settings</h3>
                    <button type="button" class="collapse-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>

                <!-- Counter Info Section -->
                <div class="sidebar-section graph-info-section">
                    <form id="counter-form">
                        <input type="hidden" id="counter-id" value="<?php echo $counter ? $counter->getId() : ''; ?>">
                        <div class="graph-info-wrapper">
                            <div class="graph-name-wrapper">
                                <label class="graph-name-label" for="counter-name-input">Counter Name <span class="required">*</span></label>
                                <input type="text" class="form-control graph-name-input" id="counter-name-input" placeholder="Enter counter name" value="<?php echo $counter ? htmlspecialchars($counter->getName()) : ''; ?>" required>
                                <div class="invalid-feedback">Counter name is required</div>
                            </div>
                            <div class="graph-description-wrapper">
                                <label class="graph-description-label" for="counter-description-input">Description</label>
                                <textarea class="form-control graph-description-input" id="counter-description-input" placeholder="Enter counter description (optional)" rows="1"><?php echo $counter ? htmlspecialchars($counter->getDescription()) : ''; ?></textarea>
                            </div>
                            <div class="graph-categories-wrapper" id="counter-categories-wrapper">
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
                        <!-- Icon Selector -->
                        <div class="config-section">
                            <label class="config-label">Icon</label>
                            <div class="icon-selector" id="icon-selector">
                                <button type="button" class="btn btn-outline-secondary icon-selector-btn" data-bs-toggle="modal" data-bs-target="#icon-picker-modal">
                                    <span class="material-icons selected-icon"><?php echo htmlspecialchars($icon); ?></span>
                                    <span class="icon-selector-text">Select Icon</span>
                                </button>
                                <input type="hidden" id="selected-icon" value="<?php echo htmlspecialchars($icon); ?>">
                            </div>
                        </div>

                        <!-- Color Picker -->
                        <div class="config-section">
                            <label class="config-label">Background Color</label>
                            <div class="color-swatches" id="color-swatches">
                                <?php
                                $defaultColors = array(
                                    '#4CAF50', // Green
                                    '#2196F3', // Blue
                                    '#9C27B0', // Purple
                                    '#F44336', // Red
                                    '#FF9800', // Orange
                                    '#00BCD4', // Cyan
                                    '#E91E63', // Pink
                                    '#3F51B5', // Indigo
                                    '#795548', // Brown
                                    '#607D8B'  // Blue Grey
                                );
                                $isCustomColor = !in_array(strtoupper($color), array_map('strtoupper', $defaultColors));
                                foreach ($defaultColors as $swatchColor):
                                    $isActive = strtoupper($color) === strtoupper($swatchColor);
                                ?>
                                <button type="button" class="color-swatch<?php echo $isActive ? ' active' : ''; ?>"
                                        data-color="<?php echo $swatchColor; ?>"
                                        style="background-color: <?php echo $swatchColor; ?>;"
                                        title="<?php echo $swatchColor; ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="custom-color-wrapper">
                                <label class="custom-color-label<?php echo $isCustomColor ? ' active' : ''; ?>">
                                    <input type="color" class="custom-color-input" id="counter-color" value="<?php echo htmlspecialchars($color); ?>">
                                    <span class="custom-color-text">Custom</span>
                                </label>
                            </div>
                        </div>

                        <!-- Format Options -->
                        <div class="config-section">
                            <label class="config-label">Number Format</label>
                            <select class="form-select form-select-sm" id="counter-format">
                                <?php foreach ($formatOptions as $opt): ?>
                                    <option value="<?php echo htmlspecialchars($opt['value']); ?>" <?php echo $format === $opt['value'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($opt['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Prefix/Suffix -->
                        <div class="config-section">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="config-label">Prefix</label>
                                    <input type="text" class="form-control form-control-sm" id="counter-prefix" value="<?php echo htmlspecialchars($prefix); ?>" placeholder="e.g., $">
                                </div>
                                <div class="col-6">
                                    <label class="config-label">Suffix</label>
                                    <input type="text" class="form-control form-control-sm" id="counter-suffix" value="<?php echo htmlspecialchars($suffix); ?>" placeholder="e.g., %">
                                </div>
                            </div>
                        </div>

                        <!-- Decimals -->
                        <div class="config-section">
                            <label class="config-label">Decimal Places</label>
                            <input type="number" class="form-control form-control-sm" id="counter-decimals" value="<?php echo intval($decimals); ?>" min="0" max="10">
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
                                    <div class="filters-list" id="counter-filters">
                                        <?php foreach ($allFilters as $filter): ?>
                                            <?php
                                            $options = isset($filter['options']) ? $filter['options'] : array();
                                            $filterType = $filter['filter_type'];
                                            $filterKey = $filter['filter_key'];
                                            $filterKeyClean = ltrim($filterKey, ':');
                                            $filterKeyDisplay = '::' . $filterKeyClean;
                                            $defaultValueRaw = isset($filter['default_value']) ? $filter['default_value'] : '';
                                            // Parse JSON default value if applicable
                                            $defaultValueDecoded = $defaultValueRaw ? json_decode($defaultValueRaw, true) : null;
                                            // Extract actual value(s) from JSON structure
                                            $defaultValue = '';
                                            $defaultValues = array();
                                            if (is_array($defaultValueDecoded)) {
                                                if (isset($defaultValueDecoded['value'])) {
                                                    $defaultValue = $defaultValueDecoded['value'];
                                                }
                                                if (isset($defaultValueDecoded['values']) && is_array($defaultValueDecoded['values'])) {
                                                    $defaultValues = $defaultValueDecoded['values'];
                                                }
                                            } elseif ($defaultValueRaw && json_last_error() !== JSON_ERROR_NONE) {
                                                // Not JSON, use as-is (legacy support)
                                                $defaultValue = $defaultValueRaw;
                                            }
                                            $filterConfig = isset($filter['filter_config']) ? $filter['filter_config'] : '';
                                            $filterConfigArr = $filterConfig ? json_decode($filterConfig, true) : array();
                                            $isInline = isset($filterConfigArr['inline']) && $filterConfigArr['inline'];
                                            $isMandatoryFilter = isset($mandatoryFilterKeys) && in_array($filterKeyClean, $mandatoryFilterKeys);
                                            $isDateRange = in_array($filterType, array('date_range', 'main_datepicker'));
                                            $isRequired = isset($filter['is_required']) && $filter['is_required'];
                                            ?>
                                            <div class="filter-input-item<?php echo $isMandatoryFilter ? ' mandatory' : ''; ?>" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>" data-filter-type="<?php echo htmlspecialchars($filterType); ?>" data-mandatory="<?php echo $isMandatoryFilter ? '1' : '0'; ?>" data-is-required="<?php echo $isRequired ? '1' : '0'; ?>" style="display: none;">
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
                                                    <?php
                                                    $dateRangeAttrs = '';
                                                    if (is_array($defaultValueDecoded) && isset($defaultValueDecoded['mode'])) {
                                                        if ($defaultValueDecoded['mode'] === 'preset' && !empty($defaultValueDecoded['preset'])) {
                                                            $dateRangeAttrs = ' data-default-preset="' . htmlspecialchars($defaultValueDecoded['preset']) . '"';
                                                        } elseif ($defaultValueDecoded['mode'] === 'specific') {
                                                            if (!empty($defaultValueDecoded['from'])) {
                                                                $dateRangeAttrs .= ' data-default-from="' . htmlspecialchars($defaultValueDecoded['from']) . '"';
                                                            }
                                                            if (!empty($defaultValueDecoded['to'])) {
                                                                $dateRangeAttrs .= ' data-default-to="' . htmlspecialchars($defaultValueDecoded['to']) . '"';
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="<?php echo htmlspecialchars($filterKeyClean); ?>" data-picker-type="range" placeholder="Select date range" autocomplete="off"<?php echo $dateRangeAttrs; ?>>

                                                <?php elseif ($filterType === 'main_datepicker'): ?>
                                                    <?php
                                                    $mainDateAttrs = '';
                                                    if (is_array($defaultValueDecoded) && isset($defaultValueDecoded['mode'])) {
                                                        if ($defaultValueDecoded['mode'] === 'preset' && !empty($defaultValueDecoded['preset'])) {
                                                            $mainDateAttrs = ' data-default-preset="' . htmlspecialchars($defaultValueDecoded['preset']) . '"';
                                                        } elseif ($defaultValueDecoded['mode'] === 'specific') {
                                                            if (!empty($defaultValueDecoded['from'])) {
                                                                $mainDateAttrs .= ' data-default-from="' . htmlspecialchars($defaultValueDecoded['from']) . '"';
                                                            }
                                                            if (!empty($defaultValueDecoded['to'])) {
                                                                $mainDateAttrs .= ' data-default-to="' . htmlspecialchars($defaultValueDecoded['to']) . '"';
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <input type="text" class="form-control form-control-sm filter-input dgc-datepicker" name="<?php echo htmlspecialchars($filterKeyClean); ?>" data-picker-type="main" placeholder="Select date range" autocomplete="off"<?php echo $mainDateAttrs; ?>>

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
                    <h3><i class="fas fa-tachometer-alt"></i> Preview</h3>
                    <div class="graph-preview-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-preview">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="graph-preview-container counter-preview-container">
                    <div class="counter-card-preview<?php echo $counter ? ' is-loading' : ''; ?>" id="counter-card-preview" style="background-color: <?php echo htmlspecialchars($color); ?>;">
                        <div class="counter-icon">
                            <span class="material-icons<?php echo $counter ? ' counter-skeleton-icon' : ''; ?>" id="preview-icon"><?php echo htmlspecialchars($icon); ?></span>
                        </div>
                        <div class="counter-content">
                            <div class="counter-value" id="preview-value"><?php echo $counter ? '<span class="counter-skeleton-value"></span>' : '--'; ?></div>
                            <div class="counter-name" id="preview-name"><?php echo $counter ? htmlspecialchars($counter->getName()) : 'Counter Name'; ?></div>
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
                                <textarea class="query-editor" placeholder="SELECT COUNT(*) as counter FROM orders WHERE date >= ::date_from AND date <= ::date_to"><?php echo $counter ? htmlspecialchars($counter->getQuery()) : ''; ?></textarea>
                                <div class="query-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Query must return a single row. Use <code>counter</code> as the column alias for the value.
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

<!-- Icon Picker Modal -->
<div class="modal fade" id="icon-picker-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="icon-search-wrapper">
                    <input type="text" class="form-control" id="icon-search" placeholder="Search icons...">
                </div>
                <div class="icon-grid" id="icon-grid">
                    <!-- Icons populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>
