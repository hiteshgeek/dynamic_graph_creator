<?php

/**
 * Migration Controller
 * Handles migration tool actions for copying DGC files to live rapidkart project
 */

// Require admin access
DGCHelper::requireAdminAccess();

// Configuration
$sourceDir = SystemConfig::basePath();
$targetDir = '/var/www/html/rapidkartprocessadminv2';

// Check if target exists
$targetExists = is_dir($targetDir);

// =========================================================================
// DATABASE VALIDATION - Check local and live database tables against install.sql
// =========================================================================

/**
 * Get DGC-specific table constants from SystemTables class
 * Returns array of constant_name => table_name
 */
function getDgcTableConstants()
{
    return [
        'DB_TBL_GRAPH' => 'graph',
        'DB_TBL_COUNTER' => 'counter',
        'DB_TBL_TABLE' => 'dgc_table',
        'DB_TBL_DATA_FILTER' => 'data_filter',
        'DB_TBL_DASHBOARD_TEMPLATE_CATEGORY' => 'dashboard_template_category',
        'DB_TBL_DASHBOARD_TEMPLATE' => 'dashboard_template',
        'DB_TBL_DASHBOARD_INSTANCE' => 'dashboard_instance',
        'DB_TBL_SYSTEM_PLACEHOLDER' => 'system_placeholder',
        'DB_TBL_WIDGET_CATEGORY' => 'widget_category',
        'DB_TBL_GRAPH_WIDGET_CATEGORY_MAPPING' => 'graph_widget_category_mapping',
        'DB_TBL_COUNTER_WIDGET_CATEGORY_MAPPING' => 'counter_widget_category_mapping',
        'DB_TBL_TABLE_WIDGET_CATEGORY_MAPPING' => 'table_widget_category_mapping',
        'DB_TBL_WIDGET_TYPE' => 'widget_type',
        'DB_TBL_FILTER_WIDGET_TYPE_MANDATORY' => 'filter_widget_type_mandatory',
    ];
}

/**
 * Parse install.sql to extract CREATE TABLE names
 */
function getTablesFromInstallSql($sqlFile)
{
    $tables = [];
    if (!file_exists($sqlFile)) {
        return ['error' => 'install.sql not found'];
    }

    $content = file_get_contents($sqlFile);
    // Match CREATE TABLE statements (both IF NOT EXISTS and regular)
    preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`]?(\w+)[`]?/i', $content, $matches);

    if (!empty($matches[1])) {
        $tables = array_unique($matches[1]);
    }

    return $tables;
}

/**
 * Parse install.sql to extract table definitions with column details
 * Returns array of table_name => [columns => [...], indexes => [...]]
 */
function getTableDefinitionsFromInstallSql($sqlFile)
{
    $definitions = [];
    if (!file_exists($sqlFile)) {
        return ['error' => 'install.sql not found'];
    }

    $content = file_get_contents($sqlFile);

    // Match each CREATE TABLE block
    preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`]?(\w+)[`]?\s*\((.*?)\)\s*(?:ENGINE|;)/is', $content, $tableMatches, PREG_SET_ORDER);

    foreach ($tableMatches as $tableMatch) {
        $tableName = $tableMatch[1];
        $tableBody = $tableMatch[2];

        $columns = [];
        $indexes = [];

        // Split by lines and process each
        $lines = preg_split('/,\s*\n/', $tableBody);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Skip index definitions
            if (preg_match('/^(PRIMARY\s+KEY|INDEX|KEY|UNIQUE|FOREIGN\s+KEY|CONSTRAINT)/i', $line)) {
                $indexes[] = $line;
                continue;
            }

            // Parse column definition
            // Format: column_name TYPE(size) [UNSIGNED] [NOT NULL] [DEFAULT value] [AUTO_INCREMENT] [COMMENT 'text']
            if (preg_match('/^[`]?(\w+)[`]?\s+(.+)$/i', $line, $colMatch)) {
                $colName = $colMatch[1];
                $colDef = trim($colMatch[2]);

                // Extract type (with size if present)
                $type = '';
                $nullable = true;
                $default = null;
                $autoIncrement = false;
                $comment = '';

                // Get type (first word, possibly with parentheses)
                if (preg_match('/^(\w+(?:\([^)]+\))?)/i', $colDef, $typeMatch)) {
                    $type = strtoupper($typeMatch[1]);
                }

                // Check for UNSIGNED
                if (preg_match('/\bUNSIGNED\b/i', $colDef)) {
                    $type .= ' UNSIGNED';
                }

                // Check for NOT NULL
                if (preg_match('/\bNOT\s+NULL\b/i', $colDef)) {
                    $nullable = false;
                }

                // Check for AUTO_INCREMENT
                if (preg_match('/\bAUTO_INCREMENT\b/i', $colDef)) {
                    $autoIncrement = true;
                }

                // Check for DEFAULT
                if (preg_match("/\bDEFAULT\s+(?:'([^']*)'|\"([^\"]*)\"|(\S+))/i", $colDef, $defMatch)) {
                    $default = isset($defMatch[3]) ? $defMatch[3] : (isset($defMatch[1]) ? $defMatch[1] : $defMatch[2]);
                    // Handle special defaults
                    if (strtoupper($default) === 'NULL') {
                        $default = null;
                    } elseif (strtoupper($default) === 'CURRENT_TIMESTAMP') {
                        $default = 'CURRENT_TIMESTAMP';
                    }
                }

                // Check for COMMENT
                if (preg_match("/\bCOMMENT\s+'([^']*)'/i", $colDef, $commentMatch)) {
                    $comment = $commentMatch[1];
                }

                $columns[$colName] = [
                    'name' => $colName,
                    'type' => $type,
                    'nullable' => $nullable,
                    'default' => $default,
                    'auto_increment' => $autoIncrement,
                    'comment' => $comment,
                    'raw_definition' => $colDef
                ];
            }
        }

        $definitions[$tableName] = [
            'columns' => $columns,
            'indexes' => $indexes
        ];
    }

    return $definitions;
}

