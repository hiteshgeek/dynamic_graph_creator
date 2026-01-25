<?php
// Set company start date for datepicker presets
echo DGCHelper::renderCompanyStartDateScript();

// Operations
$saveButtonClass = $filter ? 'btn-outline-warning' : 'btn-primary';
$rightContent = '<div class="status-indicators"></div>';
$rightContent .= '<button type="button" class="btn ' . $saveButtonClass . ' btn-sm save-filter-btn" data-save-btn><i class="fas fa-save"></i> ' . ($filter ? 'Save' : 'Create Data Filter') . '</button>';
// Navigation links
$rightContent .= '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Home"><i class="fas fa-home"></i></a>';
if (DGCHelper::hasAdminAccess()) {
    $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
}
// Widget dropdown
$leftContent = DGCHelper::renderWidgetDropdown();
echo DGCHelper::renderPageHeader([
    'title' => $filter ? 'Edit Data Filter' : 'Create Data Filter',
    'backUrl' => '?urlq=data-filter',
    'backLabel' => 'Data Filters',
    'leftContent' => $leftContent,
    'rightContent' => $rightContent
]);
?>

<div class="data-filter-form-layout">
    <!-- Sidebar with filter list -->
    <aside class="data-filter-form-sidebar" id="data-filter-sidebar">
        <div class="sidebar-card" id="data-filter-sidebar-card">
            <script>
                // Apply collapsed state immediately to prevent flash
                if (localStorage.getItem('dataFilterSidebarCollapsed') === 'true') {
                    document.getElementById('data-filter-sidebar').classList.add('collapsed');
                    document.getElementById('data-filter-sidebar-card').classList.add('collapsed');
                }
            </script>
            <div class="sidebar-card-header">
                <span class="sidebar-card-title">
                    <i class="fas fa-filter"></i>
                    <span>All Data Filters</span>
                </span>
                <span class="sidebar-count"><?php echo $totalFilters; ?></span>
                <button type="button" class="collapse-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
            <div class="filter-list-nav">
                <?php if ($totalFilters > 0): ?>
                    <?php
                    $typeIcons = array(
                        'text' => 'font',
                        'number' => 'hashtag',
                        'date' => 'calendar',
                        'date_range' => 'calendar-week',
                        'main_datepicker' => 'calendar-alt',
                        'select' => 'list',
                        'multi_select' => 'list-check',
                        'checkbox' => 'check-square',
                        'radio' => 'circle-dot',
                        'tokeninput' => 'tags'
                    );
                    foreach ($allFilters as $f):
                        $typeIcon = isset($typeIcons[$f['filter_type']]) ? $typeIcons[$f['filter_type']] : 'filter';
                    ?>
                        <a href="?urlq=data-filter/edit/<?php echo $f['dfid']; ?>"
                            class="filter-nav-item <?php echo ($filter && $f['dfid'] == $filter->getId()) ? 'active' : ''; ?>">
                            <span class="filter-nav-icon <?php echo $f['filter_type']; ?>">
                                <i class="fas fa-<?php echo $typeIcon; ?>"></i>
                            </span>
                            <span class="filter-nav-info">
                                <span class="filter-nav-name"><?php echo htmlspecialchars($f['filter_label']); ?></span>
                                <code class="placeholder-key"><?php echo htmlspecialchars($f['filter_key']); ?></code>
                            </span>
                            <span class="filter-nav-source <?php echo $f['data_source']; ?>">
                                <?php if ($f['data_source'] === 'query'): ?>
                                    <i class="fas fa-database"></i>
                                <?php else: ?>
                                    <i class="fas fa-list-ul"></i>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="sidebar-empty">
                        <i class="fas fa-filter"></i>
                        <span>No filters yet</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <main class="data-filter-form-main">
        <div class="data-filter-form-page" data-filter-id="<?php echo $filter ? $filter->getId() : ''; ?>">
            <div class="data-filter-form-content">
                <!-- Left Column - Form Fields -->
                <div class="data-filter-form-left">
                    <div class="card">
                        <div class="card-body">
                            <form id="filter-form">
                                <input type="hidden" id="filter-id" value="<?php echo $filter ? $filter->getId() : ''; ?>">

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="filter-key">Filter Key <span class="required">*</span></label>
                                        <?php
                                        // Strip :: prefix for display (will be auto-added on save)
                                        $filterKeyValue = $filter ? $filter->getFilterKey() : '';
                                        $filterKeyValue = ltrim($filterKeyValue, ':');
                                        ?>
                                        <input type="text" id="filter-key" class="form-control" placeholder="year" value="<?php echo htmlspecialchars($filterKeyValue); ?>" required>
                                        <div class="invalid-feedback">Only letters, numbers, and underscores allowed</div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="filter-label">Label <span class="required">*</span></label>
                                        <input type="text" id="filter-label" class="form-control" placeholder="Year" value="<?php echo $filter ? htmlspecialchars($filter->getFilterLabel()) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="filter-key-hints mb-3">
                                    <div class="filter-key-hint">Filter Key: Only letters, numbers, and underscores allowed.</div>
                                    <div class="filter-key-hint highlighted"><code>::</code> prefix will be added automatically.</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Filter Type <span class="required">*</span></label>
                                    <?php
                                    $filterTypes = array(
                                        'text' => array('label' => 'Text Input', 'icon' => 'font'),
                                        'number' => array('label' => 'Number Input', 'icon' => 'hashtag'),
                                        'date' => array('label' => 'Date Picker', 'icon' => 'calendar'),
                                        'date_range' => array('label' => 'Date Range', 'icon' => 'calendar-week'),
                                        'main_datepicker' => array('label' => 'Main Datepicker (with presets)', 'icon' => 'calendar-alt'),
                                        'select' => array('label' => 'Select', 'icon' => 'list'),
                                        'checkbox' => array('label' => 'Checkbox', 'icon' => 'check-square'),
                                        'radio' => array('label' => 'Radio Buttons', 'icon' => 'circle-dot'),
                                        'tokeninput' => array('label' => 'Token Input', 'icon' => 'tags')
                                    );
                                    $currentType = $filter ? $filter->getFilterType() : 'text';
                                    // Map multi_select back to select for display
                                    if ($currentType === 'multi_select') $currentType = 'select';
                                    $currentLabel = isset($filterTypes[$currentType]) ? $filterTypes[$currentType]['label'] : 'Text Input';
                                    $currentIcon = isset($filterTypes[$currentType]) ? $filterTypes[$currentType]['icon'] : 'font';
                                    ?>
                                    <div class="dropdown filter-select-dropdown filter-type-dropdown" data-filter-name="filter_type">
                                        <button class="btn btn-outline-secondary dropdown-toggle filter-select-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                            <span class="filter-select-placeholder has-selection">
                                                <i class="fas fa-<?php echo $currentIcon; ?> me-2"></i><?php echo htmlspecialchars($currentLabel); ?>
                                            </span>
                                        </button>
                                        <div class="dropdown-menu filter-select-options">
                                            <div class="filter-select-header">
                                                <input type="text" class="form-control form-control-sm select-search" placeholder="Search...">
                                            </div>
                                            <?php foreach ($filterTypes as $value => $typeInfo): ?>
                                                <div class="dropdown-item filter-select-option" data-value="<?php echo $value; ?>">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="filter_type" value="<?php echo $value; ?>" id="filter-type-<?php echo $value; ?>" <?php echo ($value === $currentType) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="filter-type-<?php echo $value; ?>">
                                                            <i class="fas fa-<?php echo $typeInfo['icon']; ?> me-2 text-muted"></i><?php echo htmlspecialchars($typeInfo['label']); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <input type="hidden" class="filter-input" id="filter-type" name="filter_type" data-filter-key="filter_type" value="<?php echo htmlspecialchars($currentType); ?>">
                                    </div>
                                </div>

                                <div id="date-range-info" class="alert alert-info" style="display: none;">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Date Range Placeholders:</strong> This filter creates two placeholders for use in SQL queries:
                                    <ul class="mb-0 mt-1">
                                        <li><code id="date-range-from-placeholder">::filter_key_from</code> - Start date</li>
                                        <li><code id="date-range-to-placeholder">::filter_key_to</code> - End date</li>
                                    </ul>
                                    <small class="text-muted">Example: <code>WHERE created_ts BETWEEN ::filter_key_from AND ::filter_key_to</code></small>
                                </div>

                                <?php
                                $isMultiSelect = $filter && $filter->getFilterType() === 'multi_select';
                                $showSelectConfig = $filter && in_array($filter->getFilterType(), array('select', 'multi_select'));
                                $filterConfig = $filter ? json_decode($filter->getFilterConfig(), true) : array();
                                $isInline = isset($filterConfig['inline']) && $filterConfig['inline'];
                                $showCheckboxRadioConfig = $filter && in_array($filter->getFilterType(), array('checkbox', 'radio'));
                                ?>
                                <div id="select-config-section" class="form-group" style="<?php echo $showSelectConfig ? '' : 'display: none;'; ?>">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="filter-multiple" <?php echo $isMultiSelect ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="filter-multiple">Allow multiple selection</label>
                                    </div>
                                    <small class="form-hint d-block mt-1">Enable users to select more than one option</small>
                                </div>

                                <div id="checkbox-radio-config-section" class="form-group" style="<?php echo $showCheckboxRadioConfig ? '' : 'display: none;'; ?>">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="filter-inline" <?php echo $isInline ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="filter-inline">Display inline</label>
                                    </div>
                                    <small class="form-hint d-block mt-1">Show options horizontally instead of stacked vertically</small>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="filter-required" <?php echo ($filter && $filter->getIsRequired()) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="filter-required">Required field</label>
                                    </div>
                                    <small class="form-hint d-block mt-1">Required filters must have a default value and cannot be emptied by users</small>
                                </div>

                                <!-- Default Value Section (always visible, required indicator shown when is_required is checked) -->
                                <?php
                                $isFilterRequired = $filter && $filter->getIsRequired();
                                $existingDefaultValue = $filter ? $filter->getDefaultValue() : '';
                                $decodedDefault = $existingDefaultValue ? json_decode($existingDefaultValue, true) : null;
                                ?>
                                <div id="default-value-section" class="form-group default-value-section"
                                     data-existing-value="<?php echo htmlspecialchars($existingDefaultValue); ?>">
                                    <label class="form-label">Default Value<?php if ($isFilterRequired): ?> <span class="required">*</span><?php endif; ?></label>
                                    <div id="default-value-content">
                                        <!-- Content populated by JavaScript based on filter type -->
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="filter-is-system" <?php echo ($filter && $filter->getIsSystem()) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="filter-is-system">
                                            <i class="fas fa-lock text-muted me-1"></i> System filter
                                        </label>
                                    </div>
                                    <small class="form-hint d-block mt-1">System filters can only be edited by admin users</small>
                                </div>

                                <?php if (!empty($widgetTypes)): ?>
                                    <div class="form-group">
                                        <label class="form-label">Mandatory for Widget Types</label>
                                        <small class="form-hint d-block mb-2">Select widget types where this filter is required in the query</small>
                                        <?php
                                        $mandatoryWidgetTypeIds = $filter ? $filter->getMandatoryWidgetTypeIds() : array();
                                        foreach ($widgetTypes as $wt):
                                        ?>
                                            <div class="form-check">
                                                <input class="form-check-input mandatory-widget-type" type="checkbox"
                                                    id="mandatory-wt-<?php echo $wt['wtid']; ?>"
                                                    value="<?php echo $wt['wtid']; ?>"
                                                    <?php echo in_array($wt['wtid'], $mandatoryWidgetTypeIds) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="mandatory-wt-<?php echo $wt['wtid']; ?>">
                                                    <i class="fas <?php echo $wt['icon']; ?> text-muted me-1"></i>
                                                    <?php echo htmlspecialchars($wt['name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Info: Required vs Mandatory -->
                                <div class="filter-settings-info">
                                    <div class="info-header" data-bs-toggle="collapse" data-bs-target="#required-vs-mandatory-info">
                                        <i class="fas fa-info-circle text-info"></i>
                                        <span>Required vs Mandatory</span>
                                        <i class="fas fa-chevron-down info-toggle-icon"></i>
                                    </div>
                                    <div class="collapse" id="required-vs-mandatory-info">
                                        <div class="info-content">
                                            <div class="info-item">
                                                <strong><i class="fas fa-asterisk text-danger"></i> Required Field</strong>
                                                <p>Controls behavior at <em>runtime</em>. Users cannot leave this filter empty - if cleared, it reverts to the default value.</p>
                                            </div>
                                            <div class="info-item">
                                                <strong><i class="fas fa-lock text-warning"></i> Mandatory for Widget Types</strong>
                                                <p>Controls behavior at <em>design time</em>. The filter placeholder must be included in the widget's SQL query.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                $dataSource = $filter ? $filter->getDataSource() : 'static';
                                $typesWithOptions = array('select', 'checkbox', 'radio', 'tokeninput');
                                $currentType = $filter ? $filter->getFilterType() : 'text';
                                // multi_select is stored in DB but shown as 'select' in UI
                                $showDataSource = in_array($currentType, $typesWithOptions) || $currentType === 'multi_select';
                                ?>

                                <div id="data-source-section" style="<?php echo $showDataSource ? '' : 'display: none;'; ?>">
                                    <div class="section-header">
                                        <i class="fas fa-database"></i>
                                        <span>Data Source</span>
                                    </div>

                                    <div class="form-group">
                                        <div class="data-source-tabs">
                                            <button type="button" class="data-source-tab <?php echo $dataSource === 'static' ? 'active' : ''; ?>" data-source="static">
                                                <i class="fas fa-list"></i> Static Options
                                            </button>
                                            <button type="button" class="data-source-tab <?php echo $dataSource === 'query' ? 'active' : ''; ?>" data-source="query">
                                                <i class="fas fa-database"></i> SQL Query
                                            </button>
                                        </div>
                                        <input type="hidden" id="data-source" value="<?php echo $dataSource; ?>">
                                    </div>
                                </div>

                                <!-- Filter Preview Section -->
                                <div id="filter-preview-section" style="display: none;">
                                    <div class="section-header">
                                        <i class="fas fa-eye"></i>
                                        <span>Filter Preview</span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Data Source Options (visible when filter type has options) -->
                <div class="data-filter-form-right" id="data-source-panel" style="<?php echo $showDataSource ? '' : 'display: none;'; ?>">
                    <!-- Static Options Section -->
                    <div id="static-options-section" style="<?php echo $dataSource === 'static' ? '' : 'display: none;'; ?>">
                        <div class="card">
                            <div class="card-body">
                                <div class="section-header">
                                    <i class="fas fa-list"></i>
                                    <span>Static Options</span>
                                </div>
                                <div class="filter-options-list">
                                    <?php
                                    $staticOptions = array();
                                    if ($filter && $filter->getStaticOptions()) {
                                        $staticOptions = json_decode($filter->getStaticOptions(), true);
                                        if (!is_array($staticOptions)) $staticOptions = array();
                                    }
                                    if (!empty($staticOptions)):
                                        foreach ($staticOptions as $opt):
                                    ?>
                                            <div class="filter-option-item">
                                                <input type="text" class="form-control option-value" placeholder="Value" value="<?php echo htmlspecialchars($opt['value']); ?>">
                                                <input type="text" class="form-control option-label" placeholder="Label" value="<?php echo htmlspecialchars($opt['label']); ?>">
                                                <button type="button" class="btn btn-sm btn-outline-secondary remove-option-btn">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php
                                        endforeach;
                                    else:
                                        ?>
                                        <div class="filter-option-item">
                                            <input type="text" class="form-control option-value" placeholder="Value">
                                            <input type="text" class="form-control option-label" placeholder="Label">
                                            <button type="button" class="btn btn-sm btn-outline-secondary remove-option-btn">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="static-options-actions">
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-option-btn">
                                        <i class="fas fa-plus"></i> Add Option
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Query Options Section -->
                    <div id="query-options-section" style="<?php echo $dataSource === 'query' ? '' : 'display: none;'; ?>">
                        <div class="card">
                            <div class="card-body">
                                <!-- System Placeholders Reference -->
                                <?php if (!empty($systemPlaceholders)): ?>
                                    <div class="system-placeholders-panel" style="margin-top: 0; margin-bottom: 1rem;">
                                        <div class="system-placeholders-header">
                                            <i class="fas fa-code"></i>
                                            <span>System Placeholders</span>
                                            <div class="system-placeholders-search">
                                                <input type="text" class="form-control form-control-sm" id="system-placeholder-search" placeholder="Search placeholders...">
                                                <i class="fas fa-search"></i>
                                            </div>
                                        </div>
                                        <div class="system-placeholders-list" id="system-placeholders-list">
                                            <?php foreach ($systemPlaceholders as $sp): ?>
                                                <div class="system-placeholder-item"
                                                    data-placeholder="::<?php echo htmlspecialchars($sp['placeholder_key']); ?>"
                                                    data-label="<?php echo htmlspecialchars(strtolower($sp['placeholder_label'])); ?>"
                                                    data-key="<?php echo htmlspecialchars(strtolower($sp['placeholder_key'])); ?>"
                                                    data-description="<?php echo htmlspecialchars(strtolower($sp['description'])); ?>"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    data-bs-html="true"
                                                    title="<strong><?php echo htmlspecialchars($sp['placeholder_label']); ?></strong><br><?php echo htmlspecialchars($sp['description']); ?><br><em>Click to copy</em>">
                                                    <span class="placeholder-label"><?php echo htmlspecialchars($sp['placeholder_label']); ?></span>
                                                    <code>::<?php echo htmlspecialchars($sp['placeholder_key']); ?></code>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="system-placeholders-empty" id="system-placeholders-empty">No placeholders match your search</div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Query Tabs -->
                                <ul class="nav nav-tabs dgc-tabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="sql-query-tab" data-bs-toggle="tab" data-bs-target="#sql-query-pane" type="button" role="tab">
                                            <i class="fas fa-database"></i> SQL Query
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation" id="generated-query-tab-item" style="display: none;">
                                        <button class="nav-link" id="generated-query-tab" data-bs-toggle="tab" data-bs-target="#generated-query-pane" type="button" role="tab">
                                            <i class="fas fa-play-circle"></i> Generated Query
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content dgc-tab-content">
                                    <!-- SQL Query Tab -->
                                    <div class="tab-pane fade show active" id="sql-query-pane" role="tabpanel">
                                        <div class="query-builder">
                                            <textarea id="data-query" class="query-editor" rows="4" placeholder="SELECT id as value, name as label, 1 as is_selected FROM categories WHERE status = 1 ORDER BY name"><?php echo $filter ? htmlspecialchars($filter->getDataQuery()) : ''; ?></textarea>
                                            <!-- Copy, Format, Test buttons and hint are generated by CodeMirrorEditor -->
                                        </div>
                                    </div>
                                    <!-- Generated Query Tab (populated by JavaScript after test) -->
                                    <div class="tab-pane fade" id="generated-query-pane" role="tabpanel">
                                        <div class="generated-query-content">
                                            <textarea id="generated-query-code" class="query-editor" readonly></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Query Result -->
                                <div id="query-result" class="query-test-result" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>