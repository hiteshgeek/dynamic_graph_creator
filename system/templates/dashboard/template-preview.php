<?php
require_once __DIR__ . '/../../includes/dashboard/template-preview-component.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php $name = $template->getName(); echo htmlspecialchars($name ? $name : 'Template'); ?> - Preview</title>

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
    <?php if ($css = Utility::getCss('dashboard')): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="template-preview-page">
    <?php
    $templateName = $template->getName();
    $templateName = $templateName ? $templateName : 'Template';

    $badges = [];
    if ($template->getIsSystem()) {
        $badges[] = ['label' => 'System', 'icon' => 'fa-lock', 'class' => 'badge-system'];
    }

    $rightContent = '<button class="btn btn-success btn-sm duplicate-template-btn" data-template-id="' . $template->getId() . '"><i class="fas fa-copy"></i> Duplicate Template</button>';
    if (!$template->getIsSystem()) {
        $rightContent .= '<a href="?urlq=dashboard/template/builder/' . $template->getId() . '" class="btn btn-design btn-sm btn-design-mode"><i class="fas fa-paint-brush"></i> Design Mode</a>';
        $rightContent .= '<button class="btn btn-danger btn-sm delete-template-btn" data-template-id="' . $template->getId() . '"><i class="fas fa-trash"></i> Delete Template</button>';
    }

    echo Utility::renderPageHeader([
        'title' => $templateName,
        'backUrl' => '?urlq=dashboard/templates',
        'badges' => $badges,
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="container-fluid">
        <div class="template-preview-page">
            <div class="template-preview-container">
                <div class="template-preview-card">
                    <div class="template-preview">
                        <?php
                        $structure = $template->getStructureArray();
                        echo renderTemplatePreview($structure);
                        ?>
                    </div>
                    <div class="template-info">
                        <h2><?php $name = $template->getName(); echo htmlspecialchars($name ? $name : 'Template'); ?></h2>
                        <?php $desc = $template->getDescription(); if (!empty($desc)): ?>
                        <p><?php echo htmlspecialchars($desc); ?></p>
                        <?php endif; ?>
                    </div>
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
    <script src="system/scripts/src/Theme.js"></script>
    <?php if ($js = Utility::getJs('dashboard')): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endif; ?>

    <script>
        // Initialize TemplateManager for delete and duplicate buttons
        document.addEventListener('DOMContentLoaded', function() {
            if (window.TemplateManager) {
                TemplateManager.initTemplateList();
            }
        });
    </script>
</body>
</html>