/**
 * Get column definitions from database table
 * @return array Column definitions or error
 */
function getTableColumnsFromDatabase($pdo, $tableName)
{
    $columns = [];

    try {
        $stmt = $pdo->query("SHOW FULL COLUMNS FROM `{$tableName}`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $type = strtoupper($row['Type']);

            // Normalize type for comparison
            $normalizedType = normalizeColumnType($type);

            $columns[$row['Field']] = [
                'name' => $row['Field'],
                'type' => $normalizedType,
                'type_raw' => $type,
                'nullable' => ($row['Null'] === 'YES'),
                'default' => $row['Default'],
                'auto_increment' => (strpos($row['Extra'], 'auto_increment') !== false),
                'comment' => isset($row['Comment']) ? $row['Comment'] : ''
            ];
        }
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }

    return $columns;
}

/**
 * Normalize column type for comparison
 * E.g., INT(11) and INT are essentially the same in modern MySQL
 */
function normalizeColumnType($type)
{
    $type = strtoupper(trim($type));

    // Normalize INT types (remove display width as it's deprecated in MySQL 8)
    $type = preg_replace('/\bINT\(\d+\)/', 'INT', $type);
    $type = preg_replace('/\bTINYINT\(\d+\)/', 'TINYINT', $type);
    $type = preg_replace('/\bSMALLINT\(\d+\)/', 'SMALLINT', $type);
    $type = preg_replace('/\bMEDIUMINT\(\d+\)/', 'MEDIUMINT', $type);
    $type = preg_replace('/\bBIGINT\(\d+\)/', 'BIGINT', $type);

    // Normalize ENUM/SET types - remove spaces after commas for consistent comparison
    // ENUM('A', 'B', 'C') and ENUM('A','B','C') should be treated as equal
    if (preg_match('/^(ENUM|SET)\s*\((.+)\)$/i', $type, $matches)) {
        $enumType = strtoupper($matches[1]);
        $values = $matches[2];
        // Remove spaces around commas and quotes
        $values = preg_replace('/\'\s*,\s*\'/', "','", $values);
        $type = $enumType . '(' . $values . ')';
    }

    return $type;
}

/**
 * Compare expected columns (from install.sql) with actual columns (from database)
 * @return array Comparison results with matches and mismatches
 */
