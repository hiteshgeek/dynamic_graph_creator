<?php
/**
 * Dynamic Graph Creator - Migration Tool
 *
 * This tool helps migrate the Dynamic Graph Creator module to your live rapidkart project.
 * Each step can be executed individually with preview and verification.
 */

// Configuration
$sourceDir = __DIR__;
$targetDir = '/var/www/html/rapidkartprocessadminv2';

// Check if target exists
$targetExists = is_dir($targetDir);

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : '';
$step = isset($_GET['step']) ? intval($_GET['step']) : 0;

// Handle file count AJAX request (target directory only)
if ($action === 'count_files') {
    header('Content-Type: application/json');
    $path = isset($_GET['path']) ? $_GET['path'] : '';
    $recursive = isset($_GET['recursive']) ? $_GET['recursive'] === '1' : false;
    $countType = isset($_GET['count_type']) ? $_GET['count_type'] : 'files'; // 'files' or 'folders'

    $fullPath = $targetDir . '/' . $path;

    if (!is_dir($fullPath)) {
        echo json_encode(['count' => 0, 'exists' => false, 'path' => $path, 'type' => $countType]);
        exit;
    }

    // Count files or folders
    $count = 0;
    if ($countType === 'folders') {
        // Count only immediate subdirectories
        $items = glob($fullPath . '/*', GLOB_ONLYDIR);
        $count = count($items);
    } else if ($recursive) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) $count++;
        }
    } else {
        $files = glob($fullPath . '/*');
        foreach ($files as $f) {
            if (is_file($f)) $count++;
        }
    }

    echo json_encode(['count' => $count, 'exists' => true, 'path' => $path, 'type' => $countType]);
    exit;
}

// Define migration steps
$steps = [
    1 => [
        'title' => 'Copy PHP Classes',
        'description' => 'Copies 10 PHP class files to handle graphs, data filters, dashboards, and UI components.',
        'files' => [
            'system/classes/DGCHelper.php',
            'system/classes/Graph.php',
            'system/classes/GraphManager.php',
            'system/classes/DataFilter.php',
            'system/classes/DataFilterManager.php',
            'system/classes/DataFilterSet.php',
            'system/classes/DashboardInstance.php',
            'system/classes/DashboardTemplate.php',
            'system/classes/DashboardTemplateCategory.php',
            'system/classes/DashboardBuilder.php',
        ],
        'type' => 'copy'
    ],
    2 => [
        'title' => 'Copy Include Files (with Asset Transformation)',
        'description' => 'Copies 3 include folders and transforms Utility::addModule*() calls to Rapidkart-style $theme->addCss()/addScript() calls.',
        'folders' => [
            'system/includes/graph',
            'system/includes/data-filter',
            'system/includes/dashboard',
        ],
        'type' => 'copy_includes_transformed'
    ],
    3 => [
        'title' => 'Copy Graph Templates',
        'description' => 'Copies template files for the Graph module (list, creator, view pages).',
        'files' => [
            'system/templates/graph/graph-list.php',
            'system/templates/graph/graph-creator.php',
            'system/templates/graph/graph-view.php',
        ],
        'type' => 'copy'
    ],
    4 => [
        'title' => 'Copy Data Filter Templates',
        'description' => 'Copies template files for the Data Filter module (list, form pages).',
        'files' => [
            'system/templates/data-filter/data-filter-list.php',
            'system/templates/data-filter/data-filter-form.php',
        ],
        'type' => 'copy'
    ],
    5 => [
        'title' => 'Copy Dashboard Templates',
        'description' => 'Copies template files for the Dashboard module (list, builder, preview, template management).',
        'files' => [
            'system/templates/dashboard/dashboard-list.php',
            'system/templates/dashboard/dashboard-builder.php',
            'system/templates/dashboard/dashboard-preview.php',
            'system/templates/dashboard/template-list.php',
            'system/templates/dashboard/template-editor.php',
            'system/templates/dashboard/template-builder.php',
            'system/templates/dashboard/template-preview.php',
        ],
        'type' => 'copy'
    ],
    6 => [
        'title' => 'Copy Page Scripts',
        'description' => 'Copies JavaScript files for page-specific functionality (delete handlers, initializers).',
        'files' => [
            'system/scripts/graph/graph-list.js',
            'system/scripts/graph/graph-creator.js',
            'system/scripts/data-filter/data-filter-list.js',
            'system/scripts/dashboard/dashboard-list.js',
            'system/scripts/dashboard/dashboard-builder.js',
            'system/scripts/dashboard/dashboard-preview.js',
            'system/scripts/dashboard/template-list.js',
            'system/scripts/dashboard/template-editor.js',
            'system/scripts/dashboard/template-builder.js',
            'system/scripts/dashboard/template-preview.js',
        ],
        'type' => 'copy'
    ],
    7 => [
        'title' => 'Copy Compiled Assets (dist/)',
        'description' => 'Copies compiled CSS/JS bundles to module-specific folders and removes hashes (e.g., common.abc123.css → system/styles/common/common.css).',
        'folder' => 'dist',
        'type' => 'copy_dist_renamed'
    ],
    8 => [
        'title' => 'Copy Theme Libraries',
        'description' => 'Copies JavaScript/CSS libraries with versioned folders to avoid conflicts with existing libraries.',
        'libraries' => [
            // Versioned libraries (to avoid conflicts)
            ['source' => 'themes/libraries/bootstrap', 'target' => 'themes/libraries/bootstrap5'],
            ['source' => 'themes/libraries/jquery', 'target' => 'themes/libraries/jquery3'],
            ['source' => 'themes/libraries/fontawesome', 'target' => 'themes/libraries/fontawesome6'],
            ['source' => 'themes/libraries/moment', 'target' => 'themes/libraries/moment2'],
            // New libraries (no version conflict)
            ['source' => 'themes/libraries/echarts', 'target' => 'themes/libraries/echarts'],
            ['source' => 'themes/libraries/codemirror', 'target' => 'themes/libraries/codemirror'],
            ['source' => 'themes/libraries/daterangepicker', 'target' => 'themes/libraries/daterangepicker'],
            ['source' => 'themes/libraries/autosize', 'target' => 'themes/libraries/autosize'],
            ['source' => 'themes/libraries/sortablejs', 'target' => 'themes/libraries/sortablejs'],
        ],
        'type' => 'copy_libraries_versioned'
    ],
    9 => [
        'title' => 'Run Database Setup',
        'description' => 'Shows the SQL that needs to be executed to create tables and insert system data.',
        'type' => 'sql_preview'
    ],
    10 => [
        'title' => 'Code Modifications Required',
        'description' => 'Manual changes needed: add routes to system.inc.php and update asset loading in include files.',
        'type' => 'code_modifications'
    ],
];

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Process action
$result = null;
$error = null;

