# Dynamic Graph Creator - Project Structure

## Overview

This project (DGC) is designed to work seamlessly with the live Rapidkart project. Some files are copied from the live project to ensure compatibility, while others are specific to DGC.

---

## File Classification

### DO NOT MODIFY - Copied from Live Project (rapidkartprocessadminv2)

These files are direct copies from the live Rapidkart project. Any changes should be made in the live project first, then copied here to maintain compatibility.

#### Classes (`system/classes/`)

| File                           | Purpose                         |
| ------------------------------ | ------------------------------- |
| `AdminUser.php`                | User entity class               |
| `AdminUserManager.php`         | User management functions       |
| `Session.php`                  | Session handling (login/logout) |
| `SessionsManager.php`          | Session DB operations           |
| `SessionDetails.php`           | Session details entity          |
| `Licence.php`                  | Licence entity class            |
| `LicenceCompanies.php`         | Company entity class            |
| `LicenceManager.php`           | Licence management functions    |
| `LicenceDomain.php`            | Domain entity class             |
| `Outlet.php`                   | Outlet entity class             |
| `OutletManager.php`            | Outlet management functions     |
| `Warehouse.php`                | Warehouse entity class          |
| `WarehouseManager.php`         | Warehouse management functions  |
| `Rapidkart.php`                | Singleton pattern for DB access |
| `SiteVariable.php`             |                                 |
| `SiteVariableManager.php`      |                                 |
| `SystemPreferences.php`        |                                 |
| `SystemPreferencesGroup.php`   |                                 |
| `SystemPreferencesManager.php` |                                 |
| `SystemPreferencesMapping.php` |                                 |
| `SQLiDatabase.php`             | Database wrapper                |
| `Template.php`                 | Template engine                 |
| `ThemeRegistry.php`            | Theme/asset management          |
| `Utility.php`                  | Utility functions               |

#### Configuration (`system/config/`)

| File             | Purpose                                                |
| ---------------- | ------------------------------------------------------ |
| `BaseConfig.php` | Base configuration (DB credentials, static properties) |

#### Interfaces (`system/interfaces/`)

| File                   | Purpose                                      |
| ---------------------- | -------------------------------------------- |
| `DatabaseObject.php`   | Database object interface                    |
| `User.php`             | User interface (extends DatabaseObject)      |
| `UniqueIdentifier.php` | Unique ID interface (extends DatabaseObject) |

#### Utilities (`system/utilities/`)

| File                     | Purpose                                     |
| ------------------------ | ------------------------------------------- |
| `SystemTables.php`       | Database table constants (subset from live) |
| `SystemTablesStatus.php` | Database table constants (subset from live) |
| `SystemConfig.php`       | System configuration                        |
| `SiteConfig.php`         | Site-specific configuration                 |
| `LocalProjectConfig.php` | DGC-specific config (.env, DB, dist paths)  |

#### Includes (`system/includes/`)

| Directory           | Purpose          |
| ------------------- | ---------------- |
| `functions.inc.php` | Common Functions |

---

### DGC SPECIFIC - Can be Modified

These files are specific to Dynamic Graph Creator and can be modified as needed.

#### Root Files

| File                 | Purpose                        |
| -------------------- | ------------------------------ |
| `index.php`          | Main entry point, routing      |
| `simulate_login.php` | Quick login/logout for testing |
| `migrate.php`        | Database migration script      |
| `.env`               | Environment configuration      |

#### Classes (`system/classes/`)