function compareTableColumns($expectedColumns, $actualColumns)
{
    $result = [
        'match' => true,
        'missing' => [],      // Columns in install.sql but not in DB
        'extra' => [],        // Columns in DB but not in install.sql
        'type_mismatch' => [], // Columns with different types
        'nullable_mismatch' => [], // Columns with different nullable settings
        'matched' => []       // Columns that match
    ];

    // Check for missing and mismatched columns
    foreach ($expectedColumns as $colName => $expected) {
        if (!isset($actualColumns[$colName])) {
            $result['missing'][] = [
                'name' => $colName,
                'expected' => $expected
            ];
            $result['match'] = false;
        } else {
            $actual = $actualColumns[$colName];
            $hasIssue = false;

            // Compare types (normalized)
            $expectedType = normalizeColumnType($expected['type']);
            $actualType = normalizeColumnType($actual['type']);

            if ($expectedType !== $actualType) {
                $result['type_mismatch'][] = [
                    'name' => $colName,
                    'expected_type' => $expected['type'],
                    'actual_type' => $actual['type_raw']
                ];
                $result['match'] = false;
                $hasIssue = true;
            }

            // Compare nullable (only if explicitly NOT NULL in install.sql)
            if (!$expected['nullable'] && $actual['nullable']) {
                $result['nullable_mismatch'][] = [
                    'name' => $colName,
                    'expected' => 'NOT NULL',
                    'actual' => 'NULL'
                ];
                $result['match'] = false;
                $hasIssue = true;
            }

            if (!$hasIssue) {
                $result['matched'][] = $colName;
            }
        }
    }

    // Check for extra columns in database
    foreach ($actualColumns as $colName => $actual) {
        if (!isset($expectedColumns[$colName])) {
            $result['extra'][] = [
                'name' => $colName,
                'actual' => $actual
            ];
            // Extra columns don't necessarily mean a mismatch (could be custom additions)
        }
    }

    return $result;
}

/**
 * Get detailed column comparison for a table in a database
 */
function getTableColumnComparison($tableName, $expectedColumns, $host, $user, $pass, $dbName)
{
    try {
        $pdo = new PDO(
            'mysql:host=' . $host . ';dbname=' . $dbName,
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // First check if table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tableName]);
        if ($stmt->rowCount() === 0) {
            return ['error' => 'Table does not exist'];
        }

        $actualColumns = getTableColumnsFromDatabase($pdo, $tableName);
        if (isset($actualColumns['error'])) {
            return $actualColumns;
        }

        return compareTableColumns($expectedColumns, $actualColumns);

    } catch (PDOException $e) {
        return ['error' => 'Connection failed: ' . $e->getMessage()];
    }
}

/**
 * Get live database credentials from .developer_db.env
 */
function getLiveDbCredentials()
{
    $envFile = SystemConfig::basePath() . '/export_tables/.developer_db.env';
    if (!file_exists($envFile)) {
        return ['error' => '.developer_db.env not found'];
    }

    $credentials = [];
    $content = file_get_contents($envFile);
    $lines = explode("\n", $content);

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;

        if (preg_match('/^([A-Z_]+)=["\']?([^"\']*)["\']?$/', $line, $matches)) {
            $credentials[$matches[1]] = $matches[2];
        }
    }

    if (empty($credentials['DB_HOST']) || empty($credentials['DB_USER'])) {
        return ['error' => 'Invalid .developer_db.env format'];
    }

    return $credentials;
}

/**
 * Check which tables exist in a database and get row counts
 * @param array $tables List of table names to check
 * @param string $host Database host
 * @param string $user Database user
 * @param string $pass Database password
 * @param string $dbName Database name
 * @return array Results with table => ['exists' => bool, 'count' => int|null] or ['error' => message]
 */
function checkDatabaseTablesWithCredentials($tables, $host, $user, $pass, $dbName)
{
    $results = [];

    try {
        $pdo = new PDO(
            'mysql:host=' . $host . ';dbname=' . $dbName,
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            $exists = $stmt->rowCount() > 0;

            $count = null;
            if ($exists) {
                try {
                    $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
                    $count = (int) $countStmt->fetchColumn();
                } catch (PDOException $e) {
                    // Table might exist but can't count (permissions, etc.)
                    $count = null;
                }
            }

            $results[$table] = [
                'exists' => $exists,
                'count' => $count
            ];
        }
    } catch (PDOException $e) {
        return ['error' => 'Database connection failed: ' . $e->getMessage()];
    }

    return $results;
}

/**
 * Perform full database validation for both local and live databases
 * Returns combined table data for easy comparison, including column validation
 */
