
<?php
    // Set company start date for datepicker presets
    echo DGCHelper::renderCompanyStartDateScript();

    $saveButtonClass = $filter ? 'btn-outline-warning' : 'btn-primary';
    $rightContent = '<div class="status-indicators"></div>';
    $rightContent .= '<button type="button" class="btn ' . $saveButtonClass . ' btn-sm save-filter-btn" data-save-btn><i class="fas fa-save"></i> ' . ($filter ? 'Save' : 'Create Data Filter') . '</button>';
    echo DGCHelper::renderPageHeader([
        'title' => $filter ? 'Edit Data Filter' : 'Create Data Filter',
        'backUrl' => '?urlq=data-filter',
        'backLabel' => 'Data Filters',
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="data-filter-form-layout">
        <!-- Sidebar with filter list -->
        <aside class="data-filter-form-sidebar">
            <div class="sidebar-header">
                <span class="sidebar-title">All Data Filters</span>
                <span class="sidebar-count"><?php echo $totalFilters; ?></span>
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
                        <span class="filter-nav-source <?php echo $f['data_source']; ?>" title="<?php echo $f['data_source'] === 'query' ? 'Query' : 'Static'; ?>">
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
                        <small class="form-hint d-block mb-3">
                            Filter Key: Only letters, numbers, and underscores allowed.<br>
                            <span class="badge bg-light text-dark"><code>::</code> prefix will be added automatically.</span>
                        </small>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="filter-type">Filter Type <span class="required">*</span></label>
                                <select id="filter-type" class="form-select">
                                    <option value="text" <?php echo ($filter && $filter->getFilterType() === 'text') ? 'selected' : ''; ?>>Text Input</option>
                                    <option value="number" <?php echo ($filter && $filter->getFilterType() === 'number') ? 'selected' : ''; ?>>Number Input</option>
                                    <option value="date" <?php echo ($filter && $filter->getFilterType() === 'date') ? 'selected' : ''; ?>>Date Picker</option>
                                    <option value="date_range" <?php echo ($filter && $filter->getFilterType() === 'date_range') ? 'selected' : ''; ?>>Date Range</option>
                                    <option value="main_datepicker" <?php echo ($filter && $filter->getFilterType() === 'main_datepicker') ? 'selected' : ''; ?>>Main Datepicker (with presets)</option>
                                    <option value="select" <?php echo ($filter && in_array($filter->getFilterType(), array('select', 'multi_select'))) ? 'selected' : ''; ?>>Select</option>
                                    <option value="checkbox" <?php echo ($filter && $filter->getFilterType() === 'checkbox') ? 'selected' : ''; ?>>Checkbox</option>
                                    <option value="radio" <?php echo ($filter && $filter->getFilterType() === 'radio') ? 'selected' : ''; ?>>Radio Buttons</option>
                                    <option value="tokeninput" <?php echo ($filter && $filter->getFilterType() === 'tokeninput') ? 'selected' : ''; ?>>Token Input</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="filter-default">Default Value</label>
                                <input type="text" id="filter-default" class="form-control" placeholder="Optional" value="<?php echo $filter ? htmlspecialchars($filter->getDefaultValue()) : ''; ?>">
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

