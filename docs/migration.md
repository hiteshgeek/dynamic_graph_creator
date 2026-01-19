# Dynamic Graph Creator - Migration Guide

This document lists all files to copy from this module to your live **rapidkartprocessadminv2** project.

---

## Quick Reference

| Category | Source | Destination |
|----------|--------|-------------|
| Classes | `system/classes/` | `system/classes/` |
| Includes | `system/includes/` | `system/includes/` |
| Templates | `system/templates/` | `system/templates/` |
| Page Scripts | `system/scripts/` | `system/scripts/` |
| Dist | `dist/` | `dist/` |
| Libraries | `themes/libraries/` | `themes/libraries/` |
| SQL | `sql/install.sql` | Run in database |

---

## 1. PHP Classes

Copy to: `system/classes/`

| File | Description |
|------|-------------|
| `Graph.php` | Graph model (CRUD operations) |
| `DataFilter.php` | Data filter model (CRUD operations) |
| `DataFilterManager.php` | Data filter management and rendering |
| `DataFilterSet.php` | Data filter set collection |
| `DashboardInstance.php` | Dashboard instance model |
| `DashboardTemplate.php` | Dashboard template model |
| `DashboardTemplateCategory.php` | Template category model |
| `DashboardBuilder.php` | Dashboard builder logic |

---

## 2. Include Files

Copy entire folders to: `system/includes/`

| Folder                                     | Description                              |
| ------------------------------------------ | ---------------------------------------- |
| `graph/graph.inc.php`                      | Graph module routes and handlers         |
| `data-filter/data-filter.inc.php`          | Data filter module routes and handlers   |
| `dashboard/dashboard.inc.php`              | Dashboard module routes and handlers     |
| `dashboard/template-preview-component.php` | Dashboard preview rendering helper       |

---

## 3. Templates

Copy entire folders to: `system/templates/`

### Graph Templates
| File | Description |
|------|-------------|
| `graph/graph-list.php` | Graph listing page |
| `graph/graph-creator.php` | Graph create/edit form |
| `graph/graph-view.php` | Graph preview with filters |

### Data Filter Templates

| File | Description |
|------|-------------|
| `data-filter/data-filter-list.php` | Data filter listing page |
| `data-filter/data-filter-form.php` | Data filter create/edit form |

### Dashboard Templates
| File | Description |
|------|-------------|
| `dashboard/dashboard-list.php` | Dashboard listing page |
| `dashboard/dashboard-builder.php` | Dashboard builder interface |
| `dashboard/dashboard-preview.php` | Dashboard preview page |
| `dashboard/template-list.php` | Template listing with categories |
| `dashboard/template-editor.php` | Template creation/edit form |
| `dashboard/template-builder.php` | Visual template builder |
| `dashboard/template-preview.php` | Template preview page |

---

## 4. Page-Specific Scripts

Copy these folders to: `system/scripts/`

| File | Description |
|------|-------------|
| `graph/graph-list.js` | Graph list delete handler |
| `graph/graph-creator.js` | Graph creator initialization |
| `data-filter/data-filter-list.js` | Data filter list delete handler |
| `dashboard/dashboard-list.js` | Dashboard list delete handler |
| `dashboard/dashboard-builder.js` | Builder page initialization |
| `dashboard/dashboard-preview.js` | Preview page functionality |
| `dashboard/template-list.js` | Template/category delete handlers |
| `dashboard/template-editor.js` | Template form validation |
| `dashboard/template-builder.js` | Template builder initialization |
| `dashboard/template-preview.js` | Template preview functionality |

---

## 5. Compiled Assets (dist/)

Assets are copied to **module-specific folders** to match Rapidkart's asset structure:

| Source File (dist/) | Target Location in Rapidkart | Description |
|---------------------|------------------------------|-------------|
| `common.abc123.css` | `system/styles/common/common.css` | Common styles (themes, variables, base) |
| `common.abc123.js` | `system/scripts/common/common.js` | Common JS (Theme.js, utilities) |
| `graph.abc123.css` | `system/styles/graph/graph.css` | Graph module CSS |
| `graph.abc123.js` | `system/scripts/graph/graph.js` | Graph module JS (GraphConfig, FilterUtils) |
| `data-filter.abc123.css` | `system/styles/data-filter/data-filter.css` | Data filter module CSS |
| `data-filter.abc123.js` | `system/scripts/data-filter/data-filter.js` | Data filter module JS (FilterManager) |
| `dashboard.abc123.css` | `system/styles/dashboard/dashboard.css` | Dashboard module CSS |
| `dashboard.abc123.js` | `system/scripts/dashboard/dashboard.js` | Dashboard JS (dashboard, TemplateManager, TemplateBuilder) |