function validateDatabaseSetup($sourceDir)
{
    $result = [
        'tables' => [],           // Combined table data for both DBs
        'local_info' => null,     // Local DB connection info
        'live_info' => null,      // Live DB connection info
        'local_error' => null,
        'live_error' => null,
        'all_passed' => true,
        'column_issues' => false  // Flag if any column issues exist
    ];

    // 1. Get tables from install.sql
    $installSqlTables = getTablesFromInstallSql($sourceDir . '/sql/install.sql');
    if (isset($installSqlTables['error'])) {
        $result['local_error'] = $installSqlTables['error'];
        $result['live_error'] = $installSqlTables['error'];
        $result['all_passed'] = false;
        return $result;
    }

    // 1b. Get table definitions with column details from install.sql
    $tableDefinitions = getTableDefinitionsFromInstallSql($sourceDir . '/sql/install.sql');
    if (isset($tableDefinitions['error'])) {
        $result['local_error'] = $tableDefinitions['error'];
        $result['live_error'] = $tableDefinitions['error'];
        $result['all_passed'] = false;
        return $result;
    }

    // 2. Get DGC table constants from SystemTables
    $dgcConstants = getDgcTableConstants();

    // 3. Check LOCAL database
    $result['local_info'] = [
        'host' => LocalProjectConfig::getDbHost(),
        'db_name' => LocalProjectConfig::getDbName()
    ];

    $localDbCheck = checkDatabaseTablesWithCredentials(
        $installSqlTables,
        LocalProjectConfig::getDbHost(),
        LocalProjectConfig::getDbUser(),
        LocalProjectConfig::getDbPass(),
        LocalProjectConfig::getDbName()
    );

    if (isset($localDbCheck['error'])) {
        $result['local_error'] = $localDbCheck['error'];
        $result['all_passed'] = false;
        $localDbCheck = [];
    }

    // 4. Check LIVE database
    $liveCredentials = getLiveDbCredentials();
    $liveDbCheck = [];
    $liveDbName = null;

    if (isset($liveCredentials['error'])) {
        $result['live_error'] = $liveCredentials['error'];
        $result['live_info'] = ['host' => 'N/A', 'db_name' => 'N/A'];
        $result['all_passed'] = false;
    } else {
        // Use DB_NAME from .env if provided, otherwise fall back to local DB name
        $liveDbName = !empty($liveCredentials['DB_NAME']) ? $liveCredentials['DB_NAME'] : LocalProjectConfig::getDbName();

        $result['live_info'] = [
            'host' => $liveCredentials['DB_HOST'],
            'db_name' => $liveDbName
        ];

        $liveDbCheck = checkDatabaseTablesWithCredentials(
            $installSqlTables,
            $liveCredentials['DB_HOST'],
            $liveCredentials['DB_USER'],
            $liveCredentials['DB_PASS'],
            $liveDbName
        );

        if (isset($liveDbCheck['error'])) {
            $result['live_error'] = $liveDbCheck['error'];
            $result['all_passed'] = false;
            $liveDbCheck = [];
        }
    }

    // 5. Build combined table data with column validation
    foreach ($installSqlTables as $table) {
        $constantName = array_search($table, $dgcConstants);

        // Local DB status
        $localExists = isset($localDbCheck[$table]) ? $localDbCheck[$table]['exists'] : null;
        $localCount = isset($localDbCheck[$table]) ? $localDbCheck[$table]['count'] : null;

        // Live DB status
        $liveExists = isset($liveDbCheck[$table]) ? $liveDbCheck[$table]['exists'] : null;
        $liveCount = isset($liveDbCheck[$table]) ? $liveDbCheck[$table]['count'] : null;

        // Data match check (only if both tables exist and have counts)
        $dataMatch = null; // null = N/A, true = match, false = mismatch
        if ($localCount !== null && $liveCount !== null) {
            $dataMatch = ($localCount === $liveCount);
        }

        // Column validation
        $localColumnComparison = null;
        $liveColumnComparison = null;
        $expectedColumns = isset($tableDefinitions[$table]) ? $tableDefinitions[$table]['columns'] : [];

        // Compare columns for LOCAL database
        if ($localExists && !empty($expectedColumns) && !isset($localDbCheck['error'])) {
            $localColumnComparison = getTableColumnComparison(
                $table,
                $expectedColumns,
                LocalProjectConfig::getDbHost(),
                LocalProjectConfig::getDbUser(),
                LocalProjectConfig::getDbPass(),
                LocalProjectConfig::getDbName()
            );

            if (!isset($localColumnComparison['error']) && !$localColumnComparison['match']) {
                $result['column_issues'] = true;
            }
        }

        // Compare columns for LIVE database
        if ($liveExists && !empty($expectedColumns) && $liveDbName && !isset($liveCredentials['error'])) {
            $liveColumnComparison = getTableColumnComparison(
                $table,
                $expectedColumns,
                $liveCredentials['DB_HOST'],
                $liveCredentials['DB_USER'],
                $liveCredentials['DB_PASS'],
                $liveDbName
            );

            if (!isset($liveColumnComparison['error']) && !$liveColumnComparison['match']) {
                $result['column_issues'] = true;
            }
        }

        $result['tables'][] = [
            'name' => $table,
            'in_install_sql' => true,
            'constant' => $constantName !== false ? $constantName : null,
            'local_exists' => $localExists,
            'local_count' => $localCount,
            'local_columns' => $localColumnComparison,
            'live_exists' => $liveExists,
            'live_count' => $liveCount,
            'live_columns' => $liveColumnComparison,
            'data_match' => $dataMatch,
            'expected_column_count' => count($expectedColumns)
        ];

        // Check for issues
        if ($localExists === false || $liveExists === false) {
            $result['all_passed'] = false;
        }
        if ($constantName === false) {
            $result['all_passed'] = false;
        }
    }

    return $result;
}

