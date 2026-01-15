<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $filter ? 'Edit' : 'Add'; ?> Filter - Dynamic Graph Creator</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Google Sans Font -->
    <link href="https://fonts.googleapis.com/css2?family=Product+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- CodeMirror for SQL highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/material.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/sql/sql.min.js"></script>

    <!-- Custom CSS -->
    <?php if ($css = Utility::getCss('common')): ?>
        <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
    <?php if ($css = Utility::getCss('filter')): ?>
        <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>

<body>
    <div class="page-header">
        <div class="page-header-left">
            <a href="?urlq=filters" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1><?php echo $filter ? 'Edit' : 'Add'; ?> Filter</h1>
        </div>
    </div>

    <div class="container">
        <div class="filter-form-page" data-filter-id="<?php echo $filter ? $filter->getId() : ''; ?>">
            <div class="card">
                <div class="card-body">
                    <form id="filter-form">
                        <input type="hidden" id="filter-id" value="<?php echo $filter ? $filter->getId() : ''; ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Filter Key <span class="required">*</span></label>
                                <input type="text" id="filter-key" class="form-control" placeholder=":placeholder_name" value="<?php echo $filter ? htmlspecialchars($filter->getFilterKey()) : ''; ?>" required>
                                <small class="form-hint">Placeholder used in SQL queries (e.g., :year, :date_from, :status)</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Label <span class="required">*</span></label>
                                <input type="text" id="filter-label" class="form-control" placeholder="Year" value="<?php echo $filter ? htmlspecialchars($filter->getFilterLabel()) : ''; ?>" required>
                                <small class="form-hint">Display label shown to users</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Filter Type <span class="required">*</span></label>
                                <select id="filter-type" class="form-control">
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
                                <label class="form-label">Default Value</label>
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
                            <label class="form-label">
                                <input type="checkbox" id="filter-multiple" <?php echo $isMultiSelect ? 'checked' : ''; ?>> Allow multiple selection
                            </label>
                            <small class="form-hint d-block">Enable users to select more than one option</small>
                        </div>

                        <div id="checkbox-radio-config-section" class="form-group" style="<?php echo $showCheckboxRadioConfig ? '' : 'display: none;'; ?>">
                            <label class="form-label">
                                <input type="checkbox" id="filter-inline" <?php echo $isInline ? 'checked' : ''; ?>> Display inline
                            </label>
                            <small class="form-hint d-block">Show options horizontally instead of stacked vertically</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" id="filter-required" <?php echo ($filter && $filter->getIsRequired()) ? 'checked' : ''; ?>> Required field
                            </label>
                        </div>

                        <hr>

                        <?php
                        $dataSource = $filter ? $filter->getDataSource() : 'static';
                        $typesWithOptions = array('select', 'checkbox', 'radio', 'tokeninput');
                        $currentType = $filter ? $filter->getFilterType() : 'text';
                        // multi_select is stored in DB but shown as 'select' in UI
                        $showDataSource = in_array($currentType, $typesWithOptions) || $currentType === 'multi_select';
                        ?>

                        <div id="data-source-section" style="<?php echo $showDataSource ? '' : 'display: none;'; ?>">
                            <h4>Data Source</h4>
                            <p class="text-muted">How to get the filter options</p>

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
                                                    <button type="button" class="btn btn-sm btn-outline remove-option-btn">
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
                                                <button type="button" class="btn btn-sm btn-outline remove-option-btn">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="static-options-actions">
                                        <button type="button" class="btn btn-sm btn-outline add-option-btn">
                                            <i class="fas fa-plus"></i> Add Option
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Query Options -->
                            <div id="query-options-section" style="<?php echo $dataSource === 'query' ? '' : 'display: none;'; ?>">
                                <div class="form-group">
                                    <label class="form-label">SQL Query</label>
                                    <textarea id="data-query" class="form-control query-textarea" rows="4" placeholder="SELECT id as value, name as label FROM categories WHERE status = 1 ORDER BY name"><?php echo $filter ? htmlspecialchars($filter->getDataQuery()) : ''; ?></textarea>
                                    <small class="form-hint">Query must return <code>value</code> and <code>label</code> columns</small>
                                </div>
                                <div class="query-actions">
                                    <button type="button" class="btn btn-sm btn-outline" id="copy-query-btn">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline" id="format-query-btn">
                                        <i class="fas fa-align-left"></i> Format SQL
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" id="test-query-btn">
                                        <i class="fas fa-play"></i> Test Query
                                    </button>
                                </div>
                                <div id="query-result" style="display: none;"></div>
                            </div>

                            <!-- Filter Preview -->
                            <div id="filter-preview-container" style="display: none;"></div>
                        </div>
                    </form>
                </div>

                <div class="card-footer">
                    <a href="?urlq=filters" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="button" class="btn btn-primary save-filter-btn">
                        <i class="fas fa-save"></i> Save Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = Utility::getJs('common')): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
    <?php if ($js = Utility::getJs('filter')): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
</body>

</html>