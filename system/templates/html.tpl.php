<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($this->getPageTitle() ?: 'Dynamic Graph Creator'); ?></title>

    <!-- Theme initialization - must run before CSS loads to prevent FOUC -->
    <script>
    (function() {
        var mode = localStorage.getItem('dgc-theme-mode') || 'light';
        var theme = mode;
        if (mode === 'system') {
            theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.classList.add('theme-' + theme);
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-theme-mode', mode);
    })();
    </script>

    <!-- Outfit Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Core Libraries -->
    <link href="<?php echo SiteConfig::themeLibrariessUrl(); ?>bootstrap5/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo SiteConfig::themeLibrariessUrl(); ?>fontawesome6/css/all.min.css" rel="stylesheet">

    <!-- Page-specific CSS -->
    <?php foreach ($this->getStylesheets() as $css): ?>
    <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endforeach; ?>
</head>
<body class="dgc-app">
    <?php echo $this->getContent(); ?>

    <!-- Page-specific JS (includes bootstrap and other libraries via ThemeRegistry) -->
    <?php foreach ($this->getScripts() as $js): ?>
    <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
</body>
</html>
