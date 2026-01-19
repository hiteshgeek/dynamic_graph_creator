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
| `DGCHelper.php` | DGC-specific UI components (empty state, page header, UUID generation) |
| `Graph.php` | Graph model (CRUD operations) |
| `GraphManager.php` | Graph retrieval and management |
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

**Note:** The migration tool automatically transforms asset loading calls during copy:

| Original (DGC)                          | Transformed (Rapidkart)                                      |
| --------------------------------------- | ------------------------------------------------------------ |
| `Utility::addModuleCss('common')`       | `$theme->addCss(SystemConfig::stylesUrl() . 'common/common.css')` |
| `Utility::addModuleCss('graph')`        | `$theme->addCss(SystemConfig::stylesUrl() . 'graph/graph.css')` |
| `Utility::addModuleJs('common')`        | `$theme->addScript(SystemConfig::scriptsUrl() . 'common/common.js')` |
| `Utility::addModuleJs('graph')`         | `$theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph.js')` |

---

## 3. Templates

Copy entire folders to: `system/templates/`

**Note:** Templates use the `.tpl.php` extension and are organized into `views/` (display/list pages) and `forms/` (create/edit pages) subfolders following Rapidkart conventions.

### Graph Templates

| File | Description |
|------|-------------|
| `graph/views/graph-list.tpl.php` | Graph listing page |
| `graph/views/graph-view.tpl.php` | Graph preview with filters |
| `graph/forms/graph-creator.tpl.php` | Graph create/edit form |

### Data Filter Templates

| File | Description |
|------|-------------|
| `data-filter/views/data-filter-list.tpl.php` | Data filter listing page |
| `data-filter/forms/data-filter-form.tpl.php` | Data filter create/edit form |

### Dashboard Templates

| File | Description |
|------|-------------|
| `dashboard/views/dashboard-list.tpl.php` | Dashboard listing page |
| `dashboard/views/dashboard-preview.tpl.php` | Dashboard preview page |
| `dashboard/views/template-list.tpl.php` | Template listing with categories |
| `dashboard/views/template-preview.tpl.php` | Template preview page |
| `dashboard/forms/dashboard-builder.tpl.php` | Dashboard builder interface |
| `dashboard/forms/template-editor.tpl.php` | Template creation/edit form |
| `dashboard/forms/template-builder.tpl.php` | Visual template builder |

### Template Loading Pattern

The include files use the `Template` class to load templates:

```php
// Example from graph.inc.php
$tpl = new Template(SystemConfig::templatesPath() . 'graph/views/graph-list');
$tpl->graphs = GraphManager::getAll();
$theme->setContent('full_main', $tpl->parse());
```

**Key points:**
- Template path is passed without the `.tpl.php` extension
- Variables are set via `$tpl->varName = value`
- `parse()` returns the rendered HTML

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
| `bootstrap5/` | `bootstrap5/` | `css/bootstrap.min.css`, `js/bootstrap.bundle.min.js` | Bootstrap 5.3.2 |
| `jquery/` | `jquery/` | `jquery.min.js` | jQuery 3.7.1 |
| `fontawesome/` | `fontawesome/` | `css/all.min.css`, `webfonts/*` | Font Awesome 6.5.1 |
| `moment/` | `moment2/` | `moment.min.js` | Moment.js 2.30.1 |
| `echarts/` | `echarts/` | `echarts.min.js` | ECharts 5.4.3 |
| `codemirror/` | `codemirror/` | `css/*.css`, `js/*.js` | CodeMirror 5.65.16 |
| `daterangepicker/` | `daterangepicker/` | `css/daterangepicker.css`, `js/daterangepicker.min.js` | Daterangepicker 3.1 |
| `autosize/` | `autosize/` | `autosize.min.js` | Autosize 6.0.1 |
| `sortablejs/` | `sortablejs/` | `Sortable.min.js` | SortableJS 1.15.0 |

**Note:** The include files and templates reference these paths directly (e.g., `bootstrap5/`). The folder names match between DGC and Rapidkart, so no renaming is needed during migration.

---

## 7. Database Setup

Run SQL file: `sql/install.sql`

Creates tables:

- `graph` - Graph definitions
- `data_filter` - Data filter definitions
- `dashboard_template_category` - Template categories (4 system categories)
- `dashboard_template` - Templates (16 system templates)
- `dashboard_instance` - User dashboard instances

### SystemTables Constants

Add these constants to Rapidkart's `system/utilities/SystemTables.php`:

```php
// Dynamic Graph Creator Tables
const DB_TBL_GRAPH = "graph";
const DB_TBL_DATA_FILTER = "data_filter";
const DB_TBL_DASHBOARD_TEMPLATE_CATEGORY = "dashboard_template_category";
const DB_TBL_DASHBOARD_TEMPLATE = "dashboard_template";
const DB_TBL_DASHBOARD_INSTANCE = "dashboard_instance";
```

**Note:** Status columns use the pattern `{prefix}sid` (e.g., `gsid`, `dfsid`, `disid`) where `1=active` and `3=deleted`.

---

## 8. Code Modifications

### DGCHelper.php

The `DGCHelper.php` class (copied in Step 1) contains DGC-specific UI components:

| Method | Description |
|--------|-------------|
| `renderEmptyState()` | Empty state UI component with icon, title, description, and optional button |
| `renderDashboardCellEmpty()` | Dashboard cell empty state for preview pages |
| `generateUUID()` | UUID v4 generation |
| `generateShortId()` | Short unique ID generation (8 chars from UUID) |
| `renderPageHeader()` | Page header with back button, badges, and theme toggle |

