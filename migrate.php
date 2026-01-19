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

// Define migration steps
$steps = [
    1 => [
        'title' => 'Copy PHP Classes',
        'description' => 'Copies 8 PHP class files to handle graphs, filters, and dashboards.',
        'files' => [
            'system/classes/Graph.php',
            'system/classes/Filter.php',
            'system/classes/FilterManager.php',
            'system/classes/FilterSet.php',
            'system/classes/DashboardInstance.php',
            'system/classes/DashboardTemplate.php',
            'system/classes/DashboardTemplateCategory.php',
            'system/classes/DashboardBuilder.php',
        ],
        'type' => 'copy'
    ],
    2 => [
        'title' => 'Copy Include Files',
        'description' => 'Copies 3 include folders that define routes and page handlers.',
        'folders' => [
            'system/includes/graph',
            'system/includes/filter',
            'system/includes/dashboard',
        ],
        'type' => 'copy_folders'
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
        'title' => 'Copy Filter Templates',
        'description' => 'Copies template files for the Filter module (list, form pages).',
        'files' => [
            'system/templates/filter/filter-list.php',
            'system/templates/filter/filter-form.php',
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
            'system/scripts/filter/filter-list.js',
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
        'description' => 'Copies compiled CSS/JS bundles and renames them to remove hashes (e.g., common.abc123.css → common.css).',
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
        'description' => 'Manual changes needed in existing files (Utility.php and system.inc.php).',
        'type' => 'code_modifications'
    ],
];

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
        } elseif ($stepData['type'] === 'copy_dist_renamed') {
            $result = copyDistRenamed($sourceDir . '/' . $stepData['folder'], $targetDir . '/' . $stepData['folder']);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
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

function copyDistRenamed($src, $dst) {
    $results = ['copied' => [], 'skipped' => []];

    if (!is_dir($src)) {
        return ['error' => 'Source folder not found: ' . $src];
    }

    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
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
        // Pattern: name.hash.ext -> name.ext
        if (preg_match('/^(.+)\.([a-f0-9]{8})\.([a-z]+)$/', $file, $matches)) {
            $newName = $matches[1] . '.' . $matches[3];
            $dstPath = $dst . '/' . $newName;
            copy($srcPath, $dstPath);
            $results['copied'][$file] = $newName;
        } else {
            // Copy as-is if no hash pattern
            copy($srcPath, $dst . '/' . $file);
            $results['copied'][$file] = $file;
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
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
        .module-filter { background: #198754; color: white; }
        .module-dashboard { background: #dc3545; color: white; }
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            border-radius: 8px;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.875rem;
            overflow-x: auto;
            white-space: pre;
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
        <div class="card step-card">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="step-number"><?php echo $num; ?></div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1"><?php echo htmlspecialchars($stepData['title']); ?></h5>
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
                            <?php foreach ($stepData['files'] as $file): ?>
                            <div class="file-item-simple">
                                <span class="status-badge status-<?php echo $fileStatuses[$file]; ?>">
                                    <?php echo $fileStatuses[$file]; ?>
                                </span>
                                <?php echo htmlspecialchars($file); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php $confirmMsg = 'Copy ' . count($stepData['files']) . ' files to target directory?';
                              if ($existingCount > 0) $confirmMsg .= '\n\nWARNING: ' . $existingCount . ' file(s) already exist and will be OVERWRITTEN!'; ?>
                        <a href="?action=execute&step=<?php echo $num; ?>"
                           class="btn btn-primary btn-sm"
                           onclick="return confirm('<?php echo $confirmMsg; ?>')">
                            <i class="fas fa-copy me-1"></i> Copy Files
                        </a>
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
                        <?php $confirmMsg = 'Copy entire ' . $stepData['folder'] . '/ folder to target directory?';
                              if ($status === 'exists') $confirmMsg .= '\n\nWARNING: Folder already exists - files will be OVERWRITTEN!'; ?>
                        <a href="?action=execute&step=<?php echo $num; ?>"
                           class="btn btn-primary btn-sm"
                           onclick="return confirm('<?php echo $confirmMsg; ?>')">
                            <i class="fas fa-folder me-1"></i> Copy Folder
                        </a>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'copy_dist_renamed'): ?>
                        <?php $status = $targetExists ? getFolderStatus($sourceDir, $targetDir, $stepData['folder']) : 'unknown'; ?>
                        <div class="file-list mb-3">
                            <?php
                            $folderPath = $sourceDir . '/' . $stepData['folder'];
                            if (is_dir($folderPath)) {
                                $files = scandir($folderPath);
                                // Separate CSS and JS files
                                $cssFiles = [];
                                $jsFiles = [];
                                foreach ($files as $f) {
                                    if ($f === '.' || $f === '..' || strpos($f, '.map') !== false || $f === 'manifest.json') continue;
                                    if (preg_match('/^(.+)\.([a-f0-9]{8})\.([a-z]+)$/', $f, $matches)) {
                                        $newName = $matches[1] . '.' . $matches[3];
                                        if ($matches[3] === 'css') {
                                            $cssFiles[] = ['source' => $f, 'target' => $newName];
                                        } else if ($matches[3] === 'js') {
                                            $jsFiles[] = ['source' => $f, 'target' => $newName];
                                        }
                                    }
                                }
                                // Display CSS files
                                foreach ($cssFiles as $file) {
                                    $moduleName = explode('.', $file['target'])[0];
                                    $moduleClass = 'module-' . $moduleName;
                                    echo '<div class="file-item">';
                                    echo '<span class="type-badge type-css">CSS</span>';
                                    echo '<span class="module-badge ' . $moduleClass . '">' . strtoupper($moduleName) . '</span>';
                                    echo '<span class="text-muted">' . htmlspecialchars($file['source']) . '</span>';
                                    echo '<span>&rarr;</span>';
                                    echo '<strong>' . htmlspecialchars($file['target']) . '</strong>';
                                    echo '</div>';
                                }
                                // Display JS files
                                foreach ($jsFiles as $file) {
                                    $moduleName = explode('.', $file['target'])[0];
                                    $moduleClass = 'module-' . $moduleName;
                                    echo '<div class="file-item">';
                                    echo '<span class="type-badge type-js">JS</span>';
                                    echo '<span class="module-badge ' . $moduleClass . '">' . strtoupper($moduleName) . '</span>';
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
                            Files will be renamed to remove content hashes for simpler loading.
                        </p>
                        <?php $confirmMsg = 'Copy and rename dist files?';
                              if ($status === 'exists') $confirmMsg .= '\n\nWARNING: Folder already exists - files will be OVERWRITTEN!'; ?>
                        <a href="?action=execute&step=<?php echo $num; ?>"
                           class="btn btn-primary btn-sm"
                           onclick="return confirm('<?php echo $confirmMsg; ?>')">
                            <i class="fas fa-folder me-1"></i> Copy &amp; Rename
                        </a>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'copy_folders'): ?>
                        <?php $folderExistCount = 0; ?>
                        <div class="file-list mb-3">
                            <?php foreach ($stepData['folders'] as $folder): ?>
                            <?php $status = $targetExists ? getFolderStatus($sourceDir, $targetDir, $folder) : 'unknown';
                                  if ($status === 'exists') $folderExistCount++; ?>
                            <div class="file-item-simple">
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <?php echo $status; ?>
                                </span>
                                <?php echo htmlspecialchars($folder); ?>/
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php $confirmMsg = 'Copy folders to target directory?';
                              if ($folderExistCount > 0) $confirmMsg .= '\n\nWARNING: ' . $folderExistCount . ' folder(s) already exist and will be OVERWRITTEN!'; ?>
                        <a href="?action=execute&step=<?php echo $num; ?>"
                           class="btn btn-primary btn-sm"
                           onclick="return confirm('<?php echo $confirmMsg; ?>')">
                            <i class="fas fa-folder me-1"></i> Copy Folders
                        </a>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'copy_libraries'): ?>
                        <?php $libExistCount = 0; ?>
                        <div class="file-list mb-3">
                            <?php foreach ($stepData['folders'] as $folder): ?>
                            <?php $status = $targetExists ? getFolderStatus($sourceDir, $targetDir, $folder) : 'unknown';
                                  if ($status === 'exists') $libExistCount++; ?>
                            <div class="file-item-simple">
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <?php echo $status; ?>
                                </span>
                                <?php echo htmlspecialchars($folder); ?>/
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php $confirmMsg = 'Copy library folders to target directory?';
                              if ($libExistCount > 0) $confirmMsg .= '\n\nWARNING: ' . $libExistCount . ' folder(s) already exist and will be OVERWRITTEN!'; ?>
                        <a href="?action=execute&step=<?php echo $num; ?>"
                           class="btn btn-primary btn-sm"
                           onclick="return confirm('<?php echo $confirmMsg; ?>')">
                            <i class="fas fa-folder me-1"></i> Copy Libraries
                        </a>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'copy_libraries_versioned'): ?>
                        <?php $libExistCount = 0; ?>
                        <div class="file-list mb-3">
                            <?php foreach ($stepData['libraries'] as $lib): ?>
                            <?php $status = $targetExists ? getVersionedLibraryStatus($sourceDir, $targetDir, $lib['source'], $lib['target']) : 'unknown';
                                  if ($status === 'exists') $libExistCount++; ?>
                            <div class="file-item-simple">
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <?php echo $status; ?>
                                </span>
                                <?php echo htmlspecialchars(basename($lib['source'])); ?>/ &rarr; <strong><?php echo htmlspecialchars(basename($lib['target'])); ?>/</strong>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="small text-muted mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Libraries will be copied to versioned folders (e.g., bootstrap5/, jquery3/) to avoid conflicts with existing libraries.
                        </p>
                        <?php $confirmMsg = 'Copy library folders to versioned directories?';
                              if ($libExistCount > 0) $confirmMsg .= '\n\nWARNING: ' . $libExistCount . ' folder(s) already exist and will be OVERWRITTEN!'; ?>
                        <a href="?action=execute&step=<?php echo $num; ?>"
                           class="btn btn-primary btn-sm"
                           onclick="return confirm('<?php echo $confirmMsg; ?>')">
                            <i class="fas fa-folder me-1"></i> Copy Libraries
                        </a>
                        <?php endif; ?>

                        <?php if ($stepData['type'] === 'sql_preview'): ?>
                        <div class="mb-3">
                            <p class="small text-muted">
                                Run the SQL file <code>sql/install.sql</code> in your database.
                                This creates the following tables:
                            </p>
                            <ul class="small">
                                <li><strong>graph</strong> - Graph definitions</li>
                                <li><strong>filter</strong> - Filter definitions</li>
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
                            <h6 class="text-primary"><i class="fas fa-file-code me-1"></i> 1. Add to Utility.php</h6>
                            <p class="small text-muted mb-2">
                                Add these 5 methods to <code>system/classes/Utility.php</code>:
                            </p>
                            <ul class="small mb-3">
                                <li><code>renderEmptyState()</code> - Empty state UI component</li>
                                <li><code>renderDashboardCellEmpty()</code> - Dashboard cell empty state</li>
                                <li><code>generateUUID()</code> - UUID v4 generation</li>
                                <li><code>generateShortId()</code> - Short unique ID generation</li>
                                <li><code>renderPageHeader()</code> - Page header with theme toggle</li>
                            </ul>

                            <h6 class="text-primary"><i class="fas fa-route me-1"></i> 2. Add Routes to system.inc.php</h6>
                            <p class="small text-muted mb-2">
                                Add these cases in the <code>switch ($url[0])</code> section of <code>system/includes/system.inc.php</code>:
                            </p>
                            <div class="code-block mb-3">case "graph":
    include_once 'graph/graph.inc.php';
    break;

case "filter":
    include_once 'filter/filter.inc.php';
    break;

case "dashboard":
    include_once 'dashboard/dashboard.inc.php';
    break;</div>

                            <h6 class="text-primary mt-3"><i class="fas fa-link me-1"></i> 3. Update Asset Loading in Include Files</h6>
                            <p class="small text-muted mb-2">
                                Replace <code>Utility::addModuleCss()</code> and <code>Utility::addModuleJs()</code> calls in the copied include files with rapidkart style:
                            </p>
                            <div class="code-block mb-3">// Replace this:
Utility::addModuleCss('common');
Utility::addModuleJs('common');

// With this:
$theme->addCss(SystemConfig::baseUrl() . 'dist/common.css');
$theme->addScript(SystemConfig::baseUrl() . 'dist/common.js');</div>
                            <p class="small text-muted mb-2">
                                Apply to all include files: <code>graph.inc.php</code>, <code>filter.inc.php</code>, <code>dashboard.inc.php</code>
                            </p>

                            <a href="docs/migration.md" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-book me-1"></i> View Full Documentation
                            </a>
                        </div>
                        <?php endif; ?>
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
                    <li><code>?urlq=filter/list</code> - Filter listing</li>
                    <li><code>?urlq=dashboard/list</code> - Dashboard listing</li>
                    <li><code>?urlq=dashboard/templates</code> - Template listing</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