| File                                    | Purpose                                |
| --------------------------------------- | -------------------------------------- |
| `Graph.php`                             | Graph entity class                     |
| `GraphManager.php`                      | Graph CRUD operations                  |
| `DataFilter.php`                        | Data filter entity class               |
| `DataFilterSet.php`                     | Filter set entity class                |
| `DataFilterManager.php`                 | Filter CRUD operations                 |
| `DashboardTemplate.php`                 | Dashboard template entity              |
| `DashboardTemplateCategory.php`         | Template category entity               |
| `DashboardInstance.php`                 | Dashboard instance entity              |
| `DashboardBuilder.php`                  | Dashboard builder logic                |
| `DGCHelper.php`                         | DGC-specific helper functions          |
| `SimulateLogin.php`                     | Login bootstrap helper (sets config)   |
| `LocalUtility.php`                      | DGC-specific utility (assets, parsing) |
| `SystemPlaceholder.php`                 | System placeholder entity              |
| `SystemPlaceholderManager.php`          | Placeholder management                 |
| `WidgetCategory.php`                    | Widget category entity                 |
| `WidgetCategoryManager.php`             | Widget category management             |
| `GraphWidgetCategoryMapping.php`        | Graph-category mapping entity          |
| `GraphWidgetCategoryMappingManager.php` | Graph-category mapping management      |

#### Includes (`system/includes/`)

| Directory      | Purpose                       |
| -------------- | ----------------------------- |
| `graph/`       | Graph module controller       |
| `data-filter/` | Data filter module controller |
| `dashboard/`   | Dashboard module controller   |
| `login/`       | Login page controller         |

#### Templates (`system/templates/`)

All templates are DGC-specific and can be modified.

#### Themes (`themes/`)

All theme files (CSS, JS, images) are DGC-specific.

---

## Database Tables

### From Live Project (shared tables)

| Table                   | Purpose                 |
| ----------------------- | ----------------------- |
| `auser`                 | User accounts           |
| `auser_session`         | User sessions           |
| `auser_company_mapping` | User-company mapping    |
| `licence`               | Licence information     |
| `licence_companies`     | Companies under licence |
| `licence_domains`       | Domain configuration    |
| `outlet`                | Outlet/branch data      |
| `outlet_user_mapping`   | User-outlet mapping     |
| `warehouse`             | Warehouse data          |

### DGC Specific Tables

| Table                             | Purpose                 |
| --------------------------------- | ----------------------- |
| `dgc_graph`                       | Graph definitions       |
| `dgc_data_filter`                 | Data filter definitions |
| `dgc_data_filter_set`             | Filter set definitions  |
| `dgc_dashboard_template`          | Dashboard templates     |
| `dgc_dashboard_template_category` | Template categories     |
| `dgc_dashboard_instance`          | Dashboard instances     |
| `dgc_placeholder_setting`         | Placeholder settings    |

---

## Quick Reference

### Login/Logout for Testing

In `index.php`, uncomment one of these lines:

```php
// SimulateLogin::loginById(1); header('Location: .?urlq=graph'); exit; // Login by user ID
// SimulateLogin::loginByEmail('your@email.com'); header('Location: .?urlq=graph'); exit; // Login by email
// SimulateLogin::logout(); header('Location: .?urlq=login'); exit; // Logout
```

**How it works:**

- `SimulateLogin::loginById($uid)` sets up `BaseConfig::$licence_id` and `BaseConfig::$company_id` from user data, then calls `Session::loginUser()`
- This mimics the live project's flow where `system.inc.php` sets licence_id from domain lookup and `login.inc.php` sets company_id from authenticated user

### Session Check Flow

```
index.php
  └── Session::init()
  └── Session::isLoggedIn(true)
        ├── Yes → Load requested page
        └── No  → Redirect to login or show 401
```

### Live Project Path

`/var/www/html/rapidkartprocessadminv2`

### Local Project Path

`/var/www/html/dynamic_graph_creator`

---

## Migration to Live

When moving DGC to the live project:

1. Copy DGC-specific classes to live project
2. Copy DGC templates and themes
3. Run DGC table migrations on live database
4. Remove stub classes (RapidkartStubs.php) - live project has real classes
5. Update paths in configuration files

The login/session system will work seamlessly as it uses the same Session class and database tables.
