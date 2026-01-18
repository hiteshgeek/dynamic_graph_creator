<?php
    echo Utility::renderPageHeader([
        'title' => $graph->getName(),
        'backUrl' => '?urlq=graph',
        'backLabel' => 'Graphs',
        'rightContent' => '<a href="?urlq=graph/edit/' . $graph->getId() . '" class="btn btn-design btn-sm btn-design-mode"><i class="fas fa-paint-brush"></i> Design Mode</a>'
    ]);
    ?>

    <div class="container">
        <div id="graph-view" data-graph-id="<?php echo $graph->getId(); ?>" data-graph-type="<?php echo $graph->getGraphType(); ?>" data-graph-name="<?php echo htmlspecialchars($graph->getName()); ?>" data-config="<?php echo htmlspecialchars($graph->getConfig()); ?>" data-has-filters="<?php echo !empty($filters) ? '1' : '0'; ?>">
            <!-- Filters -->
            <?php if (!empty($filters)): ?>
                <div class="card graph-view-filters">
                    <div class="filters-list" id="graph-filters">
                        <?php foreach ($filters as $filter):
                            $filterKey = $filter['filter_key'];
                            $filterKeyClean = ltrim($filterKey, ':');
                            $filterType = $filter['filter_type'];
                            $defaultValue = $filter['default_value'];
                            $options = isset($filter['options']) ? $filter['options'] : array();
                            // Get filter config for inline display
                            $filterConfig = isset($filter['filter_config']) ? $filter['filter_config'] : '';
                            $filterConfigArr = $filterConfig ? json_decode($filterConfig, true) : array();
                            $isInline = isset($filterConfigArr['inline']) && $filterConfigArr['inline'];
                        ?>
                            <div class="filter-input-item" data-filter-key="<?php echo htmlspecialchars($filterKeyClean); ?>">
                                <div class="filter-input-header">
                                    <label class="filter-input-label"><?php echo htmlspecialchars($filter['filter_label']); ?></label>
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
                    <div class="filter-actions">
                        <button type="button" class="btn btn-primary btn-sm filter-apply-btn">
                            <i class="fas fa-check"></i> Apply Filters
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Chart -->
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <span class="graph-type-icon <?php echo $graph->getGraphType(); ?>">
                            <i class="fas fa-chart-<?php echo $graph->getGraphType(); ?>"></i>
                        </span>
                        <span class="text-muted"><?php echo ucfirst($graph->getGraphType()); ?> Chart</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="export-chart" title="Save chart as PNG image">
                        <i class="fas fa-image"></i> Save Image
                    </button>
                </div>
                <div class="graph-preview-container" style="height: 500px;"></div>
            </div>
        </div>
    </div>