if ($action === 'execute' && $step > 0 && isset($steps[$step])) {
    $stepData = $steps[$step];

    try {
        if ($stepData['type'] === 'copy') {
            $result = copyFiles($sourceDir, $targetDir, $stepData['files']);
        } elseif ($stepData['type'] === 'copy_folder') {
            $result = copyFolder($sourceDir . '/' . $stepData['folder'], $targetDir . '/' . $stepData['folder']);
        } elseif ($stepData['type'] === 'copy_folders') {
            $results = [];
            foreach ($stepData['folders'] as $folder) {
                $srcFolder = $sourceDir . '/' . $folder;
                $dstFolder = $targetDir . '/' . $folder;
                if (is_dir($srcFolder)) {
                    $results[$folder] = copyFolder($srcFolder, $dstFolder);
                }
            }
            $result = $results;
        } elseif ($stepData['type'] === 'copy_libraries') {
            $results = [];
            foreach ($stepData['folders'] as $folder) {
                $srcFolder = $sourceDir . '/' . $folder;
                $dstFolder = $targetDir . '/' . $folder;
                if (is_dir($srcFolder)) {
                    $results[$folder] = copyFolder($srcFolder, $dstFolder);
                }
            }
            $result = $results;
        } elseif ($stepData['type'] === 'copy_libraries_versioned') {
            $results = [];
            foreach ($stepData['libraries'] as $lib) {
                $srcFolder = $sourceDir . '/' . $lib['source'];
                $dstFolder = $targetDir . '/' . $lib['target'];
                if (is_dir($srcFolder)) {
                    $results[$lib['target']] = copyFolder($srcFolder, $dstFolder);
                }
            }
            $result = $results;
        } elseif ($stepData['type'] === 'copy_includes_transformed') {
            // Copy include folders with asset loading transformation
            $results = [];
            foreach ($stepData['folders'] as $folder) {
                $srcFolder = $sourceDir . '/' . $folder;
                $dstFolder = $targetDir . '/' . $folder;
                if (is_dir($srcFolder)) {
                    $results[$folder] = copyIncludesTransformed($srcFolder, $dstFolder);
                }
            }
            $result = $results;
        } elseif ($stepData['type'] === 'copy_dist_renamed') {
            // Copy to module-specific folders (system/styles/module/ and system/scripts/module/)
            $result = copyDistRenamed($sourceDir . '/' . $stepData['folder'], $targetDir);
        }

        // If AJAX request, return JSON response
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'step' => $step,
                'title' => $stepData['title'],
                'result' => $result,
                'timestamp' => time()
            ]);
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();

        // If AJAX request, return JSON error
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'step' => $step,
                'error' => $error,
                'timestamp' => time()
            ]);
            exit;
        }
    }
}

// Helper functions
function copyFiles($sourceDir, $targetDir, $files) {
    $results = [];
    foreach ($files as $file) {
        $src = $sourceDir . '/' . $file;
        $dst = $targetDir . '/' . $file;

        // Create directory if needed
        $dstDir = dirname($dst);
        if (!is_dir($dstDir)) {
            mkdir($dstDir, 0755, true);
        }

        if (file_exists($src)) {
            $copied = copy($src, $dst);
            $results[$file] = $copied ? 'success' : 'failed';
        } else {
            $results[$file] = 'source not found';
        }
    }
    return $results;
}

function copyFolder($src, $dst) {
    $results = ['files' => 0, 'dirs' => 0];

    if (!is_dir($src)) {
        return ['error' => 'Source folder not found: ' . $src];
    }

    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
        $results['dirs']++;
    }

    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;

        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;

        if (is_dir($srcPath)) {
            $subResult = copyFolder($srcPath, $dstPath);
            $results['files'] += $subResult['files'];
            $results['dirs'] += $subResult['dirs'];
        } else {
            copy($srcPath, $dstPath);
            $results['files']++;
        }
    }
    closedir($dir);

    return $results;
}

/**
 * Copy include files with asset loading transformation
 * Replaces Utility::addModule*() calls with Rapidkart-style $theme->addCss()/addScript() calls
 */
function copyIncludesTransformed($src, $dst) {
    $results = ['files' => 0, 'dirs' => 0, 'transformed' => 0];

    if (!is_dir($src)) {
        return ['error' => 'Source folder not found: ' . $src];
    }

    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
        $results['dirs']++;
    }

    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;

        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;

        if (is_dir($srcPath)) {
            $subResult = copyIncludesTransformed($srcPath, $dstPath);
            $results['files'] += $subResult['files'];
            $results['dirs'] += $subResult['dirs'];
            $results['transformed'] += $subResult['transformed'];
        } else {
            // Only transform .inc.php files
            if (strpos($file, '.inc.php') !== false) {
                $content = file_get_contents($srcPath);
                $originalContent = $content;

                // Transform Utility::addModuleCss() calls
                $content = preg_replace(
                    "/Utility::addModuleCss\('([^']+)'\);/",
                    "\$theme->addCss(SystemConfig::stylesUrl() . '$1/$1.css');",
                    $content
                );

                // Transform Utility::addModuleJs() calls
                $content = preg_replace(
                    "/Utility::addModuleJs\('([^']+)'\);/",
                    "\$theme->addScript(SystemConfig::scriptsUrl() . '$1/$1.js');",
                    $content
                );

                file_put_contents($dstPath, $content);

                if ($content !== $originalContent) {
                    $results['transformed']++;
                }
            } else {
                copy($srcPath, $dstPath);
            }
            $results['files']++;
        }
    }
    closedir($dir);

    return $results;
}

function getFileStatus($sourceDir, $targetDir, $file) {
    $src = $sourceDir . '/' . $file;
    $dst = $targetDir . '/' . $file;

    $srcExists = file_exists($src);
    $dstExists = file_exists($dst);

    if (!$srcExists) return 'missing';
    if (!$dstExists) return 'new';

    // Compare file contents
    if (md5_file($src) === md5_file($dst)) {
        return 'same';
    }
    return 'different';
}

