# Template System Migration Guide

This document explains how to migrate pages from the old full-HTML template style to the new ThemeRegistry-based system.

## Overview

The new system separates concerns:
- **html.tpl.php** - Central HTML shell (DOCTYPE, head, body tags, core libraries)
- **.inc.php files** - Register page-specific CSS/JS via ThemeRegistry
- **Template files** - Return only body content (no HTML shell)

## Migration Status

### Completed Pages

| Module | Page | .inc.php | Template | JS File |
|--------|------|----------|----------|---------|
| Graph | List | ✅ `showList()` | ✅ `graph-list.php` | ✅ `graph-list.js` |
| Graph | Creator | ✅ `showCreator()` | ✅ `graph-creator.php` | - |
| Graph | View | ✅ `showView()` | ✅ `graph-view.php` | - |
| Filter | List | ✅ `showFilterList()` | ✅ `filter-list.php` | ✅ `filter-list.js` |
| Filter | Form | ✅ `showFilterForm()` | ✅ `filter-form.php` | - |
| Dashboard | List | ✅ `showList()` | ✅ `dashboard-list.php` | ✅ `dashboard-list.js` |
| Dashboard | Builder | ✅ `showBuilder()` | ✅ `dashboard-builder.php` | - |
| Dashboard | Preview | ✅ `showPreview()` | ✅ `dashboard-preview.php` | ✅ `dashboard-preview.js` |
| Template | List | ✅ `showTemplateList()` | ✅ `template-list.php` | ✅ `template-list.js` |
| Template | Creator | ✅ `showTemplateCreator()` | ✅ `template-creator.php` | - |
| Template | Editor | ✅ `showTemplateEditor()` | ✅ `template-editor.php` | - |
| Template | Builder | ✅ `showTemplateBuilder()` | ✅ `template-builder.php` | - |
| Template | Preview | ✅ `showTemplatePreview()` | ✅ `template-preview.php` | ✅ `template-preview.js` |

## Folder Structure

```
dynamic_graph_creator/
├── themes/
│   └── libraries/                    # CDN libraries stored locally
│       ├── bootstrap/
│       │   ├── css/bootstrap.min.css
│       │   └── js/bootstrap.bundle.min.js
│       ├── fontawesome/
│       │   ├── css/all.min.css
│       │   └── webfonts/
│       ├── echarts/
│       │   └── echarts.min.js
│       ├── codemirror/
│       │   ├── css/codemirror.css
│       │   ├── css/material.min.css
│       │   ├── js/codemirror.min.js
│       │   ├── js/sql.min.js
│       │   └── js/placeholder.min.js
│       ├── jquery/
│       │   └── jquery.min.js
│       ├── moment/
│       │   └── moment.min.js
│       ├── daterangepicker/
│       │   ├── css/daterangepicker.css
│       │   └── js/daterangepicker.min.js
│       └── autosize/
│           └── autosize.min.js
│
├── dist/                             # Compiled module assets (current)
│   ├── common.*.css
│   ├── common.*.js
│   ├── graph.*.css
│   ├── graph.*.js
│   ├── filter.*.css
│   ├── filter.*.js
│   ├── dashboard.*.css
│   └── dashboard.*.js
│
└── system/
    ├── scripts/
    │   ├── src/                      # Source JS files
    │   │   └── Theme.js
    │   ├── graph/
    │   │   └── graph-list.js         # Delete modal handler
    │   ├── filter/
    │   │   └── filter-list.js        # Delete modal handler
    │   └── dashboard/
    │       ├── dashboard-list.js     # Delete modal handler
    │       ├── dashboard-preview.js  # Delete functionality
    │       ├── template-list.js      # Delete handlers for templates/categories
    │       └── template-preview.js   # Placeholder for preview scripts
    │
    ├── styles/                       # Future: module CSS folders
    │
    └── templates/
        ├── html.tpl.php              # Central HTML shell
        ├── graph/
        │   ├── graph-list.php        # Body content only
        │   ├── graph-creator.php
        │   └── graph-view.php
        ├── filter/
        │   ├── filter-list.php
        │   └── filter-form.php
        └── dashboard/
            ├── dashboard-list.php
            ├── dashboard-builder.php
            ├── dashboard-preview.php
            ├── template-list.php
            ├── template-creator.php
            ├── template-editor.php
            ├── template-builder.php
            └── template-preview.php
```

## Migration Steps for a Page