**Usage:** All copied template files use `DGCHelper::` directly. No Utility.php modifications needed.

### Template.php (Manual Modification Required)

Rapidkart already has a `Template.php` class. **Do not copy** DGC's Template.php. Instead, modify Rapidkart's existing `Template.php` to add the `.dgc-app` wrapper for CSS isolation.

**Add this wrapper in Rapidkart's `Template::parse()` method:**

```php
public function parse()
{
    if (!$this->template) return false;
    ob_start();
    extract($this->variables, EXTR_SKIP);
    require $this->template;
    $content = ob_get_clean();

    // Wrap content in .dgc-app for CSS isolation
    // This prevents DGC styles from conflicting with Rapidkart's Bootstrap 3
    return '<div class="dgc-app">' . $content . '</div>';
}
```

**Bootstrap:** Ensure `DGCHelper.php` is loaded in your bootstrap/autoloader:

```php
require_once 'system/classes/DGCHelper.php';
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

## 11. Asset Loading (Automatic Transformation)

**Good news!** The migration tool **automatically transforms** asset loading calls when copying include files.

### Automatic Transformation

When you run Step 2 ("Copy Include Files"), the migration tool transforms:

| Original (DGC) | Transformed (Rapidkart) |
|----------------|-------------------------|
| `Utility::addModuleCss('common')` | `$theme->addCss(SystemConfig::stylesUrl() . 'common/common.css')` |
| `Utility::addModuleCss('graph')` | `$theme->addCss(SystemConfig::stylesUrl() . 'graph/graph.css')` |
| `Utility::addModuleJs('common')` | `$theme->addScript(SystemConfig::scriptsUrl() . 'common/common.js')` |
| `Utility::addModuleJs('graph')` | `$theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph.js')` |

### What Stays the Same

Page-specific scripts and external library includes already use the correct Rapidkart pattern:

```php
// These are NOT transformed (already correct):
$theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph-list.js');
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js', 5);
```

**No manual changes needed for asset loading!**

---

## Migration Checklist

### Phase 1: Database
- [ ] Run `sql/install.sql` on your database
- [ ] Add table constants to `system/utilities/SystemTables.php` (see Section 7)

### Phase 2: Copy Files
- [ ] Copy `system/classes/*.php` (10 files - does NOT include Template.php)
- [ ] Copy `system/includes/graph/` folder
- [ ] Copy `system/includes/data-filter/` folder
- [ ] Copy `system/includes/dashboard/` folder
- [ ] Copy `system/templates/graph/views/` and `graph/forms/` folders
- [ ] Copy `system/templates/data-filter/views/` and `data-filter/forms/` folders
- [ ] Copy `system/templates/dashboard/views/` and `dashboard/forms/` folders
- [ ] Copy `system/scripts/graph/` folder
- [ ] Copy `system/scripts/data-filter/` folder
- [ ] Copy `system/scripts/dashboard/` folder
- [ ] Copy compiled CSS/JS to module-specific folders (see Section 5)
- [ ] Copy new libraries to `themes/libraries/`

### Phase 3: Code Changes

- [ ] Add `require_once 'system/classes/DGCHelper.php'` to bootstrap
- [ ] Modify Rapidkart's `Template.php` to add `.dgc-app` wrapper (see Section 8)
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

---

## 12. CSS Isolation (Bootstrap 3 Compatibility)

DGC uses Bootstrap 5 and Font Awesome 6, which would normally conflict with Rapidkart's Bootstrap 3 and Font Awesome 4.7 styles. To prevent conflicts, DGC CSS is **automatically scoped** under a `.dgc-app` wrapper class.

### How It Works

1. **Build-time CSS scoping**: The build process uses `postcss-prefix-selector` to automatically prefix all DGC CSS selectors with `.dgc-app`.

2. **Template wrapper**: The `Template::parse()` method automatically wraps all rendered content in `<div class="dgc-app">...</div>`.

3. **Result**: DGC styles only apply to elements inside the `.dgc-app` wrapper, leaving Rapidkart's Bootstrap 3 styles unaffected.

### What's Excluded from Prefixing

The following selectors are **not** prefixed (they need to work globally):

| Selector | Reason |
|----------|--------|
| `:root`, `:root.theme-dark`, `:root.theme-light` | CSS variables for theming |
| `[data-theme]` | Theme attribute selectors |
| `@keyframes`, `@font-face` | Animation and font declarations |
| `body`, `html`, `*` | Global selectors |

### CSS Output Example

```css
/* Before (SCSS source) */
.btn { display: flex; }
.card { padding: 1rem; }

/* After (compiled CSS) */
.dgc-app .btn { display: flex; }
.dgc-app .card { padding: 1rem; }
```

### No Manual Work Required

This is all handled automatically:
- Build process prefixes CSS selectors
- Template class wraps content
- No changes needed to template files or SCSS

### Troubleshooting

If DGC styles don't apply correctly:

1. **Check wrapper**: Ensure content is inside `.dgc-app` wrapper (inspect HTML)
2. **CSS specificity**: If Rapidkart styles still override, increase specificity in SCSS
3. **Theme variables**: CSS variables (`:root`) are global and may affect other pages if variable names conflict (all DGC variables use `--dgc-` prefix)