**Note:** The migration tool automatically removes content hashes from filenames and routes files to the correct module folder. Source maps and manifest.json are skipped.

---

## 6. Theme Libraries

Copy to: `themes/libraries/`

Libraries are copied to **versioned folders** to avoid conflicts with existing libraries in rapidkart.

| Source | Target | Files | Notes |
|--------|--------|-------|-------|
| `bootstrap/` | `bootstrap5/` | `css/bootstrap.min.css`, `js/bootstrap.bundle.min.js` | Bootstrap 5.3.2 |
| `jquery/` | `jquery3/` | `jquery.min.js` | jQuery 3.7.1 |
| `fontawesome/` | `fontawesome6/` | `css/all.min.css`, `webfonts/*` | Font Awesome 6.5.1 |
| `moment/` | `moment2/` | `moment.min.js` | Moment.js 2.30.1 |
| `echarts/` | `echarts/` | `echarts.min.js` | ECharts 5.4.3 |
| `codemirror/` | `codemirror/` | `css/*.css`, `js/*.js` | CodeMirror 5.65.16 |
| `daterangepicker/` | `daterangepicker/` | `css/daterangepicker.css`, `js/daterangepicker.min.js` | Daterangepicker 3.1 |
| `autosize/` | `autosize/` | `autosize.min.js` | Autosize 6.0.1 |
| `sortablejs/` | `sortablejs/` | `Sortable.min.js` | SortableJS 1.15.0 |

**Note:** The include files and templates reference these versioned paths (e.g., `bootstrap5/`, `jquery3/`). This allows the module to use newer library versions without affecting existing rapidkart pages.

---

## 7. Database Setup

Run SQL file: `sql/install.sql`

Creates tables:

- `graph` - Graph definitions
- `data_filter` - Data filter definitions
- `dashboard_template_category` - Template categories (4 system categories)
- `dashboard_template` - Templates (16 system templates)
- `dashboard_instance` - User dashboard instances

---

## 8. Code Modifications

### Utility.php

Add these methods to existing `system/classes/Utility.php`:

**Note:** The functions `ajaxResponseTrue()` and `ajaxResponseFalse()` already exist in the live project.

#### 1. renderEmptyState()

```php
/**
 * Render an empty state component
 *
 * @param string $icon FontAwesome icon class (e.g., 'fa-chart-bar', 'fa-th-large')
 * @param string $title The main heading text
 * @param string $description The description text (supports HTML)
 * @param string|null $buttonText The button label (null or empty to hide button)
 * @param string|null $buttonUrl The button URL (use '#' or empty for button element, null to hide)
 * @param string $color Color theme: 'blue' (default), 'green', 'orange', 'purple'
 * @param string $buttonClass Optional additional CSS class for the button (for JS handlers)
 * @return string HTML markup for the empty state
 */
public static function renderEmptyState($icon, $title, $description, $buttonText = null, $buttonUrl = null, $color = 'blue', $buttonClass = '')
{
    $colorClass = ' empty-state-' . htmlspecialchars($color);
    $html = '<div class="empty-state' . $colorClass . '">';
    $html .= '<div class="empty-state-content">';
    $html .= '<div class="empty-state-icon">';
    $html .= '<i class="fas ' . htmlspecialchars($icon) . '"></i>';
    $html .= '</div>';
    $html .= '<h3>' . htmlspecialchars($title) . '</h3>';
    $html .= '<p>' . $description . '</p>';

    if (!empty($buttonText)) {
        if (empty($buttonUrl) || $buttonUrl === '#') {
            $btnClass = 'btn btn-primary btn-sm' . ($buttonClass ? ' ' . htmlspecialchars($buttonClass) : '');
            $html .= '<button type="button" class="' . $btnClass . '" autofocus>';
            $html .= '<i class="fas fa-plus"></i> ' . htmlspecialchars($buttonText);
            $html .= '</button>';
        } else {
            $btnClass = 'btn btn-primary btn-sm' . ($buttonClass ? ' ' . htmlspecialchars($buttonClass) : '');
            $html .= '<a href="' . htmlspecialchars($buttonUrl) . '" class="' . $btnClass . '" autofocus>';
            $html .= '<i class="fas fa-plus"></i> ' . htmlspecialchars($buttonText);
            $html .= '</a>';
        }
    }

    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
```