### Step 1: Update the .inc.php file

Add CSS/JS registration at the top of your show function:

```php
function showList()
{
    $theme = Rapidkart::getInstance()->getThemeRegistry();

    // Add page-specific CSS using helper functions
    Utility::addModuleCss('common');
    Utility::addModuleCss('graph');  // or 'filter', 'dashboard', etc.

    // Add page-specific JS using helper functions
    Utility::addModuleJs('common');
    $theme->addScript(SystemConfig::scriptsUrl() . 'src/Theme.js');
    Utility::addModuleJs('graph');

    // Add page-specific scripts (if any)
    $theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph-list.js');

    // Set page title
    $theme->setPageTitle('Page Title - Dynamic Graph Creator');

    // Get content from template using output buffering
    $someData = getData();
    ob_start();
    require_once SystemConfig::templatesPath() . 'module/template.php';
    $content = ob_get_clean();

    // Set content to theme
    $theme->setContent('full_main', $content);
}
```

### Step 2: Update the template file

Remove from template:
- `<!DOCTYPE html>`
- `<html>`, `</html>`
- `<head>`, `</head>` and all contents
- `<body>`, `</body>`
- All `<link>` CSS tags
- All `<script src="">` tags

Keep in template:
- PHP logic for building content
- HTML markup (divs, containers, modals, etc.)

### Step 3: Move inline scripts to separate JS files

Create a new JS file in the appropriate folder:
```
system/scripts/module/module-page.js
```

Then add it in the .inc.php:
```php
$theme->addScript(SystemConfig::scriptsUrl() . 'module/module-page.js');
```

## API Reference

### ThemeRegistry Methods

```php
$theme = Rapidkart::getInstance()->getThemeRegistry();

// Add CSS file
$theme->addCss($url, $weight = 10);

// Add JavaScript file
$theme->addScript($url, $weight = 10);

// Set page title
$theme->setPageTitle($title);

// Set page content
$theme->setContent($region, $content);
```

### Utility Helper Functions

```php
// Add CSS from dist folder (uses manifest.json for cache busting)
Utility::addModuleCss('common');
Utility::addModuleCss('graph');
Utility::addModuleCss('filter');
Utility::addModuleCss('dashboard');

// Add JS from dist folder
Utility::addModuleJs('common');
Utility::addModuleJs('graph');
Utility::addModuleJs('filter');
Utility::addModuleJs('dashboard');
```

### Config URL Helpers

```php
// Theme libraries (Bootstrap, Font Awesome, etc.)
SiteConfig::themeLibrariessUrl()  // => /themes/libraries/

// System scripts
SystemConfig::scriptsUrl()        // => /system/scripts/

// System styles
SystemConfig::stylesUrl()         // => /system/styles/

// Dist folder
SystemConfig::distUrl()           // => /dist/
```

## Example: Adding a Library

To add a library from themes/libraries:

```php
$theme->addScript(SiteConfig::themeLibrariessUrl() . 'echarts/echarts.min.js');
$theme->addCss(SiteConfig::themeLibrariessUrl() . 'codemirror/codemirror.css');
```

## Weight System

Lower weight = loads earlier. Use weights to control load order:

```php
$theme->addCss($commonCss, 5);    // Loads first
$theme->addCss($moduleCss, 10);   // Loads second
$theme->addCss($pageCss, 15);     // Loads third
```

## Future Migration to Rapidkart Structure

When migrating to rapidkart-style folder structure (CSS/JS in module folders):

**Current (using dist with hash):**
```php
Utility::addModuleCss('graph');
Utility::addModuleJs('graph');
```

**Future (rapidkart style):**
```php
$theme->addCss(SystemConfig::stylesUrl() . 'graph/graph.css');
$theme->addScript(SystemConfig::scriptsUrl() . 'graph/graph.js');
```

File mapping:
- `dist/common.*.css` → `system/styles/common/common.css`
- `dist/common.*.js` → `system/scripts/common/common.js`
- `dist/graph.*.css` → `system/styles/graph/graph.css`
- `dist/graph.*.js` → `system/scripts/graph/graph.js`
- `dist/filter.*.css` → `system/styles/filter/filter.css`
- `dist/filter.*.js` → `system/scripts/filter/filter.js`
- `dist/dashboard.*.css` → `system/styles/dashboard/dashboard.css`
- `dist/dashboard.*.js` → `system/scripts/dashboard/dashboard.js`

## Compatibility with Rapidkart