// Run validation
$dbValidation = validateDatabaseSetup($sourceDir);

// $url is already parsed in index.php
$action = isset($url[1]) ? $url[1] : 'list';

// Check for AJAX requests
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Handle AJAX actions via POST
if ($isAjax && isset($_POST['submit'])) {
    switch ($_POST['submit']) {
        case 'count_files':
            countFiles($_POST);
            break;
        case 'execute_step':
            executeStep($_POST);
            break;
        case 'copy_single_file':
            copySingleFile($_POST);
            break;
    }
    exit; // AJAX handlers call exit after response
}

// Handle GET actions
switch ($action) {
    case 'list':
    default:
        showMigrateList();
        break;
}

// =========================================================================
// HELPER FUNCTIONS
// =========================================================================

/**
 * Transform asset loading calls from DGC style to Rapidkart style
 * LocalUtility::addModuleCss/Js() -> $theme->addCss/Script()
 */
function transformAssetLoading($content)
{
    // Transform LocalUtility::addModuleCss('module') -> $theme->addCss(SystemConfig::stylesUrl() . 'module/module.css')
    $content = preg_replace_callback(
        "/LocalUtility::addModuleCss\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/",
        function ($matches) {
            $module = $matches[1];
            return "\$theme->addCss(SystemConfig::stylesUrl() . '{$module}/{$module}.css')";
        },
        $content
    );

    // Transform LocalUtility::addModuleJs('module') -> $theme->addScript(SystemConfig::scriptsUrl() . 'module/module.js')
    $content = preg_replace_callback(
        "/LocalUtility::addModuleJs\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/",
        function ($matches) {
            $module = $matches[1];
            return "\$theme->addScript(SystemConfig::scriptsUrl() . '{$module}/{$module}.js')";
        },
        $content
    );

    // Transform LocalUtility::addPageScript('module', 'script-name') or LocalUtility::addPageScript('module', 'script-name', weight)
    // -> $theme->addScript(SystemConfig::scriptsUrl() . 'module/script-name.js') or $theme->addScript(SystemConfig::scriptsUrl() . 'module/script-name.js', weight)
    $content = preg_replace_callback(
        "/LocalUtility::addPageScript\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*(?:,\s*(\d+)\s*)?\)/",
        function ($matches) {
            $module = $matches[1];
            $scriptName = $matches[2];
            $weight = isset($matches[3]) && $matches[3] !== '' ? $matches[3] : null;

            if ($weight !== null) {
                return "\$theme->addScript(SystemConfig::scriptsUrl() . '{$module}/{$scriptName}.js', {$weight})";
            }
            return "\$theme->addScript(SystemConfig::scriptsUrl() . '{$module}/{$scriptName}.js')";
        },
        $content
    );

    return $content;
}

