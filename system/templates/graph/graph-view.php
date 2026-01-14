<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($graph->getName()); ?> - Dynamic Graph Creator</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Google Sans Font -->
    <link href="https://fonts.googleapis.com/css2?family=Product+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

    <!-- Custom CSS -->
    <?php if ($css = GraphUtility::getCss()): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="page-header">
        <div class="page-header-left">
            <h1>Dynamic Graph Creator</h1>
            <div class="breadcrumb">
                <i class="fas fa-chevron-right"></i>
                <a href="?urlq=graph">Graphs</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo htmlspecialchars($graph->getName()); ?></span>
            </div>
        </div>
        <div class="page-header-right">
            <a href="?urlq=graph/edit/<?php echo $graph->getId(); ?>" class="btn btn-outline">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <div class="container">
        <div id="graph-view" data-graph-id="<?php echo $graph->getId(); ?>" data-graph-type="<?php echo $graph->getGraphType(); ?>" data-graph-name="<?php echo htmlspecialchars($graph->getName()); ?>" data-config="<?php echo htmlspecialchars($graph->getConfig()); ?>">
            <!-- Filters -->
            <?php if (!empty($filters)): ?>
            <div class="card">
                <div class="filter-inputs">
                    <?php foreach ($filters as $filter): ?>
                        <div class="filter-input-group">
                            <label><?php echo htmlspecialchars($filter['filter_label']); ?></label>
                            <?php
                            $key = $filter['filter_key'];
                            $type = $filter['filter_type'];
                            $defaultVal = $filter['default_value'];
                            ?>

                            <?php if ($type === 'date'): ?>
                                <input type="date" data-filter-key="<?php echo $key; ?>" value="<?php echo $defaultVal; ?>">

                            <?php elseif ($type === 'date_range'): ?>
                                <div class="filter-input-group-date-range">
                                    <input type="date" data-filter-key="<?php echo $key; ?>_from" placeholder="From">
                                    <input type="date" data-filter-key="<?php echo $key; ?>_to" placeholder="To">
                                </div>

                            <?php elseif ($type === 'number'): ?>
                                <input type="number" data-filter-key="<?php echo $key; ?>" value="<?php echo $defaultVal; ?>">

                            <?php elseif ($type === 'select' || $type === 'multi_select'): ?>
                                <?php
                                $options = array();
                                if (!empty($filter['filter_options'])) {
                                    $parsed = json_decode($filter['filter_options'], true);
                                    $options = is_array($parsed) ? $parsed : (isset($parsed['options']) ? $parsed['options'] : array());
                                }
                                ?>
                                <select data-filter-key="<?php echo $key; ?>" <?php echo $type === 'multi_select' ? 'multiple' : ''; ?>>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($options as $opt): ?>
                                    <option value="<?php echo htmlspecialchars($opt['value']); ?>"><?php echo htmlspecialchars($opt['label']); ?></option>
                                    <?php endforeach; ?>
                                </select>

                            <?php else: ?>
                                <input type="text" data-filter-key="<?php echo $key; ?>" value="<?php echo htmlspecialchars($defaultVal); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <button type="button" class="btn btn-primary filter-apply-btn">
                        <i class="fas fa-check"></i> Apply Filters
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Chart -->
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h2><?php echo htmlspecialchars($graph->getName()); ?></h2>
                        <span class="text-muted">
                            <i class="fas fa-chart-<?php echo $graph->getGraphType(); ?>"></i>
                            <?php echo ucfirst($graph->getGraphType()); ?> Chart
                        </span>
                    </div>
                    <button type="button" class="btn btn-outline" id="export-chart" title="Save chart as PNG image">
                        <i class="fas fa-image"></i> Save Image
                    </button>
                </div>
                <div class="graph-preview-container" style="height: 500px;"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = GraphUtility::getJs()): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
</body>
</html>
