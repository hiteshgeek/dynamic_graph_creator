<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($this->getPageTitle() ?: 'Dynamic Graph Creator'); ?></title>

    <!-- Google Sans Font -->
    <link href="https://fonts.googleapis.com/css2?family=Product+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Core Libraries -->
    <link href="<?php echo SiteConfig::themeLibrariessUrl(); ?>bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo SiteConfig::themeLibrariessUrl(); ?>fontawesome/css/all.min.css" rel="stylesheet">

    <!-- Page-specific CSS -->
    <?php foreach ($this->getStylesheets() as $css): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endforeach; ?>
</head>
<body>
    <?php echo $this->getContent(); ?>

    <!-- Core Libraries JS -->
    <script src="<?php echo SiteConfig::themeLibrariessUrl(); ?>bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Page-specific JS -->
    <?php foreach ($this->getScripts() as $js): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
</body>
</html>