These patterns work in both projects:

```php
$theme = Rapidkart::getInstance()->getThemeRegistry();
$theme->addScript(SiteConfig::themeLibrariessUrl() . "library/file.js");
$theme->addCss(SiteConfig::themeLibrariessUrl() . "library/file.css");
$theme->setPageTitle('Page Title');
$theme->setContent('full_main', $content);
```

## Checklist for Migrating a New Page

- [ ] Update .inc.php to use ThemeRegistry
- [ ] Add `Utility::addModuleCss()` calls
- [ ] Add `Utility::addModuleJs()` calls
- [ ] Add page-specific scripts via `$theme->addScript()`
- [ ] Set page title via `$theme->setPageTitle()`
- [ ] Use output buffering to capture template content
- [ ] Call `$theme->setContent('full_main', $content)`
- [ ] Remove HTML shell from template (DOCTYPE, html, head, body tags)
- [ ] Remove CSS link tags from template
- [ ] Remove script src tags from template
- [ ] Move inline scripts to separate JS files
- [ ] Test page loads correctly
- [ ] Verify all CSS loads (check Network tab)
- [ ] Verify all JS loads and works
- [ ] Test all interactive features (modals, buttons, etc.)

## Key Files Created During Migration

### Core Infrastructure

| File | Purpose |
|------|---------|
| `system/classes/ThemeRegistry.php` | Central class for managing page rendering |
| `system/utilities/SiteConfig.php` | Provides `themeLibrariessUrl()` for rapidkart compatibility |
| `system/templates/html.tpl.php` | Central HTML shell template |

### Modified Core Files

| File | Changes |
|------|---------|
| `system/utilities/SystemConfig.php` | Added `themesUrl()`, `themesPath()` |
| `system/classes/Rapidkart.php` | Added `getThemeRegistry()` singleton method |
| `system/classes/Utility.php` | Added `addModuleCss()`, `addModuleJs()` helpers |
| `index.php` | Added requires and `renderPage()` call |

### Page-Specific JS Files

| File | Purpose |
|------|---------|
| `system/scripts/graph/graph-list.js` | Delete modal handler for graph list |
| `system/scripts/graph/graph-creator.js` | Graph config initialization when editing |
| `system/scripts/filter/filter-list.js` | Delete modal handler for filter list |
| `system/scripts/dashboard/dashboard-list.js` | Delete modal handler for dashboard list |
| `system/scripts/dashboard/dashboard-builder.js` | Dashboard builder initialization and name editing |
| `system/scripts/dashboard/dashboard-preview.js` | Delete functionality for dashboard preview |
| `system/scripts/dashboard/template-list.js` | Delete handlers for templates and categories |
| `system/scripts/dashboard/template-editor.js` | Template create/edit form validation |
| `system/scripts/dashboard/template-builder.js` | Template builder initialization and details editing |
| `system/scripts/dashboard/template-preview.js` | TemplateManager initialization for delete/duplicate |

## Inline Scripts That Must Stay Inline

Some small inline scripts need to stay in templates to prevent flash of unstyled content (FOUC):

| Template | Script Purpose | Reason |
|----------|----------------|--------|
| `graph-creator.php` | Sidebar collapsed state IIFE | Must run before DOM ready to prevent flash |
| `template-list.php` | Category collapsed state per category | Must run immediately as each category renders |

These scripts are small IIFEs that read from localStorage and apply CSS classes before the page fully loads.

## Libraries in themes/libraries/

All CDN libraries are now stored locally for offline use and faster loading:

| Library | Version | Files |
|---------|---------|-------|
| Bootstrap | 5.3.2 | `css/bootstrap.min.css`, `js/bootstrap.bundle.min.js` |
| Font Awesome | 6.5.1 | `css/all.min.css`, `webfonts/*` |
| ECharts | 5.4.3 | `echarts.min.js` |
| CodeMirror | 5.65.16 | `css/codemirror.css`, `css/material.min.css`, `js/codemirror.min.js`, `js/sql.min.js`, `js/placeholder.min.js` |
| jQuery | 3.7.1 | `jquery.min.js` |
| Moment.js | 2.30.1 | `moment.min.js` |
| Daterangepicker | 3.1 | `css/daterangepicker.css`, `js/daterangepicker.min.js` |
| Autosize | 6.0.1 | `autosize.min.js` |
| SortableJS | 1.15.0 | `Sortable.min.js` |
