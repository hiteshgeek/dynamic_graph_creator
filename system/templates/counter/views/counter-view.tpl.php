
<?php
    // Set company start date for datepicker presets
    echo DGCHelper::renderCompanyStartDateScript();

    // Get counter config
    $config = $counter->getConfigArray();
    $defaultConfig = Counter::getDefaultConfig();
    $icon = isset($config['icon']) && $config['icon'] ? $config['icon'] : $defaultConfig['icon'];
    $color = isset($config['color']) && $config['color'] ? $config['color'] : $defaultConfig['color'];
    $format = isset($config['format']) && $config['format'] ? $config['format'] : $defaultConfig['format'];

    // Operations
    $rightContent = '<a href="?urlq=widget-counter/edit/' . $counter->getId() . '" class="btn btn-icon btn-outline-design btn-design-mode" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Design Mode"><i class="fas fa-paint-brush"></i></a>';
    $rightContent .= '<a href="?urlq=widget-counter/create" class="btn btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create New Counter"><i class="fas fa-plus"></i></a>';
    // Navigation links
    $rightContent .= '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Home"><i class="fas fa-home"></i></a>';
    if (DGCHelper::hasAdminAccess()) {
        $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
        $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
    }

    // Widget dropdown
    $leftContent = DGCHelper::renderWidgetDropdown('counter');
    echo DGCHelper::renderPageHeader([
        'title' => $counter->getName(),
        'backUrl' => '?urlq=widget-counter',
        'backLabel' => 'Counters',
        'leftContent' => $leftContent,
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="graph-view-layout">
        <!-- Sidebar with counter list -->
        <aside class="graph-view-sidebar" id="graph-view-sidebar">
            <script>
                // Apply collapsed state immediately to prevent flash
                if (localStorage.getItem('graphViewSidebarCollapsed') === 'true') {
                    document.getElementById('graph-view-sidebar').classList.add('collapsed');
                }
            </script>
            <div class="sidebar-header">
                <span class="sidebar-title">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>All Counters</span>
                </span>
                <span class="sidebar-count"><?php
                    $currentIdx = 0;
                    foreach ($allCounters as $index => $c) {
                        if ($c->getId() == $counter->getId()) {
                            $currentIdx = $index + 1;
                            break;
                        }
                    }
                    echo $currentIdx . '/' . $totalCounters;
                ?></span>
                <button type="button" class="collapse-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
            <div class="graph-list-nav">
                <?php foreach ($allCounters as $index => $c): ?>
                    <?php
                    $cConfig = $c->getConfigArray();
                    $cIcon = isset($cConfig['icon']) && $cConfig['icon'] ? $cConfig['icon'] : $defaultConfig['icon'];
                    $cColor = isset($cConfig['color']) && $cConfig['color'] ? $cConfig['color'] : $defaultConfig['color'];
                    ?>
                    <a href="?urlq=widget-counter/view/<?php echo $c->getId(); ?>"
                       class="graph-nav-item <?php echo ($c->getId() == $counter->getId()) ? 'active' : ''; ?>">
                        <span class="graph-type-icon counter-icon-colored" style="--counter-color: <?php echo htmlspecialchars($cColor); ?>;">
                            <span class="material-icons"><?php echo htmlspecialchars($cIcon); ?></span>
                        </span>
                        <span class="graph-nav-info">
                            <span class="graph-nav-name"><?php echo htmlspecialchars($c->getName()); ?></span>
                            <span class="graph-nav-type">Counter</span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- Main content -->
        <main class="graph-view-main">
            <div id="counter-view"
                 data-counter-id="<?php echo $counter->getId(); ?>"
                 data-counter-name="<?php echo htmlspecialchars($counter->getName()); ?>"
                 data-config="<?php echo htmlspecialchars($counter->getConfig()); ?>"
                 data-has-filters="<?php echo !empty($filters) ? '1' : '0'; ?>">

                <!-- Filters -->
                <?php if (!empty($filters)): ?>
                    <div class="card graph-view-filters">
                        <div class="filters-list" id="counter-filters">
                            <?php foreach ($filters as $filter):
                                $filterKey = $filter['filter_key'];
                                $filterKeyClean = ltrim($filterKey, ':');
                                $filterType = $filter['filter_type'];
                                $defaultValue = $filter['default_value'];
                                $options = isset($filter['options']) ? $filter['options'] : array();
                                $filterConfig = isset($filter['filter_config']) ? $filter['filter_config'] : '';
                                $filterConfigArr = $filterConfig ? json_decode($filterConfig, true) : array();
                                $isInline = isset($filterConfigArr['inline']) && $filterConfigArr['inline'];
                            ?>
                                <div class="filter-input-item" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>">
                                    <div class="filter-input-header">
                                        <label class="filter-input-label"><?php echo htmlspecialchars($filter['filter_label']); ?></label>
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

                <!-- Counter Card -->
                <div class="card counter-display-card">
                    <div class="card-header">
                        <div class="card-header-left">
                            <span class="graph-type-icon counter-icon-colored" style="--counter-color: <?php echo htmlspecialchars($color); ?>;">
                                <span class="material-icons"><?php echo htmlspecialchars($icon); ?></span>
                            </span>
                            <span class="text-muted"><?php echo htmlspecialchars($counter->getName()); ?></span>
                            <?php if (!empty($categories)): ?>
                                <?php echo DGCHelper::renderWidgetCategoryBadges($categories, 'lg'); ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary refresh-btn" id="refresh-counter">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="counter-preview-container">
                        <div class="counter-card-display" style="background-color: <?php echo htmlspecialchars($color); ?>;">
                            <div class="counter-icon">
                                <span class="material-icons"><?php echo htmlspecialchars($icon); ?></span>
                            </div>
                            <div class="counter-content">
                                <div class="counter-value" data-format="<?php echo htmlspecialchars($format); ?>">--</div>
                                <div class="counter-name"><?php echo htmlspecialchars($counter->getName()); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