function copyFiles($sourceDir, $targetDir, $files)
{
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

function copyFolder($src, $dst)
{
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

function getFileStatus($sourceDir, $targetDir, $file, $applyTransformation = false)
{
    $src = $sourceDir . '/' . $file;
    $dst = $targetDir . '/' . $file;

    $srcExists = file_exists($src);
    $dstExists = file_exists($dst);

    if (!$srcExists) return 'missing';
    if (!$dstExists) return 'new';

    // Compare file contents
    // For transformed files, apply transformation before comparison
    if ($applyTransformation) {
        $srcContent = file_get_contents($src);
        $srcContent = transformAssetLoading($srcContent);
        $dstContent = file_get_contents($dst);
        if (md5($srcContent) === md5($dstContent)) {
            return 'same';
        }
    } else {
        if (md5_file($src) === md5_file($dst)) {
            return 'same';
        }
    }
    return 'different';
}

function getFolderStatus($sourceDir, $targetDir, $folder)
{
    $src = $sourceDir . '/' . $folder;
    $dst = $targetDir . '/' . $folder;

    if (!is_dir($src)) return 'missing';
    if (!is_dir($dst)) return 'new';
    return 'exists';
}

function getVersionedLibraryStatus($sourceDir, $targetDir, $sourcePath, $targetPath)
{
    $src = $sourceDir . '/' . $sourcePath;
    $dst = $targetDir . '/' . $targetPath;

    if (!is_dir($src)) return 'missing';
    if (!is_dir($dst)) return 'new';
    return 'exists';
}

function copyDistRenamed($src, $targetDir)
{
    $results = ['copied' => [], 'skipped' => []];

    if (!is_dir($src)) {
        return ['error' => 'Source folder not found: ' . $src];
    }

    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..' || $file === 'per_page') continue;

        $srcPath = $src . '/' . $file;

        // Skip .map files and manifest.json
        if (strpos($file, '.map') !== false || $file === 'manifest.json') {
            $results['skipped'][] = $file;
            continue;
        }

        // Remove hash from filename: common.abc123.css -> common.css
        if (preg_match('/^(.+)\.([a-f0-9]{8})\.([a-z]+)$/', $file, $matches)) {
            $moduleName = $matches[1];
            $ext = $matches[3];
            $newName = $moduleName . '.' . $ext;

            // Route to module-specific folder based on file type
            if ($ext === 'css') {
                $moduleDir = $targetDir . '/system/styles/' . $moduleName;
            } else if ($ext === 'js') {
                $moduleDir = $targetDir . '/system/scripts/' . $moduleName;
            } else {
                $results['skipped'][] = $file;
                continue;
            }

            // Create module directory if needed
            if (!is_dir($moduleDir)) {
                mkdir($moduleDir, 0755, true);
            }

            $dstPath = $moduleDir . '/' . $newName;
            copy($srcPath, $dstPath);

            $relativePath = ($ext === 'css' ? 'system/styles/' : 'system/scripts/') . $moduleName . '/' . $newName;
            $results['copied'][$file] = $relativePath;
        } else {
            $results['skipped'][] = $file;
        }
    }
    closedir($dir);

    // Handle per_page directory (per-page scripts with cache-busting hashes)
    $perPageSrc = $src . '/per_page';
    if (is_dir($perPageSrc)) {
        $modules = scandir($perPageSrc);
        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') continue;

            $moduleSrc = $perPageSrc . '/' . $module;
            if (!is_dir($moduleSrc)) continue;

            $scripts = scandir($moduleSrc);
            foreach ($scripts as $file) {
                if ($file === '.' || $file === '..') continue;

                // Skip .map files
                if (strpos($file, '.map') !== false) {
                    $results['skipped'][] = "per_page/{$module}/{$file}";
                    continue;
                }

                // Remove hash: script-name.abc123.js -> script-name.js
                if (preg_match('/^(.+)\.([a-f0-9]{8})\.js$/', $file, $matches)) {
                    $scriptName = $matches[1];
                    $newName = $scriptName . '.js';

                    // Target: system/scripts/{module}/{script-name}.js
                    $moduleDir = $targetDir . '/system/scripts/' . $module;
                    if (!is_dir($moduleDir)) {
                        mkdir($moduleDir, 0755, true);
                    }

                    $srcPathFile = $moduleSrc . '/' . $file;
                    $dstPath = $moduleDir . '/' . $newName;
                    copy($srcPathFile, $dstPath);

                    $relativePath = "system/scripts/{$module}/{$newName}";
                    $results['copied']["per_page/{$module}/{$file}"] = $relativePath;
                } else {
                    $results['skipped'][] = "per_page/{$module}/{$file}";
                }
            }
        }
    }

    return $results;
}

// =========================================================================
// AJAX HANDLERS
// =========================================================================

/**
 * Count files in target directory (AJAX)
 */
function countFiles($data)
{
    global $targetDir;

    $path = isset($data['path']) ? $data['path'] : '';
    $recursive = isset($data['recursive']) ? $data['recursive'] === '1' : false;
    $countType = isset($data['count_type']) ? $data['count_type'] : 'files';

    $fullPath = $targetDir . '/' . $path;

    if (!is_dir($fullPath)) {
        DGCHelper::ajaxResponseTrue('Count completed', ['count' => 0, 'exists' => false, 'path' => $path, 'type' => $countType]);
    }

    $count = 0;
    if ($countType === 'folders') {
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

    DGCHelper::ajaxResponseTrue('Count completed', ['count' => $count, 'exists' => true, 'path' => $path, 'type' => $countType]);
}

/**
 * Execute migration step (AJAX)
 */
function executeStep($data)
{
    global $sourceDir, $targetDir;

    $step = isset($data['step']) ? intval($data['step']) : 0;
    $steps = getMigrationSteps();

    if ($step <= 0 || !isset($steps[$step])) {
        DGCHelper::ajaxResponseFalse('Invalid step');
    }

    $stepData = $steps[$step];
    $result = null;

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
        } elseif ($stepData['type'] === 'copy_scripts_and_includes') {
            $results = ['scripts' => [], 'includes' => []];

            if (!empty($stepData['files'])) {
                $results['scripts'] = copyFiles($sourceDir, $targetDir, $stepData['files']);
            }

            if (!empty($stepData['include_files'])) {
                foreach ($stepData['include_files'] as $file) {
                    $srcFile = $sourceDir . '/' . $file;
                    $dstFile = $targetDir . '/' . $file;
                    if (file_exists($srcFile)) {
                        $dstDir = dirname($dstFile);
                        if (!is_dir($dstDir)) {
                            mkdir($dstDir, 0755, true);
                        }
                        $content = file_get_contents($srcFile);
                        $content = transformAssetLoading($content);
                        file_put_contents($dstFile, $content);
                        $results['includes'][$file] = 'copied';
                    }
                }
            }
            $result = $results;
        } elseif ($stepData['type'] === 'copy_dist_renamed') {
            $result = copyDistRenamed($sourceDir . '/' . $stepData['folder'], $targetDir);
        }

        DGCHelper::ajaxResponseTrue('Step completed', [
            'step' => $step,
            'title' => $stepData['title'],
            'result' => $result,
            'timestamp' => time()
        ]);
    } catch (Exception $e) {
        DGCHelper::ajaxResponseFalse('Error: ' . $e->getMessage());
    }
}