function getFolderStatus($sourceDir, $targetDir, $folder) {
    $src = $sourceDir . '/' . $folder;
    $dst = $targetDir . '/' . $folder;

    if (!is_dir($src)) return 'missing';
    if (!is_dir($dst)) return 'new';
    return 'exists';
}

/**
 * Get status for versioned library (source and target have different paths)
 */
function getVersionedLibraryStatus($sourceDir, $targetDir, $sourcePath, $targetPath) {
    $src = $sourceDir . '/' . $sourcePath;
    $dst = $targetDir . '/' . $targetPath;

    if (!is_dir($src)) return 'missing';
    if (!is_dir($dst)) return 'new';
    return 'exists';
}

function copyDistRenamed($src, $targetDir) {
    $results = ['copied' => [], 'skipped' => []];

    if (!is_dir($src)) {
        return ['error' => 'Source folder not found: ' . $src];
    }

    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;

        $srcPath = $src . '/' . $file;

        // Skip .map files and manifest.json
        if (strpos($file, '.map') !== false || $file === 'manifest.json') {
            $results['skipped'][] = $file;
            continue;
        }

        // Remove hash from filename: common.abc123.css -> common.css
        // Pattern: modulename.hash.ext -> modulename.ext
        // Route to module-specific folder: system/styles/modulename/ or system/scripts/modulename/
        if (preg_match('/^(.+)\.([a-f0-9]{8})\.([a-z]+)$/', $file, $matches)) {
            $moduleName = $matches[1]; // e.g., "common", "graph", "data-filter", "dashboard"
            $ext = $matches[3];
            $newName = $moduleName . '.' . $ext;

            // Route to module-specific folder based on file type
            if ($ext === 'css') {
                $moduleDir = $targetDir . '/system/styles/' . $moduleName;
            } else if ($ext === 'js') {
                $moduleDir = $targetDir . '/system/scripts/' . $moduleName;
            } else {
                // Skip unknown extensions
                $results['skipped'][] = $file;
                continue;
            }

            // Create module directory if needed
            if (!is_dir($moduleDir)) {
                mkdir($moduleDir, 0755, true);
            }

            $dstPath = $moduleDir . '/' . $newName;
            copy($srcPath, $dstPath);

            // Store with full relative path for display
            $relativePath = ($ext === 'css' ? 'system/styles/' : 'system/scripts/') . $moduleName . '/' . $newName;
            $results['copied'][$file] = $relativePath;
        } else {
            // Skip files without hash pattern (shouldn't happen with build output)
            $results['skipped'][] = $file;
        }
    }
    closedir($dir);

    return $results;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration Tool - Dynamic Graph Creator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .migration-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .step-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 1rem;
            transition: all 0.2s;
        }
        .step-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        .step-card.completed {
            border-left: 4px solid #28a745;
        }
        .step-card.pending {
            border-left: 4px solid #ffc107;
        }
        .step-number {
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 20px;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        .file-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.875rem;
        }
        .file-item {
            padding: 0.25rem 0;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .file-item-simple {
            padding: 0.25rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .file-number {
            font-size: 0.75rem;
            color: #6c757d;
            min-width: 1.5rem;
            text-align: right;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }
        .status-new { background: #d4edda; color: #155724; }
        .status-same { background: #e2e3e5; color: #383d41; }
        .status-different { background: #fff3cd; color: #856404; }
        .status-missing { background: #f8d7da; color: #721c24; }
        .type-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.4rem;
            border-radius: 3px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .type-css { background: #264de4; color: white; }
        .type-js { background: #f0db4f; color: #323330; }
        /* Module badges */
        .module-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.4rem;
            border-radius: 3px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .module-common { background: #6f42c1; color: white; }
        .module-graph { background: #0d6efd; color: white; }
        .module-data-filter { background: #198754; color: white; }
        .module-dashboard { background: #dc3545; color: white; }
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            border-radius: 8px;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.8rem;
            overflow: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 300px;
            width: 100%;
            box-sizing: border-box;
        }
        .utility-method {
            max-width: 100%;
            overflow: hidden;
        }
        .target-path {
            background: #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.875rem;
        }
        .alert-custom {
            border-radius: 8px;
            border: none;
        }
        /* Last execution time badges */
        .last-exec-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .last-exec-badge i {
            font-size: 0.65rem;
        }
        .exec-recent {
            background: #d4edda;
            color: #155724;
        }
        .exec-today {
            background: #cce5ff;
            color: #004085;
        }
        .exec-old {
            background: #e2e3e5;
            color: #383d41;
        }
        /* Toast notifications (exact match from compiled common.css) */
        .dgc-toast-container {
            position: fixed;
            top: 5px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1100;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .dgc-toast {
            display: flex !important;
            align-items: center;
            gap: 16px;
            padding: 16px 24px;
            padding-left: 16px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 300px;
            animation: dgcSlideIn 0.3s ease;
            color: #212529;
        }
        .dgc-toast .dgc-toast-indicator {
            width: 4px;
            align-self: stretch;
            border-radius: 2px;
            flex-shrink: 0;
        }
        .dgc-toast.success .dgc-toast-indicator { background-color: #4caf50; }
        .dgc-toast.success i { color: #4caf50; }
        .dgc-toast.error .dgc-toast-indicator { background-color: #f44336; }
        .dgc-toast.error i { color: #f44336; }
        .dgc-toast.warning .dgc-toast-indicator { background-color: #ff9800; }
        .dgc-toast.warning i { color: #ff9800; }
        .dgc-toast.info .dgc-toast-indicator { background-color: #2196f3; }
        .dgc-toast.info i { color: #2196f3; }
        .dgc-toast i { font-size: 20px; }
        .dgc-toast .dgc-toast-message { flex: 1; color: #212529; }
        .dgc-toast .dgc-toast-close {
            background: transparent;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 4px;
        }
        .dgc-toast .dgc-toast-close:hover { color: #212529; }
        @keyframes dgcSlideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="migration-header">
        <div class="container">
            <h1><i class="fas fa-truck-moving me-2"></i>Migration Tool</h1>
            <p class="mb-0 opacity-75">Dynamic Graph Creator &rarr; Rapidkart Process Admin</p>
        </div>
    </div>

    <div class="container pb-5">
        <?php if (!$targetExists): ?>
        <div class="alert alert-danger alert-custom">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Target directory not found:</strong> <?php echo htmlspecialchars($targetDir); ?>
            <br><small>Please update the <code>$targetDir</code> variable in this file.</small>
        </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="target-path">
                    <strong>Source:</strong> <?php echo htmlspecialchars($sourceDir); ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="target-path">
                    <strong>Target:</strong> <?php echo htmlspecialchars($targetDir); ?>
                </div>
            </div>
        </div>

        <?php if ($result !== null): ?>
        <div class="alert alert-success alert-custom mb-4">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Step <?php echo $step; ?> completed successfully!</strong>
            <?php if (is_array($result)): ?>
            <div class="mt-2 small">
                <?php
                if (isset($result['files'])) {
                    echo "Copied {$result['files']} files, created {$result['dirs']} directories";
                } else {
                    foreach ($result as $file => $status) {
                        if (is_array($status)) {
                            echo "<br>{$file}: {$status['files']} files";
                        } else {
                            $icon = $status === 'success' ? '✓' : '✗';
                            echo "<br>{$icon} {$file}";
                        }
                    }
                }
                ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-custom mb-4">
            <i class="fas fa-times-circle me-2"></i>
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <h4 class="mb-3">Migration Steps</h4>

        <?php foreach ($steps as $num => $stepData): ?>
        <div class="card step-card" data-step-card="<?php echo $num; ?>">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="step-number"><?php echo $num; ?></div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="mb-0"><?php echo htmlspecialchars($stepData['title']); ?></h5>
                            <span class="last-exec-badge" data-step-exec="<?php echo $num; ?>" style="display:none;"></span>
                        </div>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($stepData['description']); ?></p>

                        <?php if ($stepData['type'] === 'copy' && isset($stepData['files'])): ?>
                        <?php
                        $existingCount = 0;
                        $fileStatuses = [];
                        foreach ($stepData['files'] as $file) {
                            $status = $targetExists ? getFileStatus($sourceDir, $targetDir, $file) : 'unknown';
                            $fileStatuses[$file] = $status;
                            if ($status === 'exists' || $status === 'different') $existingCount++;
                        }
                        ?>
                        <div class="file-list mb-3">
                            <?php $fileNum = 1; foreach ($stepData['files'] as $file): ?>
                            <div class="file-item-simple">
                                <span class="file-number"><?php echo $fileNum++; ?>.</span>
                                <span class="status-badge status-<?php echo $fileStatuses[$file]; ?>">
                                    <?php echo $fileStatuses[$file]; ?>
                                </span>
                                <?php echo htmlspecialchars($file); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php
                              $confirmTitle = 'Copy ' . count($stepData['files']) . ' Files';
                              $confirmMsg = 'Copy ' . count($stepData['files']) . ' files to target directory?';
                              $hasWarning = $existingCount > 0;
                              $warningMsg = $existingCount . ' file(s) already exist and will be OVERWRITTEN!';
                              // Get unique directory for counting
                              $countDir = dirname($stepData['files'][0]); ?>
                        <button type="button" class="btn btn-primary btn-sm confirm-action-btn"
                                data-action-url="?action=execute&step=<?php echo $num; ?>"
                                data-step="<?php echo $num; ?>"
                                data-title="<?php echo htmlspecialchars($confirmTitle); ?>"
                                data-message="<?php echo htmlspecialchars($confirmMsg); ?>"
                                data-has-warning="<?php echo $hasWarning ? '1' : '0'; ?>"
                                data-warning="<?php echo htmlspecialchars($warningMsg); ?>">
                            <i class="fas fa-copy me-1"></i> Copy Files
                        </button>
                        <?php
                        // Get unique directories from file list
                        $uniqueDirs = array();
                        foreach ($stepData['files'] as $file) {
                            $dir = dirname($file);
                            if (!in_array($dir, $uniqueDirs)) {
                                $uniqueDirs[] = $dir;
                            }
                        }
                        // If multiple directories, show a button for each
                        if (count($uniqueDirs) > 1): ?>
                        <?php foreach ($uniqueDirs as $dir): ?>
                        <button class="btn btn-outline-info btn-sm count-target-btn ms-1"
                                data-path="<?php echo htmlspecialchars($dir); ?>"
                                data-recursive="0"
                                title="Count files in <?php echo htmlspecialchars($dir); ?>">
                            <i class="fas fa-calculator me-1"></i> <?php echo htmlspecialchars(basename($dir)); ?>
                        </button>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <button class="btn btn-outline-info btn-sm count-target-btn"
                                data-path="<?php echo htmlspecialchars($countDir); ?>"
                                data-recursive="0">
                            <i class="fas fa-calculator me-1"></i> Count Target
                        </button>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'copy_folder'): ?>
                        <?php $status = $targetExists ? getFolderStatus($sourceDir, $targetDir, $stepData['folder']) : 'unknown'; ?>
                        <div class="file-list mb-3">
                            <div class="file-item-simple">
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <?php echo $status; ?>
                                </span>
                                <?php echo htmlspecialchars($stepData['folder']); ?>/
                            </div>
                            <?php
                            // List files in folder
                            $folderPath = $sourceDir . '/' . $stepData['folder'];
                            if (is_dir($folderPath)) {
                                $files = scandir($folderPath);
                                foreach ($files as $f) {
                                    if ($f !== '.' && $f !== '..') {
                                        echo '<div class="file-item-simple ps-4">&nbsp;&nbsp;' . htmlspecialchars($f) . '</div>';
                                    }
                                }
                            }
                            ?>
                        </div>
                        <?php
                              $confirmTitle = 'Copy Folder';
                              $confirmMsg = 'Copy entire ' . $stepData['folder'] . '/ folder to target directory?';
                              $hasWarning = $status === 'exists';
                              $warningMsg = 'Folder already exists - files will be OVERWRITTEN!'; ?>
                        <button type="button" class="btn btn-primary btn-sm confirm-action-btn"
                                data-action-url="?action=execute&step=<?php echo $num; ?>"
                                data-step="<?php echo $num; ?>"
                                data-title="<?php echo htmlspecialchars($confirmTitle); ?>"
                                data-message="<?php echo htmlspecialchars($confirmMsg); ?>"
                                data-has-warning="<?php echo $hasWarning ? '1' : '0'; ?>"
                                data-warning="<?php echo htmlspecialchars($warningMsg); ?>">
                            <i class="fas fa-folder me-1"></i> Copy Folder
                        </button>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'copy_dist_renamed'): ?>
                        <?php $status = $targetExists ? getFolderStatus($sourceDir, $targetDir, $stepData['folder']) : 'unknown'; ?>
                        <div class="file-list mb-3">
                            <?php
                            $folderPath = $sourceDir . '/' . $stepData['folder'];
                            $assetNum = 1;
                            if (is_dir($folderPath)) {
                                $files = scandir($folderPath);
                                // Separate CSS and JS files
                                $cssFiles = [];
                                $jsFiles = [];
                                foreach ($files as $f) {
                                    if ($f === '.' || $f === '..' || strpos($f, '.map') !== false || $f === 'manifest.json') continue;
                                    if (preg_match('/^(.+)\.([a-f0-9]{8})\.([a-z]+)$/', $f, $matches)) {
                                        $moduleName = $matches[1];
                                        $ext = $matches[3];
                                        $newName = $moduleName . '.' . $ext;
                                        // Build module-specific target path
                                        if ($ext === 'css') {
                                            $targetPath = 'system/styles/' . $moduleName . '/' . $newName;
                                            $cssFiles[] = ['source' => $f, 'target' => $targetPath, 'module' => $moduleName];
                                        } else if ($ext === 'js') {
                                            $targetPath = 'system/scripts/' . $moduleName . '/' . $newName;
                                            $jsFiles[] = ['source' => $f, 'target' => $targetPath, 'module' => $moduleName];
                                        }
                                    }
                                }
                                // Display CSS files
                                foreach ($cssFiles as $file) {
                                    $moduleClass = 'module-' . $file['module'];
                                    echo '<div class="file-item">';
                                    echo '<span class="file-number">' . $assetNum++ . '.</span>';
                                    echo '<span class="type-badge type-css">CSS</span>';
                                    echo '<span class="module-badge ' . $moduleClass . '">' . strtoupper($file['module']) . '</span>';
                                    echo '<span class="text-muted">' . htmlspecialchars($file['source']) . '</span>';
                                    echo '<span>&rarr;</span>';
                                    echo '<strong>' . htmlspecialchars($file['target']) . '</strong>';
                                    echo '</div>';
                                }
                                // Display JS files
                                foreach ($jsFiles as $file) {
                                    $moduleClass = 'module-' . $file['module'];
                                    echo '<div class="file-item">';
                                    echo '<span class="file-number">' . $assetNum++ . '.</span>';
                                    echo '<span class="type-badge type-js">JS</span>';
                                    echo '<span class="module-badge ' . $moduleClass . '">' . strtoupper($file['module']) . '</span>';
                                    echo '<span class="text-muted">' . htmlspecialchars($file['source']) . '</span>';
                                    echo '<span>&rarr;</span>';
                                    echo '<strong>' . htmlspecialchars($file['target']) . '</strong>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <p class="small text-muted mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Files are copied to module-specific folders matching Rapidkart's asset structure.
                        </p>
                        <?php
                              $confirmTitle = 'Copy & Rename Assets';
                              $confirmMsg = 'Copy and rename dist files to module-specific folders?';
                              $hasWarning = $status === 'exists';
                              $warningMsg = 'Some folders may already exist - files will be OVERWRITTEN!'; ?>
                        <button type="button" class="btn btn-primary btn-sm confirm-action-btn"
                                data-action-url="?action=execute&step=<?php echo $num; ?>"
                                data-step="<?php echo $num; ?>"
                                data-title="<?php echo htmlspecialchars($confirmTitle); ?>"
                                data-message="<?php echo htmlspecialchars($confirmMsg); ?>"
                                data-has-warning="<?php echo $hasWarning ? '1' : '0'; ?>"
                                data-warning="<?php echo htmlspecialchars($warningMsg); ?>">
                            <i class="fas fa-folder me-1"></i> Copy &amp; Rename
                        </button>
                        <button class="btn btn-outline-info btn-sm count-target-btn ms-1"
                                data-path="system/styles"
                                data-recursive="1"
                                title="Count CSS files in system/styles">
                            <i class="fas fa-calculator me-1"></i> Styles
                        </button>
                        <button class="btn btn-outline-info btn-sm count-target-btn ms-1"
                                data-path="system/scripts"
                                data-recursive="1"
                                title="Count JS files in system/scripts">
                            <i class="fas fa-calculator me-1"></i> Scripts
                        </button>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'copy_folders'): ?>
                        <?php $folderExistCount = 0; ?>
                        <div class="file-list mb-3">
                            <?php $folderNum = 1; foreach ($stepData['folders'] as $folder): ?>
                            <?php $status = $targetExists ? getFolderStatus($sourceDir, $targetDir, $folder) : 'unknown';
                                  if ($status === 'exists') $folderExistCount++; ?>
                            <div class="file-item-simple">
                                <span class="file-number"><?php echo $folderNum++; ?>.</span>
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <?php echo $status; ?>
                                </span>
                                <?php echo htmlspecialchars($folder); ?>/
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php
                              $confirmTitle = 'Copy Folders';
                              $confirmMsg = 'Copy folders to target directory?';
                              $hasWarning = $folderExistCount > 0;
                              $warningMsg = $folderExistCount . ' folder(s) already exist and will be OVERWRITTEN!'; ?>
                        <button type="button" class="btn btn-primary btn-sm confirm-action-btn"
                                data-action-url="?action=execute&step=<?php echo $num; ?>"
                                data-step="<?php echo $num; ?>"
                                data-title="<?php echo htmlspecialchars($confirmTitle); ?>"
                                data-message="<?php echo htmlspecialchars($confirmMsg); ?>"
                                data-has-warning="<?php echo $hasWarning ? '1' : '0'; ?>"
                                data-warning="<?php echo htmlspecialchars($warningMsg); ?>">
                            <i class="fas fa-folder me-1"></i> Copy Folders
                        </button>
                        <?php foreach ($stepData['folders'] as $folder): ?>
                        <button class="btn btn-outline-info btn-sm count-target-btn ms-1"
                                data-path="<?php echo htmlspecialchars($folder); ?>"
                                data-recursive="1"
                                title="Count files in <?php echo htmlspecialchars(basename($folder)); ?>">
                            <i class="fas fa-calculator me-1"></i> <?php echo htmlspecialchars(basename($folder)); ?>
                        </button>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'copy_libraries'): ?>
                        <?php $libExistCount = 0; ?>
                        <div class="file-list mb-3">
                            <?php $libNum = 1; foreach ($stepData['folders'] as $folder): ?>
                            <?php $status = $targetExists ? getFolderStatus($sourceDir, $targetDir, $folder) : 'unknown';
                                  if ($status === 'exists') $libExistCount++; ?>
                            <div class="file-item-simple">
                                <span class="file-number"><?php echo $libNum++; ?>.</span>
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <?php echo $status; ?>
                                </span>
                                <?php echo htmlspecialchars($folder); ?>/
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php
                              $confirmTitle = 'Copy Libraries';
                              $confirmMsg = 'Copy library folders to target directory?';
                              $hasWarning = $libExistCount > 0;
                              $warningMsg = $libExistCount . ' folder(s) already exist and will be OVERWRITTEN!'; ?>
                        <button type="button" class="btn btn-primary btn-sm confirm-action-btn"
                                data-action-url="?action=execute&step=<?php echo $num; ?>"
                                data-step="<?php echo $num; ?>"
                                data-title="<?php echo htmlspecialchars($confirmTitle); ?>"
                                data-message="<?php echo htmlspecialchars($confirmMsg); ?>"
                                data-has-warning="<?php echo $hasWarning ? '1' : '0'; ?>"
                                data-warning="<?php echo htmlspecialchars($warningMsg); ?>">
                            <i class="fas fa-folder me-1"></i> Copy Libraries
                        </button>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'copy_libraries_versioned'): ?>
                        <?php $libExistCount = 0; ?>
                        <div class="file-list mb-3">
                            <?php $libVerNum = 1; foreach ($stepData['libraries'] as $lib): ?>
                            <?php $status = $targetExists ? getVersionedLibraryStatus($sourceDir, $targetDir, $lib['source'], $lib['target']) : 'unknown';
                                  if ($status === 'exists') $libExistCount++; ?>
                            <div class="file-item-simple">
                                <span class="file-number"><?php echo $libVerNum++; ?>.</span>
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <?php echo $status; ?>
                                </span>
                                <?php echo htmlspecialchars(basename($lib['source'])); ?>/ &rarr; <strong><?php echo htmlspecialchars($lib['target']); ?>/</strong>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="small text-muted mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Libraries will be copied to versioned folders (e.g., bootstrap5/, jquery3/) to avoid conflicts with existing libraries.
                        </p>
                        <?php
                              $confirmTitle = 'Copy Versioned Libraries';
                              $confirmMsg = 'Copy library folders to versioned directories?';
                              $hasWarning = $libExistCount > 0;
                              $warningMsg = $libExistCount . ' folder(s) already exist and will be OVERWRITTEN!'; ?>
                        <button type="button" class="btn btn-primary btn-sm confirm-action-btn"
                                data-action-url="?action=execute&step=<?php echo $num; ?>"
                                data-step="<?php echo $num; ?>"
                                data-title="<?php echo htmlspecialchars($confirmTitle); ?>"
                                data-message="<?php echo htmlspecialchars($confirmMsg); ?>"
                                data-has-warning="<?php echo $hasWarning ? '1' : '0'; ?>"
                                data-warning="<?php echo htmlspecialchars($warningMsg); ?>">
                            <i class="fas fa-folder me-1"></i> Copy Libraries
                        </button>
                        <button class="btn btn-outline-info btn-sm count-target-btn ms-1"
                                data-path="themes/libraries"
                                data-count-type="folders"
                                title="Count folders in themes/libraries">
                            <i class="fas fa-calculator me-1"></i> Count Libraries
                        </button>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'sql_preview'): ?>
                        <div class="mb-3">
                            <p class="small text-muted">
                                Run the SQL file <code>sql/install.sql</code> in your database.
                                This creates the following tables:
                            </p>
                            <ul class="small">
                                <li><strong>graph</strong> - Graph definitions</li>
                                <li><strong>data_filter</strong> - Data filter definitions</li>
                                <li><strong>dashboard_template_category</strong> - 4 system categories</li>
                                <li><strong>dashboard_template</strong> - 16 system templates</li>
                                <li><strong>dashboard_instance</strong> - User dashboards</li>
                            </ul>
                            <a href="sql/install.sql" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-eye me-1"></i> View SQL File
                            </a>
                            <a href="sql/install.sql" download class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download me-1"></i> Download SQL
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'code_modifications'): ?>
                        <div class="mb-3">
                            <h6 class="text-primary"><i class="fas fa-route me-1"></i> 1. Add Routes to system.inc.php</h6>
                            <p class="small text-muted mb-2">
                                Add these cases in the <code>switch ($url[0])</code> section of <code>system/includes/system.inc.php</code>:
                            </p>
                            <div class="d-flex justify-content-end mb-1">
                                <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="routes-code">
                                    <i class="fas fa-copy me-1"></i> Copy
                                </button>
                            </div>
                            <div class="code-block mb-3" id="routes-code">case "graph":
    include_once 'graph/graph.inc.php';
    break;

case "data-filter":
    include_once 'data-filter/data-filter.inc.php';
    break;

case "dashboard":
    include_once 'dashboard/dashboard.inc.php';
    break;</div>

                            <h6 class="text-primary mt-3"><i class="fas fa-check-circle me-1"></i> 2. Asset Loading (Automatic)</h6>
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-magic me-1"></i>
                                <strong>Step 2 handles this automatically!</strong> The include files are copied with <code>Utility::addModule*()</code> calls transformed to Rapidkart-style <code>$theme->addCss()</code> / <code>$theme->addScript()</code> calls.
                            </div>
                            <p class="small text-muted mb-2">
                                <strong>Transformation applied:</strong>
                            </p>
                            <div class="code-block mb-3">Utility::addModuleCss('common')  →  $theme->addCss(SystemConfig::stylesUrl() . 'common/common.css')
Utility::addModuleCss('graph')   →  $theme->addCss(SystemConfig::stylesUrl() . 'graph/graph.css')
Utility::addModuleJs('common')   →  $theme->addScript(SystemConfig::scriptsUrl() . 'common/common.js')
Utility::addModuleJs('graph')    →  $theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph.js')</div>
                            <p class="small text-muted mb-3">
                                Page-specific scripts and external libraries remain unchanged - they already use the correct Rapidkart pattern.
                            </p>

                            <a href="docs/migration.md" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-book me-1"></i> View Full Documentation
                            </a>
                        </div>
                        <?php endif; ?>
<!-- END OF CODE_MODIFICATIONS CONTENT - OLD ASSET LOADING BLOCKS REMOVED -->
<?php if (false): // Old detailed asset loading blocks - no longer needed since transformation is automatic ?>
                            <!-- graph.inc.php -->
                            <div class="asset-loading-file mb-4">
                                <h6 class="text-info mb-2"><i class="fas fa-file-code me-1"></i> graph.inc.php</h6>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">Top of File</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="graph-top">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="graph-top">// Load graph module assets (once at top of file)
$theme = Rapidkart::getInstance()->getThemeRegistry();
$theme->addCss(SystemConfig::stylesUrl() . 'common/common.css');
$theme->addCss(SystemConfig::stylesUrl() . 'graph/graph.css');
$theme->addScript(SystemConfig::scriptsUrl() . 'common/common.js');
$theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph.js');
$theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showList()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="graph-showList">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="graph-showList">// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph-list.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showCreator()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="graph-showCreator">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="graph-showCreator">// Add libraries
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);
$theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror/css/codemirror.min.css', 5);
$theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror/css/material.min.css', 6);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror/js/codemirror.min.js', 6);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror/js/sql.min.js', 7);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize/autosize.min.js', 5);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery/jquery.min.js', 4);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment/moment.min.js', 5);
$theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker/css/daterangepicker.css', 5);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker/js/daterangepicker.min.js', 8);

// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph-creator.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showView()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="graph-showView">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="graph-showView">// Add libraries
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery/jquery.min.js', 4);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment/moment.min.js', 5);
$theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker/css/daterangepicker.css', 5);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker/js/daterangepicker.min.js', 8);</div>
                                </div>
                            </div>

                            <!-- data-filter.inc.php -->
                            <div class="asset-loading-file mb-4">
                                <h6 class="text-info mb-2"><i class="fas fa-file-code me-1"></i> data-filter.inc.php</h6>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">Top of File</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="datafilter-top">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="datafilter-top">// Load data-filter module assets (once at top of file)
$theme = Rapidkart::getInstance()->getThemeRegistry();
$theme->addCss(SystemConfig::stylesUrl() . 'common/common.css');
$theme->addCss(SystemConfig::stylesUrl() . 'data-filter/data-filter.css');
$theme->addScript(SystemConfig::scriptsUrl() . 'common/common.js');
$theme->addScript(SystemConfig::scriptsUrl() . 'data-filter/data-filter.js');
$theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showDataFilterList()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="datafilter-showList">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="datafilter-showList">// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'data-filter/data-filter-list.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showDataFilterForm()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="datafilter-showForm">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="datafilter-showForm">// Add libraries
$theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror/css/codemirror.min.css', 5);
$theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror/css/material.min.css', 6);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror/js/codemirror.min.js', 6);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'codemirror/js/sql.min.js', 7);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'jquery/jquery.min.js', 4);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'moment/moment.min.js', 5);
$theme->addCss(SiteConfig::themeLibrariessUrl() . 'daterangepicker/css/daterangepicker.css', 5);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'daterangepicker/js/daterangepicker.min.js', 8);</div>
                                </div>
                            </div>

                            <!-- dashboard.inc.php -->
                            <div class="asset-loading-file mb-4">
                                <h6 class="text-info mb-2"><i class="fas fa-file-code me-1"></i> dashboard.inc.php</h6>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">Top of File</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="dashboard-top">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="dashboard-top">// Load dashboard module assets (once at top of file)
$theme = Rapidkart::getInstance()->getThemeRegistry();
$theme->addCss(SystemConfig::stylesUrl() . 'common/common.css');
$theme->addCss(SystemConfig::stylesUrl() . 'dashboard/dashboard.css');
$theme->addScript(SystemConfig::scriptsUrl() . 'common/common.js');
$theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/dashboard.js');
$theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showList()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="dashboard-showList">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="dashboard-showList">// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/dashboard-list.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showBuilder()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="dashboard-showBuilder">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="dashboard-showBuilder">// Add libraries
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'sortablejs/Sortable.min.js', 5);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize/autosize.min.js', 5);

// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/dashboard-builder.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showPreview()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="dashboard-showPreview">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="dashboard-showPreview">// Add libraries
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);

// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/dashboard-preview.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showTemplateList()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="dashboard-showTemplateList">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="dashboard-showTemplateList">// Add libraries
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'sortablejs/Sortable.min.js', 5);

// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/template-list.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showTemplateCreator() / showTemplateEditor()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="dashboard-showTemplateEditor">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="dashboard-showTemplateEditor">// Add libraries
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize/autosize.min.js', 5);

// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/template-editor.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showTemplateBuilder()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="dashboard-showTemplateBuilder">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="dashboard-showTemplateBuilder">// Add libraries
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'sortablejs/Sortable.min.js', 5);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'autosize/autosize.min.js', 5);

// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/template-builder.js');</div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small">showTemplatePreview()</strong>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" data-target="dashboard-showTemplatePreview">
                                            <i class="fas fa-copy me-1"></i> Copy
                                        </button>
                                    </div>
                                    <div class="code-block" id="dashboard-showTemplatePreview">// Add libraries
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);

// Add page-specific JS
$theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/template-preview.js');</div>
                                </div>
                            </div>
<?php endif; // End of old asset loading blocks ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="card mt-4">
            <div class="card-body">
                <h5><i class="fas fa-clipboard-check me-2"></i>After Migration</h5>
                <p class="text-muted">Once all steps are complete, test the following URLs:</p>
                <ul class="mb-0">
                    <li><code>?urlq=graph/list</code> - Graph listing</li>
                    <li><code>?urlq=data-filter/list</code> - Data filter listing</li>
                    <li><code>?urlq=dashboard/list</code> - Dashboard listing</li>
                    <li><code>?urlq=dashboard/templates</code> - Template listing</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="confirm-message mb-0">Are you sure?</p>
                    <div class="alert alert-warning mt-3 mb-0 confirm-warning" style="display:none;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <span class="warning-text"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary confirm-execute">
                        <i class="fas fa-play me-1"></i> Execute
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Toast utility (matches common.js pattern)
    const Toast = {
        container: null,
        init() {
            this.container = document.createElement('div');
            this.container.className = 'dgc-toast-container';
            document.body.appendChild(this.container);
        },
        show(message, type = 'success', duration = 3000) {
            if (!this.container) this.init();
            const toast = document.createElement('div');
            toast.className = `dgc-toast ${type}`;
            const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
            toast.innerHTML = `<span class="dgc-toast-indicator"></span><i class="fas ${icons[type] || icons.success}"></i><span class="dgc-toast-message">${message}</span><button class="dgc-toast-close"><i class="fas fa-times"></i></button>`;
            toast.querySelector('.dgc-toast-close').addEventListener('click', () => toast.remove());
            this.container.appendChild(toast);
            if (duration > 0) setTimeout(() => toast.remove(), duration);
        },
        success(message) { this.show(message, 'success'); },
        error(message) { this.show(message, 'error', 5000); },
        warning(message) { this.show(message, 'warning', 4000); },
        info(message) { this.show(message, 'info'); }
    };

    // LocalStorage key for execution times
    const STORAGE_KEY = 'migrate_execution_times';

    // Get execution times from localStorage
    function getExecutionTimes() {
        const data = localStorage.getItem(STORAGE_KEY);
        return data ? JSON.parse(data) : {};
    }

    // Save execution time for a step
    function saveExecutionTime(step, timestamp) {
        const times = getExecutionTimes();
        times[step] = timestamp;
        localStorage.setItem(STORAGE_KEY, JSON.stringify(times));
    }

    // Format relative time
    function formatRelativeTime(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;

        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';

        const date = new Date(timestamp * 1000);
        return date.toLocaleDateString();
    }

    // Get badge class based on execution time
    function getExecBadgeClass(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;

        // Within last 10 minutes = green (recent)
        if (diff < 600) return 'exec-recent';

        // Same day = blue (today)
        const execDate = new Date(timestamp * 1000).toDateString();
        const todayDate = new Date().toDateString();
        if (execDate === todayDate) return 'exec-today';

        // Otherwise = gray (old)
        return 'exec-old';
    }

    // Update all execution badges on page load
    function updateAllExecBadges() {
        const times = getExecutionTimes();
        document.querySelectorAll('[data-step-exec]').forEach(badge => {
            const step = badge.getAttribute('data-step-exec');
            if (times[step]) {
                const badgeClass = getExecBadgeClass(times[step]);
                const relativeTime = formatRelativeTime(times[step]);
                badge.className = 'last-exec-badge ' + badgeClass;
                badge.innerHTML = '<i class="fas fa-check-circle"></i> ' + relativeTime;
                badge.style.display = 'inline-flex';
            }
        });
    }

    // Update single execution badge
    function updateExecBadge(step, timestamp) {
        const badge = document.querySelector('[data-step-exec="' + step + '"]');
        if (badge) {
            const badgeClass = getExecBadgeClass(timestamp);
            const relativeTime = formatRelativeTime(timestamp);
            badge.className = 'last-exec-badge ' + badgeClass;
            badge.innerHTML = '<i class="fas fa-check-circle"></i> ' + relativeTime;
            badge.style.display = 'inline-flex';
        }
    }

    // Confirmation Modal functionality
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    let pendingActionUrl = null;
    let pendingStep = null;

    document.querySelectorAll('.confirm-action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const title = this.getAttribute('data-title');
            const message = this.getAttribute('data-message');
            const hasWarning = this.getAttribute('data-has-warning') === '1';
            const warning = this.getAttribute('data-warning');

            pendingActionUrl = this.getAttribute('data-action-url');
            pendingStep = this.getAttribute('data-step');

            // Update modal content
            document.querySelector('#confirmModal .modal-title').textContent = title;
            document.querySelector('#confirmModal .confirm-message').textContent = message;

            const warningEl = document.querySelector('#confirmModal .confirm-warning');
            if (hasWarning) {
                document.querySelector('#confirmModal .warning-text').textContent = warning;
                warningEl.style.display = 'block';
            } else {
                warningEl.style.display = 'none';
            }

            confirmModal.show();
        });
    });

    // Execute action via AJAX
    document.querySelector('#confirmModal .confirm-execute').addEventListener('click', function() {
        if (!pendingActionUrl) return;

        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Executing...';
        btn.disabled = true;

        fetch(pendingActionUrl, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            confirmModal.hide();
            btn.innerHTML = originalHtml;
            btn.disabled = false;

            if (data.success) {
                // Save execution time
                saveExecutionTime(data.step, data.timestamp);
                updateExecBadge(data.step, data.timestamp);

                // Show success toast
                let resultMsg = 'Step ' + data.step + ' completed: ' + data.title;
                if (data.result) {
                    if (data.result.files !== undefined) {
                        resultMsg += ' (' + data.result.files + ' files)';
                    } else if (data.result.copied) {
                        resultMsg += ' (' + Object.keys(data.result.copied).length + ' files)';
                    }
                }
                Toast.success(resultMsg);
            } else {
                Toast.error('Error: ' + data.error);
            }
        })
        .catch(error => {
            confirmModal.hide();
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            Toast.error('Request failed: ' + error.message);
        });
    });

    // Copy button functionality
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const codeBlock = document.getElementById(targetId);
            if (codeBlock) {
                const text = codeBlock.textContent;
                navigator.clipboard.writeText(text).then(() => {
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-success');
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-secondary');
                    }, 2000);
                });
            }
        });
    });

    // Count target files button functionality
    document.querySelectorAll('.count-target-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const path = this.getAttribute('data-path');
            const recursive = this.getAttribute('data-recursive') || '0';
            const countType = this.getAttribute('data-count-type') || 'files';
            const originalHtml = this.innerHTML;

            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Counting...';
            this.disabled = true;

            fetch('?action=count_files&path=' + encodeURIComponent(path) + '&recursive=' + recursive + '&count_type=' + countType)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        const icon = data.type === 'folders' ? 'fa-folder' : 'fa-file';
                        const label = data.type === 'folders' ? 'folders' : 'files';
                        this.innerHTML = '<i class="fas ' + icon + ' me-1"></i> ' + data.count + ' ' + label;
                        this.classList.remove('btn-outline-info');
                        this.classList.add('btn-info');
                    } else {
                        this.innerHTML = '<i class="fas fa-folder-open me-1"></i> Not found';
                        this.classList.remove('btn-outline-info');
                        this.classList.add('btn-warning');
                    }
                    this.disabled = false;

                    // Reset after 5 seconds
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                        this.classList.remove('btn-info', 'btn-warning');
                        this.classList.add('btn-outline-info');
                    }, 5000);
                })
                .catch(error => {
                    this.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Error';
                    this.classList.remove('btn-outline-info');
                    this.classList.add('btn-danger');
                    this.disabled = false;

                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-outline-info');
                    }, 3000);
                });
        });
    });

    // Initialize execution badges on page load
    updateAllExecBadges();
    </script>
</body>
</html>
