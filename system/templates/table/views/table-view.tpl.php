
<?php
    // Set company start date for datepicker presets
    echo DGCHelper::renderCompanyStartDateScript();

    // Get table config
    $config = $table->getConfigArray();
    $defaultConfig = WidgetTable::getDefaultConfig();

    // Operations
    $rightContent = '<a href="?urlq=widget-table/edit/' . $table->getId() . '" class="btn btn-icon btn-outline-design btn-design-mode" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Design Mode"><i class="fas fa-paint-brush"></i></a>';
    $rightContent .= '<a href="?urlq=widget-table/create" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create New Table"><i class="fas fa-plus"></i></a>';
    // Navigation links
    $rightContent .= '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Home"><i class="fas fa-home"></i></a>';
    if (DGCHelper::hasAdminAccess()) {
        $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
        $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
    }

    // Widget dropdown
    $leftContent = DGCHelper::renderWidgetDropdown('table');
    echo DGCHelper::renderPageHeader([
        'title' => $table->getName(),
        'backUrl' => '?urlq=widget-table',
        'backLabel' => 'Tables',
        'leftContent' => $leftContent,
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="graph-view-layout">
        <!-- Sidebar with table list -->
        <aside class="graph-view-sidebar" id="graph-view-sidebar">
            <script>
                // Apply collapsed state immediately to prevent flash
                if (localStorage.getItem('graphViewSidebarCollapsed') === 'true') {
                    document.getElementById('graph-view-sidebar').classList.add('collapsed');
                }
            </script>
            <div class="sidebar-header">
                <span class="sidebar-title">
                    <i class="fas fa-table"></i>
                    <span>All Tables</span>
                </span>
                <span class="sidebar-count"><?php
                    $currentIdx = 0;
                    foreach ($allTables as $index => $t) {
                        if ($t->getId() == $table->getId()) {
                            $currentIdx = $index + 1;
                            break;
                        }
                    }
                    echo $currentIdx . '/' . $totalTables;
                ?></span>
                <button type="button" class="collapse-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
            <div class="graph-list-nav">
                <?php foreach ($allTables as $index => $t): ?>
                    <a href="?urlq=widget-table/view/<?php echo $t->getId(); ?>"
                       class="graph-nav-item <?php echo ($t->getId() == $table->getId()) ? 'active' : ''; ?>">
                        <span class="graph-type-icon table-icon">
                            <i class="fas fa-table"></i>
                        </span>
                        <span class="graph-nav-info">
                            <span class="graph-nav-name"><?php echo htmlspecialchars($t->getName()); ?></span>
                            <span class="graph-nav-type">Table</span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- Main content -->
        <main class="graph-view-main">
            <div id="table-view"
                 data-table-id="<?php echo $table->getId(); ?>"
                 data-table-name="<?php echo htmlspecialchars($table->getName()); ?>"
                 data-config="<?php echo htmlspecialchars($table->getConfig()); ?>"
                 data-has-filters="<?php echo !empty($filters) ? '1' : '0'; ?>">

                <!-- Filters -->
                <?php if (!empty($filters)): ?>
                    <div class="card graph-view-filters">
                        <div class="filters-list" id="table-filters">
                            <?php foreach ($filters as $filter):
                                $filterKey = $filter['filter_key'];
                                $filterKeyClean = ltrim($filterKey, ':');
                                $filterType = $filter['filter_type'];
                                $defaultValue = $filter['default_value'];
                                $options = isset($filter['options']) ? $filter['options'] : array();
                                $filterConfig = isset($filter['filter_config']) ? $filter['filter_config'] : '';
                                $filterConfigArr = $filterConfig ? json_decode($filterConfig, true) : array();
                                $isInline = isset($filterConfigArr['inline']) && $filterConfigArr['inline'];
                                $isMandatoryFilter = isset($mandatoryFilterKeys) && in_array($filterKeyClean, $mandatoryFilterKeys);
                            ?>
                                <div class="filter-input-item<?php echo $isMandatoryFilter ? ' mandatory' : ''; ?>" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>" data-mandatory="<?php echo $isMandatoryFilter ? '1' : '0'; ?>">
                                    <div class="filter-input-header">
                                        <label class="filter-input-label"><?php echo htmlspecialchars($filter['filter_label']); ?></label>
                                        <?php if ($isMandatoryFilter): ?>
                                            <span class="mandatory-badge">Mandatory</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($filterType === 'select'):
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
                        <div class="filter-actions">
                            <div class="auto-apply-toggle">
                                <span class="auto-apply-label">Live Filtering</span>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="auto-apply-switch">
                                </div>
                            </div>
                            <div class="filter-actions-separator"></div>
                            <button type="button" class="btn btn-primary btn-sm filter-apply-btn">
                                <i class="fas fa-check"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Table Display Card -->
                <div class="card table-display-card">
                    <div class="card-header">
                        <div class="card-header-left">
                            <span class="graph-type-icon table-icon">
                                <i class="fas fa-table"></i>
                            </span>
                            <span class="text-muted"><?php echo htmlspecialchars($table->getName()); ?></span>
                            <?php if (!empty($categories)): ?>
                                <?php echo DGCHelper::renderWidgetCategoryBadges($categories, 'lg'); ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary refresh-btn" id="refresh-table">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="table-preview-container table-view-container">
                        <div class="table-loading" id="table-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="table-content" id="table-content">
                            <!-- Table will be rendered here by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