#### 2. renderDashboardCellEmpty()

```php
/**
 * Render a dashboard cell empty state
 * Used for empty areas/cells within dashboard sections (both edit and view mode)
 *
 * @param string $icon FontAwesome icon class (e.g., 'fa-chart-line', 'fa-plus-circle')
 * @param string $message The message to display below the icon
 * @return string HTML markup for the dashboard cell empty state
 */
public static function renderDashboardCellEmpty($icon = 'fa-plus-circle', $message = 'Add content here')
{
    $html = '<div class="dashboard-cell-empty" tabindex="0" role="button">';
    $html .= '<div class="cell-empty-icon">';
    $html .= '<i class="fas ' . htmlspecialchars($icon) . '"></i>';
    $html .= '</div>';
    $html .= '<div class="cell-empty-message">';
    $html .= htmlspecialchars($message);
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
```

#### 3. generateUUID()

```php
/**
 * Generate a UUID v4
 * Format: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
 *
 * @return string UUID string
 */
public static function generateUUID()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
```

#### 4. generateShortId()

```php
/**
 * Generate a short unique ID (8 characters from UUID)
 *
 * @param string $prefix Optional prefix for the ID
 * @return string Short unique ID with optional prefix
 */
public static function generateShortId($prefix = '')
{
    $uuid = self::generateUUID();
    $shortId = substr(str_replace('-', '', $uuid), 0, 8);
    return $prefix ? $prefix . '-' . $shortId : $shortId;
}
```

#### 5. renderPageHeader()

```php
/**
 * Render the page header component
 *
 * @param array $options Configuration options:
 *   - 'title' (string) Required. The page title
 *   - 'backUrl' (string|null) Optional. URL for back button
 *   - 'backLabel' (string) Optional. Back button label (default: 'Back')
 *   - 'badges' (array) Optional. Array of badge configs
 *   - 'leftContent' (string) Optional. Additional HTML for left section
 *   - 'rightContent' (string) Optional. HTML for right section
 *   - 'titleEditable' (bool) Optional. If true, title can be edited
 *   - 'titleId' (string) Optional. ID for the title element
 *   - 'titleDescription' (string) Optional. Description for info tooltip
 * @return string HTML markup for the page header
 */
public static function renderPageHeader($options)
{
    $title = isset($options['title']) ? $options['title'] : '';
    $backUrl = isset($options['backUrl']) ? $options['backUrl'] : null;
    $backLabel = isset($options['backLabel']) ? $options['backLabel'] : 'Back';
    $badges = isset($options['badges']) ? $options['badges'] : [];
    $leftContent = isset($options['leftContent']) ? $options['leftContent'] : '';
    $rightContent = isset($options['rightContent']) ? $options['rightContent'] : '';
    $titleEditable = isset($options['titleEditable']) ? $options['titleEditable'] : false;
    $titleId = isset($options['titleId']) ? $options['titleId'] : '';
    $titleDescription = isset($options['titleDescription']) ? $options['titleDescription'] : '';

    $html = '<div class="page-header">';
    $html .= '<div class="page-header-left">';

    if ($backUrl) {
        $html .= '<a href="' . htmlspecialchars($backUrl) . '" class="btn btn-secondary btn-sm" data-back-to-list>';
        $html .= '<i class="fas fa-arrow-left"></i> ' . htmlspecialchars($backLabel);
        $html .= '</a>';
    }

    if ($titleEditable) {
        $html .= '<div class="dashboard-name-editor">';
        $idAttr = $titleId ? ' id="' . htmlspecialchars($titleId) . '"' : '';
        $html .= '<h1' . $idAttr . '>' . htmlspecialchars($title) . '</h1>';
        if ($titleDescription) {
            $descriptionHtml = nl2br(htmlspecialchars($titleDescription));
            $html .= '<span class="description-tooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="' . $descriptionHtml . '"><i class="fas fa-info-circle"></i></span>';
        }
        $html .= '<button id="edit-dashboard-details-btn" class="btn btn-icon btn-outline-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Details"><i class="fas fa-pencil"></i></button>';
        $html .= '</div>';
    } else {
        $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
    }

    foreach ($badges as $badge) {
        $badgeClass = isset($badge['class']) ? $badge['class'] : 'badge-secondary';
        $html .= '<span class="badge ' . htmlspecialchars($badgeClass) . '">';
        if (isset($badge['icon'])) {
            $html .= '<i class="fas ' . htmlspecialchars($badge['icon']) . '"></i> ';
        }
        $html .= htmlspecialchars($badge['label']);
        $html .= '</span>';
    }

    if ($leftContent) {
        $html .= $leftContent;
    }

    $html .= '</div>';
    $html .= '<div class="page-header-right">';
    if ($rightContent) {
        $html .= $rightContent;
    }

    $html .= '<div class="header-separator"></div>';
    $html .= '<button type="button" class="btn btn-icon theme-toggle-btn">';
    $html .= '<i class="fas"></i>';
    $html .= '</button>';
    $html .= '<script>(function(){var m=localStorage.getItem("dgc-theme-mode")||"light",i=document.querySelector(".theme-toggle-btn i");if(i){i.classList.add(m==="dark"?"fa-moon":m==="system"?"fa-desktop":"fa-sun");}})();</script>';

    $html .= '</div>';
    $html .= '</div>';

    return $html;
}
```

