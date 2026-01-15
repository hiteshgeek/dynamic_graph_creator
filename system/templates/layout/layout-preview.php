<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($layout->getName()); ?> - Preview</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Google Sans Font -->
    <link href="https://fonts.googleapis.com/css2?family=Product+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <?php if ($css = Utility::getCss('common')): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
    <?php if ($css = Utility::getCss('layout')): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="page-header">
        <div class="page-header-left">
            <a href="?urlq=layout" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1><?php echo htmlspecialchars($layout->getName()); ?></h1>
        </div>
        <div class="page-header-right">
            <a href="?urlq=layout/builder/<?php echo $layout->getId(); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Layout
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div id="layout-preview" class="layout-preview" data-layout-id="<?php echo $layout->getId(); ?>">
            <div class="layout-sections">
                <?php
                $structure = $layout->getStructureArray();
                if (isset($structure['sections'])):
                    foreach ($structure['sections'] as $section):
                ?>
                <div class="layout-section"
                     data-section-id="<?php echo htmlspecialchars($section['sid']); ?>"
                     style="display: grid; grid-template-columns: <?php echo htmlspecialchars($section['gridTemplate']); ?>; gap: <?php echo isset($section['gap']) ? htmlspecialchars($section['gap']) : '16px'; ?>; min-height: <?php echo isset($section['minHeight']) ? htmlspecialchars($section['minHeight']) : '200px'; ?>;">

                    <?php foreach ($section['areas'] as $area): ?>
                    <div class="layout-area"
                         data-area-id="<?php echo htmlspecialchars($area['aid']); ?>"
                         style="grid-column: span <?php echo isset($area['colSpan']) ? intval($area['colSpan']) : 1; ?>;">

                        <?php if (isset($area['content']) && $area['content']['type'] === 'empty'): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas <?php echo isset($area['emptyState']['icon']) ? htmlspecialchars($area['emptyState']['icon']) : 'fa-plus-circle'; ?>"></i>
                            </div>
                            <div class="empty-state-message">
                                <?php echo isset($area['emptyState']['message']) ? htmlspecialchars($area['emptyState']['message']) : 'No content'; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="area-content">
                            <p>Widget: <?php echo isset($area['content']['widgetType']) ? htmlspecialchars($area['content']['widgetType']) : 'Unknown'; ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <?php if ($js = Utility::getJs('common')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>
</body>
</html>
