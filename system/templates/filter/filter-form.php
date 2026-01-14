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
            <h1>Dynamic Graph Creator</h1>
            <div class="breadcrumb">
                <i class="fas fa-chevron-right"></i>
                <a href="?urlq=filters">Filters</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo $filter ? 'Edit' : 'Add'; ?> Filter</span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="filter-form-page" data-filter-id="<?php echo $filter ? $filter->getId() : ''; ?>">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h2><?php echo $filter ? 'Edit' : 'Add'; ?> Filter</h2>
                        <span class="text-muted">Define a reusable filter for graphs</span>
                    </div>
                </div>

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
                                    <option value="select" <?php echo ($filter && $filter->getFilterType() === 'select') ? 'selected' : ''; ?>>Select (Single)</option>
                                    <option value="multi_select" <?php echo ($filter && $filter->getFilterType() === 'multi_select') ? 'selected' : ''; ?>>Select (Multiple)</option>
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

                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" id="filter-required" <?php echo ($filter && $filter->getIsRequired()) ? 'checked' : ''; ?>> Required field
                            </label>
                        </div>

                        <hr>

                        <?php
                        $dataSource = $filter ? $filter->getDataSource() : 'static';
                        $typesWithOptions = array('select', 'multi_select', 'checkbox', 'radio', 'tokeninput');
                        $currentType = $filter ? $filter->getFilterType() : 'text';
                        $showDataSource = in_array($currentType, $typesWithOptions);
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
                                    <button type="button" class="btn btn-sm btn-outline add-option-btn">
                                        <i class="fas fa-plus"></i> Add Option
                                    </button>
                                </div>
                            </div>

                            <!-- Query Options -->
                            <div id="query-options-section" style="<?php echo $dataSource === 'query' ? '' : 'display: none;'; ?>">
                                <div class="form-group">
                                    <label class="form-label">SQL Query</label>
                                    <textarea id="data-query" class="form-control query-textarea" rows="4" placeholder="SELECT id as value, name as label FROM categories WHERE status = 1 ORDER BY name"><?php echo $filter ? htmlspecialchars($filter->getDataQuery()) : ''; ?></textarea>
                                    <small class="form-hint">Query must return <code>value</code> and <code>label</code> columns</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" id="test-query-btn">
                                    <i class="fas fa-play"></i> Test Query
                                </button>
                                <div id="query-result" style="display: none; margin-top: 10px;"></div>
                            </div>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var typesWithOptions = ['select', 'multi_select', 'checkbox', 'radio', 'tokeninput'];
        var queryEditor = null;

        // Initialize CodeMirror for query (matching graph creator styling)
        var queryTextarea = document.getElementById('data-query');
        if (queryTextarea) {
            queryEditor = CodeMirror.fromTextArea(queryTextarea, {
                mode: 'text/x-sql',
                theme: 'default',
                lineNumbers: true,
                lineWrapping: true
            });
        }

        // Show/hide data source section based on filter type
        document.getElementById('filter-type').addEventListener('change', function() {
            var dataSourceSection = document.getElementById('data-source-section');
            dataSourceSection.style.display = typesWithOptions.includes(this.value) ? 'block' : 'none';
        });

        // Data source tab switching
        document.querySelectorAll('.data-source-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                var source = this.dataset.source;
                document.getElementById('data-source').value = source;

                document.querySelectorAll('.data-source-tab').forEach(function(t) {
                    t.classList.remove('active');
                });
                this.classList.add('active');

                document.getElementById('static-options-section').style.display = source === 'static' ? 'block' : 'none';
                document.getElementById('query-options-section').style.display = source === 'query' ? 'block' : 'none';

                if (source === 'query' && queryEditor) {
                    queryEditor.refresh();
                }
            });
        });

        // Add option button
        document.querySelector('.add-option-btn').addEventListener('click', function() {
            addOptionRow();
        });

        // Remove option buttons (delegated)
        document.querySelector('.filter-options-list').addEventListener('click', function(e) {
            if (e.target.closest('.remove-option-btn')) {
                var row = e.target.closest('.filter-option-item');
                if (document.querySelectorAll('.filter-option-item').length > 1) {
                    row.remove();
                }
            }
        });

        // Test query button
        document.getElementById('test-query-btn').addEventListener('click', function() {
            testQuery();
        });

        // Save filter
        document.querySelector('.save-filter-btn').addEventListener('click', function() {
            saveFilter();
        });

        function addOptionRow(value, label) {
            var optionsList = document.querySelector('.filter-options-list');
            var row = document.createElement('div');
            row.className = 'filter-option-item';
            row.innerHTML =
                '<input type="text" class="form-control option-value" placeholder="Value" value="' + (value || '') + '">' +
                '<input type="text" class="form-control option-label" placeholder="Label" value="' + (label || '') + '">' +
                '<button type="button" class="btn btn-sm btn-outline remove-option-btn">' +
                    '<i class="fas fa-times"></i>' +
                '</button>';
            optionsList.appendChild(row);
        }

        function testQuery() {
            var query = queryEditor ? queryEditor.getValue() : document.getElementById('data-query').value;
            if (!query.trim()) {
                Toast.error('Please enter a query');
                return;
            }

            Loading.show('Testing query...');
            Ajax.post('test_filter_query', { query: query }).then(function(result) {
                Loading.hide();
                var resultDiv = document.getElementById('query-result');
                if (result.success) {
                    var options = result.data.options || [];
                    var html = '<div class="alert alert-success"><strong>Query valid!</strong> Found ' + options.length + ' options.</div>';

                    // Show warnings if any
                    if (result.data.warnings && result.data.warnings.length > 0) {
                        html += '<div class="alert alert-warning"><ul class="mb-0">';
                        result.data.warnings.forEach(function(w) {
                            html += '<li>' + w + '</li>';
                        });
                        html += '</ul></div>';
                    }

                    if (options.length > 0) {
                        html += '<table class="table table-sm"><thead><tr><th>Value</th><th>Label</th></tr></thead><tbody>';
                        options.slice(0, 10).forEach(function(opt) {
                            html += '<tr><td>' + (opt.value || '-') + '</td><td>' + (opt.label || '-') + '</td></tr>';
                        });
                        if (options.length > 10) {
                            html += '<tr><td colspan="2" class="text-muted">... and ' + (options.length - 10) + ' more</td></tr>';
                        }
                        html += '</tbody></table>';
                    }
                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger">' + (result.message || 'Query failed') + '</div>';
                }
                resultDiv.style.display = 'block';
            }).catch(function() {
                Loading.hide();
                Toast.error('Failed to test query');
            });
        }

        function saveFilter() {
            var filterKey = document.getElementById('filter-key').value.trim();
            var filterLabel = document.getElementById('filter-label').value.trim();
            var filterType = document.getElementById('filter-type').value;
            var dataSource = document.getElementById('data-source').value;

            if (!filterKey) {
                Toast.error('Filter key is required');
                return;
            }

            if (!filterLabel) {
                Toast.error('Filter label is required');
                return;
            }

            // Ensure filter key starts with :
            if (filterKey.charAt(0) !== ':') {
                filterKey = ':' + filterKey;
            }

            var data = {
                filter_id: document.getElementById('filter-id').value,
                filter_key: filterKey,
                filter_label: filterLabel,
                filter_type: filterType,
                data_source: typesWithOptions.includes(filterType) ? dataSource : 'static',
                data_query: '',
                static_options: '',
                default_value: document.getElementById('filter-default').value,
                is_required: document.getElementById('filter-required').checked ? 1 : 0
            };

            // Get options based on data source
            if (typesWithOptions.includes(filterType)) {
                if (dataSource === 'query') {
                    data.data_query = queryEditor ? queryEditor.getValue() : document.getElementById('data-query').value;
                } else {
                    var optionItems = [];
                    document.querySelectorAll('.filter-option-item').forEach(function(row) {
                        var value = row.querySelector('.option-value').value.trim();
                        var label = row.querySelector('.option-label').value.trim();
                        if (value || label) {
                            optionItems.push({ value: value, label: label || value });
                        }
                    });
                    data.static_options = JSON.stringify(optionItems);
                }
            }

            Loading.show('Saving filter...');
            Ajax.post('save_filter', data).then(function(result) {
                Loading.hide();
                if (result.success) {
                    Toast.success('Filter saved successfully');
                    window.location.href = '?urlq=filters';
                } else {
                    Toast.error(result.message || 'Failed to save filter');
                }
            }).catch(function() {
                Loading.hide();
                Toast.error('Failed to save filter');
            });
        }
    });
    </script>

    <style>
    .form-row {
        display: flex;
        gap: 20px;
    }
    .form-row .form-group {
        flex: 1;
    }
    .data-source-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    .data-source-tab {
        flex: 1;
        padding: 12px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
    }
    .data-source-tab:hover {
        border-color: #4285f4;
    }
    .data-source-tab.active {
        border-color: #4285f4;
        background: rgba(66, 133, 244, 0.05);
        color: #4285f4;
    }
    .data-source-tab i {
        display: block;
        font-size: 24px;
        margin-bottom: 5px;
    }
    .query-textarea {
        font-family: monospace;
    }
    /* Match graph creator CodeMirror styling */
    .CodeMirror {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        height: 150px;
        background: #FAFAFA;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', monospace;
        font-size: 13px;
        line-height: 1.6;
    }
    .CodeMirror-scroll {
        min-height: 150px;
    }
    .CodeMirror-focused {
        background: #fff;
    }
    .CodeMirror-gutters {
        background: #f5f5f5;
        border-right: 1px solid #e0e0e0;
    }
    .CodeMirror-linenumber {
        color: #999;
        padding: 0 8px;
    }
    /* SQL Syntax highlighting */
    .cm-keyword {
        color: #7c3aed;
        font-weight: 500;
    }
    .cm-def,
    .cm-variable-2 {
        color: #0891b2;
    }
    .cm-string {
        color: #059669;
    }
    .cm-number {
        color: #d97706;
    }
    .cm-comment {
        color: #999;
        font-style: italic;
    }
    .cm-operator {
        color: #333;
    }
    .cm-builtin {
        color: #dc2626;
    }
    /* Filter options styling */
    .filter-option-item {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 8px;
    }
    .filter-option-item input {
        flex: 1;
    }
    .filter-option-item .remove-option-btn {
        width: 38px;
        height: 38px;
        min-width: 38px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc3545;
        border-color: #dc3545;
    }
    .filter-option-item .remove-option-btn:hover {
        background: rgba(220, 53, 69, 0.1);
    }
    #query-result .table {
        font-size: 13px;
        margin-top: 10px;
    }
    #test-query-btn {
        margin-top: 10px;
    }
    </style>
</body>
</html>