---

## 9. Menu Integration

Add menu items to your navigation:

```php
// In your menu configuration
$menuItems = [
    [
        'label' => 'Graphs',
        'url' => '?urlq=graph/list',
        'icon' => 'fa-chart-bar'
    ],
    [
        'label' => 'Data Filters',
        'url' => '?urlq=data-filter/list',
        'icon' => 'fa-filter'
    ],
    [
        'label' => 'Dashboards',
        'url' => '?urlq=dashboard/list',
        'icon' => 'fa-tachometer-alt'
    ],
    [
        'label' => 'Templates',
        'url' => '?urlq=dashboard/templates',
        'icon' => 'fa-th-large'
    ]
];
```

---

## 10. System Routing

Add route cases to `system/includes/system.inc.php` in the main switch statement:

```php
// Add these cases in the switch ($url[0]) section

case "graph":
    include_once 'graph/graph.inc.php';
    break;

case "data-filter":
    include_once 'data-filter/data-filter.inc.php';
    break;

case "dashboard":
    include_once 'dashboard/dashboard.inc.php';
    break;
```

**Note:** These include files should be placed in subfolders:

- `system/includes/graph/graph.inc.php`
- `system/includes/data-filter/data-filter.inc.php`
- `system/includes/dashboard/dashboard.inc.php`

---

## 11. Update Asset Loading in Include Files

The copied include files use helper methods `Utility::addModuleCss()` and `Utility::addModuleJs()` in each function. In Rapidkart, assets are loaded **once at the top of the file** and removed from individual functions.

### Rapidkart Pattern

In Rapidkart, assets are loaded at the top of the include file (after the `<?php` tag):

```php
<?php
// Load assets once at the top
$theme->addCss(SystemConfig::stylesUrl() . 'module/module.css');
$theme->addScript(SystemConfig::scriptsUrl() . 'module/module.js');

function showList() {
    // No asset loading here - already loaded at top
}

function showForm() {
    // No asset loading here - already loaded at top
}
```

---

### File: `system/includes/graph/graph.inc.php`

**Add at top of file:**

```php
// Load graph module assets
$theme->addCss(SystemConfig::stylesUrl() . 'common/common.css');
$theme->addCss(SystemConfig::stylesUrl() . 'graph/graph.css');
$theme->addScript(SystemConfig::scriptsUrl() . 'common/common.js');
$theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph.js');
```

**Remove from functions:**
- `showList()` - Remove 4 `Utility::addModule*` calls
- `showCreator()` - Remove 4 `Utility::addModule*` calls
- `showView()` - Remove 4 `Utility::addModule*` calls

---

### File: `system/includes/data-filter/data-filter.inc.php`

