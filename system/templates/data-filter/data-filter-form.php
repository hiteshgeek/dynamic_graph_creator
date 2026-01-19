<?php
    $saveButtonClass = $filter ? 'btn-outline-warning' : 'btn-primary';
    echo Utility::renderPageHeader([
        'title' => $filter ? 'Edit Data Filter' : 'Create Data Filter',
        'backUrl' => '?urlq=data-filter',
        'backLabel' => 'Data Filters',
        'rightContent' => '<button type="button" class="btn ' . $saveButtonClass . ' btn-sm save-filter-btn" data-save-btn><i class="fas fa-save"></i> ' . ($filter ? 'Save' : 'Create Data Filter') . '</button>'
    ]);
    ?>

    <div class="filter-form-layout<?php echo ($totalFilters == 0) ? ' no-sidebar' : ''; ?>">
        <?php if ($totalFilters > 0): ?>
        <!-- Sidebar with filter list -->
        <aside class="filter-form-sidebar">
            <div class="sidebar-header">
                <span class="sidebar-title">All Data Filters</span>
                <span class="sidebar-count"><?php echo $totalFilters; ?></span>
            </div>
            <div class="filter-list-nav">
                <?php
                $typeIcons = array(
                    'text' => 'font',
                    'number' => 'hashtag',
                    'date' => 'calendar',
                    'date_range' => 'calendar-week',
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
                            <span class="filter-nav-key"><?php echo htmlspecialchars($f['filter_key']); ?></span>
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
            </div>
        </aside>
        <?php endif; ?>

        <!-- Main content -->
        <main class="filter-form-main">
            <div class="filter-form-page" data-filter-id="<?php echo $filter ? $filter->getId() : ''; ?>">
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
                                <small class="form-hint">Only letters, numbers, and underscores. <code>::</code> prefix is added automatically.</small>
                                <div class="invalid-feedback">Only letters, numbers, and underscores allowed</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="filter-label">Label <span class="required">*</span></label>
                                <input type="text" id="filter-label" class="form-control" placeholder="Year" value="<?php echo $filter ? htmlspecialchars($filter->getFilterLabel()) : ''; ?>" required>
                                <small class="form-hint">Display label shown to users</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="filter-type">Filter Type <span class="required">*</span></label>
                                <select id="filter-type" class="form-select">
                                    <option value="text" <?php echo ($filter && $filter->getFilterType() === 'text') ? 'selected' : ''; ?>>Text Input</option>
                                    <option value="number" <?php echo ($filter && $filter->getFilterType() === 'number') ? 'selected' : ''; ?>>Number Input</option>
                                    <option value="date" <?php echo ($filter && $filter->getFilterType() === 'date') ? 'selected' : ''; ?>>Date Picker</option>
                                    <option value="date_range" <?php echo ($filter && $filter->getFilterType() === 'date_range') ? 'selected' : ''; ?>>Date Range</option>
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

                            <!-- Static Options -->
                            <div id="static-options-section" style="<?php echo $dataSource === 'static' ? '' : 'display: none;'; ?>">
                                <div class="form-group">
                                    <label class="form-label">Options</label>
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

                            <!-- Query Options -->
                            <div id="query-options-section" style="<?php echo $dataSource === 'query' ? '' : 'display: none;'; ?>">
                                <div class="form-group">
                                    <label class="form-label" for="data-query">SQL Query</label>
                                    <div class="query-builder">
                                        <textarea id="data-query" class="query-editor" rows="4" placeholder="SELECT id as value, name as label, 1 as is_selected FROM categories WHERE status = 1 ORDER BY name"><?php echo $filter ? htmlspecialchars($filter->getDataQuery()) : ''; ?></textarea>
                                        <!-- Copy, Format, Test buttons and hint are generated by CodeMirrorEditor -->
                                        <div id="query-result" class="query-test-result" style="display: none;"></div>
                                    </div>
                                </div>
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
        </main>
    </div>