/**
 * Copy a single file (AJAX)
 */
function copySingleFile($data)
{
    global $sourceDir, $targetDir;

    $file = isset($data['file']) ? trim($data['file']) : '';

    if (empty($file)) {
        DGCHelper::ajaxResponseFalse('File path is required');
    }

    try {
        $result = copyFiles($sourceDir, $targetDir, [$file]);

        DGCHelper::ajaxResponseTrue('File copied successfully', [
            'file' => $file,
            'result' => $result[$file],
            'timestamp' => time()
        ]);
    } catch (Exception $e) {
        DGCHelper::ajaxResponseFalse('Error: ' . $e->getMessage());
    }
}

// =========================================================================
// MIGRATION STEPS DEFINITION
// =========================================================================

function getMigrationSteps()
{
    return [
        1 => [
            'title' => 'Copy PHP Classes',
            'description' => 'Copies PHP class files to handle graphs, counters, tables, data filters, dashboards, templates, system placeholders, widget categories/types, and UI components.',
            'files' => [
                'system/classes/DGCHelper.php',
                'system/classes/Graph.php',
                'system/classes/GraphManager.php',
                'system/classes/WidgetCounter.php',
                'system/classes/WidgetCounterManager.php',
                'system/classes/WidgetCounterCategoryMapping.php',
                'system/classes/WidgetCounterCategoryMappingManager.php',
                'system/classes/WidgetTable.php',
                'system/classes/WidgetTableManager.php',
                'system/classes/WidgetTableCategoryMapping.php',
                'system/classes/WidgetTableCategoryMappingManager.php',
                'system/classes/DataFilter.php',
                'system/classes/DataFilterManager.php',
                'system/classes/DataFilterSet.php',
                'system/classes/QueryHelper.php',
                'system/classes/DashboardInstance.php',
                'system/classes/DashboardTemplate.php',
                'system/classes/DashboardTemplateCategory.php',
                'system/classes/DashboardBuilder.php',
                'system/classes/SystemPlaceholder.php',
                'system/classes/SystemPlaceholderManager.php',
                'system/classes/WidgetCategory.php',
                'system/classes/WidgetCategoryManager.php',
                'system/classes/GraphWidgetCategoryMapping.php',
                'system/classes/GraphWidgetCategoryMappingManager.php',
                'system/classes/WidgetType.php',
                'system/classes/WidgetTypeManager.php',
                'system/classes/FilterWidgetTypeMandatoryManager.php',
                'system/classes/element/Element.php',
                'system/classes/element/ElementManager.php',
            ],
            'type' => 'copy'
        ],
        2 => [
            'title' => 'Copy Graph Templates',
            'description' => 'Copies template files for the Graph module (views and forms).',
            'files' => [
                'system/templates/graph/views/graph-list.tpl.php',
                'system/templates/graph/views/graph-view.tpl.php',
                'system/templates/graph/forms/graph-creator.tpl.php',
            ],
            'type' => 'copy'
        ],
        3 => [
            'title' => 'Copy Counter Templates',
            'description' => 'Copies template files for the Counter module (views and forms).',
            'files' => [
                'system/templates/counter/views/counter-list.tpl.php',
                'system/templates/counter/views/counter-view.tpl.php',
                'system/templates/counter/forms/counter-creator.tpl.php',
            ],
            'type' => 'copy'
        ],
        4 => [
            'title' => 'Copy Table Templates',
            'description' => 'Copies template files for the Table widget module (views and forms).',
            'files' => [
                'system/templates/table/views/table-list.tpl.php',
                'system/templates/table/views/table-view.tpl.php',
                'system/templates/table/forms/table-creator.tpl.php',
            ],
            'type' => 'copy'
        ],
        5 => [
            'title' => 'Copy Data Filter Templates',
            'description' => 'Copies template files for the Data Filter module (views and forms).',
            'files' => [
                'system/templates/data-filter/views/data-filter-list.tpl.php',
                'system/templates/data-filter/forms/data-filter-form.tpl.php',
            ],
            'type' => 'copy'
        ],
        6 => [
            'title' => 'Copy Dashboard Templates',
            'description' => 'Copies template files for the Dashboard module (views and forms).',
            'files' => [
                'system/templates/dashboard/views/dashboard-list.tpl.php',
                'system/templates/dashboard/views/dashboard-preview.tpl.php',
                'system/templates/dashboard/views/template-list.tpl.php',
                'system/templates/dashboard/views/template-preview.tpl.php',
                'system/templates/dashboard/forms/dashboard-builder.tpl.php',
                'system/templates/dashboard/forms/template-editor.tpl.php',
                'system/templates/dashboard/forms/template-builder.tpl.php',
            ],
            'type' => 'copy'
        ],
        7 => [
            'title' => 'Copy Include Files',
            'description' => 'Copies include files with transforms. Page scripts now come from dist/per_page/. Include files are transformed: LocalUtility::addModule*() and addPageScript() -> $theme->addCss()/addScript().',
            'files' => [], // Page scripts now come from dist/per_page/ via Step 8
            'include_files' => [
                'system/includes/graph/graph.inc.php',
                'system/includes/counter/counter.inc.php',
                'system/includes/table/table.inc.php',
                'system/includes/data-filter/data-filter.inc.php',
                'system/includes/dashboard/dashboard.inc.php',
                'system/includes/dashboard/template-preview-component.php',
            ],
            'type' => 'copy_scripts_and_includes'
        ],
        8 => [
            'title' => 'Copy Compiled Assets (dist/)',
            'description' => 'Copies compiled CSS/JS bundles to module-specific folders and removes hashes (e.g., common.abc123.css -> system/styles/common/common.css).',
            'folder' => 'dist',
            'type' => 'copy_dist_renamed'
        ],
        9 => [
            'title' => 'Copy Theme Libraries',
            'description' => 'Copies JavaScript/CSS libraries to the target project.',
            'libraries' => [
                ['source' => 'themes/libraries/bootstrap5', 'target' => 'themes/libraries/bootstrap5'],
                ['source' => 'themes/libraries/jquery3', 'target' => 'themes/libraries/jquery3'],
                ['source' => 'themes/libraries/fontawesome6', 'target' => 'themes/libraries/fontawesome6'],
                ['source' => 'themes/libraries/moment-dgc', 'target' => 'themes/libraries/moment-dgc'],
                ['source' => 'themes/libraries/echarts-dgc', 'target' => 'themes/libraries/echarts-dgc'],
                ['source' => 'themes/libraries/codemirror-dgc', 'target' => 'themes/libraries/codemirror-dgc'],
                ['source' => 'themes/libraries/daterangepicker-dgc', 'target' => 'themes/libraries/daterangepicker-dgc'],
                ['source' => 'themes/libraries/autosize-dgc', 'target' => 'themes/libraries/autosize-dgc'],
                ['source' => 'themes/libraries/sortablejs-dgc', 'target' => 'themes/libraries/sortablejs-dgc'],
            ],
            'type' => 'copy_libraries_versioned'
        ],
        10 => [
            'title' => 'Run Database Setup',
            'description' => 'Shows the SQL that needs to be executed to create tables and insert system data.',
            'type' => 'sql_preview'
        ],
        11 => [
            'title' => 'Code Modifications Required',
            'description' => 'Manual changes needed: add routes to system.inc.php and update asset loading in include files.',
            'type' => 'code_modifications'
        ],
    ];
}

// =========================================================================
// VIEW FUNCTIONS
// =========================================================================

/**
 * Show migration list/main page
 * Note: This is a standalone page with its own HTML structure (doesn't use ThemeRegistry)
 */
function showMigrateList()
{
    global $sourceDir, $targetDir, $targetExists, $dbValidation;

    $steps = getMigrationSteps();

    // Include the template directly (standalone page with its own HTML)
    include SystemConfig::templatesPath() . 'migrate/views/migrate-list.tpl.php';

    // Exit to prevent ThemeRegistry from rendering
    exit;
}