**Add at top of file:**

```php
// Load data-filter module assets
$theme->addCss(SystemConfig::stylesUrl() . 'common/common.css');
$theme->addCss(SystemConfig::stylesUrl() . 'data-filter/data-filter.css');
$theme->addScript(SystemConfig::scriptsUrl() . 'common/common.js');
$theme->addScript(SystemConfig::scriptsUrl() . 'data-filter/data-filter.js');
```

**Remove from functions:**
- `showList()` - Remove 4 `Utility::addModule*` calls
- `showForm()` - Remove 4 `Utility::addModule*` calls

---

### File: `system/includes/dashboard/dashboard.inc.php`

**Add at top of file:**

```php
// Load dashboard module assets
$theme->addCss(SystemConfig::stylesUrl() . 'common/common.css');
$theme->addCss(SystemConfig::stylesUrl() . 'dashboard/dashboard.css');
$theme->addScript(SystemConfig::scriptsUrl() . 'common/common.js');
$theme->addScript(SystemConfig::scriptsUrl() . 'dashboard/dashboard.js');
```

**Remove from functions:**
- `showList()` - Remove 4 `Utility::addModule*` calls
- `showBuilder()` - Remove 4 `Utility::addModule*` calls
- `showPreview()` - Remove 4 `Utility::addModule*` calls
- `showTemplateList()` - Remove 4 `Utility::addModule*` calls
- `showTemplateEditor()` - Remove 4 `Utility::addModule*` calls
- `showTemplateCreate()` - Remove 4 `Utility::addModule*` calls
- `showTemplateBuilder()` - Remove 4 `Utility::addModule*` calls
- `showTemplatePreview()` - Remove 4 `Utility::addModule*` calls

---

### Summary

| File | Add at Top | Remove from Functions |
|------|------------|----------------------|
| `graph.inc.php` | 4 lines | 3 functions (12 lines) |
| `data-filter.inc.php` | 4 lines | 2 functions (8 lines) |
| `dashboard.inc.php` | 4 lines | 8 functions (32 lines) |

**Note:** The `Theme.js` file and page-specific scripts (e.g., `graph-list.js`) already use the Rapidkart pattern with `$theme->addScript()` and don't need to be changed.

---

## Migration Checklist

### Phase 1: Database
- [ ] Run `sql/install.sql` on your database

### Phase 2: Copy Files
- [ ] Copy `system/classes/*.php` (8 files)
- [ ] Copy `system/includes/graph/` folder
- [ ] Copy `system/includes/data-filter/` folder
- [ ] Copy `system/includes/dashboard/` folder
- [ ] Copy `system/templates/graph/` folder
- [ ] Copy `system/templates/data-filter/` folder
- [ ] Copy `system/templates/dashboard/` folder
- [ ] Copy `system/scripts/graph/` folder
- [ ] Copy `system/scripts/data-filter/` folder
- [ ] Copy `system/scripts/dashboard/` folder
- [ ] Copy compiled CSS/JS to module-specific folders (see Section 5)
- [ ] Copy new libraries to `themes/libraries/`

### Phase 3: Code Changes
- [ ] Add `renderEmptyState()` method to `Utility.php`
- [ ] Add `renderDashboardCellEmpty()` method to `Utility.php`
- [ ] Add `generateUUID()` method to `Utility.php`
- [ ] Add `generateShortId()` method to `Utility.php`
- [ ] Add `renderPageHeader()` method to `Utility.php`
- [ ] Add route cases to `system.inc.php` (graph, data-filter, dashboard)
- [ ] Update include files to replace `addModuleCss/addModuleJs` with rapidkart style
- [ ] Add menu items to navigation

### Phase 4: Testing
- [ ] Visit `?urlq=graph/list` - Graph listing works
- [ ] Visit `?urlq=graph/create` - Create graph form works
- [ ] Visit `?urlq=data-filter/list` - Data filter listing works
- [ ] Visit `?urlq=data-filter/create` - Create data filter form works
- [ ] Visit `?urlq=dashboard/list` - Dashboard listing works
- [ ] Visit `?urlq=dashboard/templates` - Template listing works
- [ ] Create a dashboard from template
- [ ] Add graph to dashboard
- [ ] Preview dashboard with filters
- [ ] Test light/dark theme toggle
