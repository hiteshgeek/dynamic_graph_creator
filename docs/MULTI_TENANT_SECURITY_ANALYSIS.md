# Multi-Tenant Query Security & Empty Filter Analysis

## Executive Summary

This document analyzes security concerns in a multi-tenant DGC environment where multiple clients share the same server. The primary goal is to prevent data breaches via SQL queries in graphs, counters, tables, and filters.

**Critical Issue**: Current "allow empty" behavior replaces placeholders with `1=1` which bypasses filtering entirely, exposing all tenants' data.

---

## Table of Contents

1. [Problem Statement](#problem-statement)
2. [Key Insight: Not All Tables Have company_id](#key-insight-not-all-tables-have-company_id)
3. [Scenarios to Address](#scenarios-to-address)
4. [Concrete Solutions](#concrete-solutions)
5. [Database Schema Changes](#database-schema-changes)
6. [Implementation Summary](#implementation-summary)
7. [Questions for Team Discussion](#questions-for-team-discussion)
8. [Decision Matrix](#decision-matrix)
9. [Next Steps](#next-steps)

---

## Problem Statement

When a filter placeholder is empty and `allowEmpty=true`, the current system replaces:

```sql
WHERE company_id IN (::company_id)
```

With:

```sql
WHERE company_id IN (1=1)  -- Invalid SQL or bypasses security
```

**This is a critical security flaw in a multi-tenant environment.**

### Current Code Flow

1. User doesn't select a company filter value
2. `QueryHelper::replaceQueryPlaceholders()` checks if value is empty
3. If `allowEmpty=true`, replaces `::company_id` with `1=1`
4. Query returns ALL companies' data = **DATA BREACH**

---

## Key Insight: Not All Tables Have company_id

We need a **Table Registry** that tracks:
- Which tables have `company_id` field
- The column name (usually `company_id` but could vary)
- Whether filtering is required for that table

### Example Registry Structure

```php
// Tables WITH company_id - REQUIRE filtering
$companyFilteredTables = [
    'orders' => ['column' => 'company_id', 'required' => true],
    'customers' => ['column' => 'company_id', 'required' => true],
    'invoices' => ['column' => 'company_id', 'required' => true],
    'products' => ['column' => 'company_id', 'required' => true],
    'users' => ['column' => 'company_id', 'required' => true],
];

// Tables WITHOUT company_id - Master/shared data (no filter needed)
$masterDataTables = [
    'countries',    // Shared country list
    'currencies',   // Shared currencies
    'settings',     // Global settings
];
```

---

## Scenarios to Address

### Scenario 1: Simple Single-Value Placeholders

```sql
SELECT * FROM orders WHERE company_id = ::company_id
```

| Aspect | Details |
|--------|---------|
| **Current behavior when empty** | Replaces with `1=1` → Returns ALL companies' data |
| **Risk Level** | 🔴 **CRITICAL** - Complete data breach |
| **Frequency** | Very common |

---

### Scenario 2: IN Clause Placeholders

```sql
SELECT * FROM orders WHERE company_id IN (::company_id)
```

| Aspect | Details |
|--------|---------|
| **Current behavior when empty** | Invalid SQL `IN (1=1)` or bypasses filter |
| **Risk Level** | 🔴 **CRITICAL** - Data breach or query error |
| **Frequency** | Common for multi-select |

---

### Scenario 3: Date Range Placeholders

```sql
SELECT * FROM orders
WHERE order_date BETWEEN ::date_from AND ::date_to
AND company_id = ::company_id
```

| Aspect | Details |
|--------|---------|
| **Unique Challenge** | ONE filter creates TWO placeholders (`::date_from`, `::date_to`) |
| **Current behavior when empty** | Replaces with `1=1` → Invalid BETWEEN syntax |
| **Risk Level** | 🟡 **MEDIUM** - Performance (all dates) or missing data |
| **Question** | What should date range default to when empty? |

---

### Scenario 4: Subqueries

```sql
SELECT * FROM orders o
WHERE o.company_id = ::company_id
AND o.customer_id IN (
    SELECT c.customer_id FROM customers c
    WHERE c.company_id = ::company_id
)
```

| Aspect | Details |
|--------|---------|
| **Unique Challenge** | Same placeholder used in main query AND subquery |
| **Risk** | If only main query filtered, subquery leaks data |
| **Risk Level** | 🔴 **CRITICAL** - Subquery can expose other companies' data |

---

### Scenario 5: JOINs

```sql
SELECT o.*, c.name
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
WHERE o.company_id = ::company_id
```

| Aspect | Details |
|--------|---------|
| **Unique Challenge** | `orders` filtered but `customers` NOT filtered |
| **Risk** | Customer data from other companies exposed via JOIN |
| **Risk Level** | 🔴 **CRITICAL** - JOIN brings in unfiltered data |

---

### Scenario 6: UNIONs

```sql
SELECT order_id, 'order' as type FROM orders WHERE company_id = ::company_id
UNION
SELECT invoice_id, 'invoice' as type FROM invoices WHERE company_id = ::company_id
```

| Aspect | Details |
|--------|---------|
| **Unique Challenge** | Each UNION part needs company filtering |
| **Risk** | One part unfiltered = data breach |
| **Risk Level** | 🔴 **CRITICAL** - UNION combines secure and insecure data |

---

### Scenario 7: Multi-Select Filters

```sql
SELECT * FROM orders WHERE status IN (::status)
```

| Aspect | Details |
|--------|---------|
| **Unique Challenge** | `::status` could be array: `['pending', 'shipped']` |
| **Question** | When empty, show ALL statuses or NONE? |
| **Risk Level** | 🟢 **LOW** - UX issue, not security |

---

### Scenario 8: Text Search Filters

```sql
SELECT * FROM orders WHERE order_notes LIKE CONCAT('%', ::search, '%')
```

| Aspect | Details |
|--------|---------|
| **Unique Challenge** | Empty search = match everything? |
| **Risk Level** | 🟢 **LOW** - UX issue, not security |

---

## Concrete Solutions

### Solution 1 & 2: Simple & IN Clause Placeholders

**Approach**: `use_all_from_query` - When empty, get ALL values from filter's data_query

```php
// In QueryHelper.php - when placeholder is empty
public static function getEmptyReplacementValue($placeholder, $filter) {
    $settings = $filter->getEmptyBehaviorSettings();

    switch ($settings['emptyBehavior']) {
        case 'use_all_from_query':
            // Execute the filter's data_query and get all values
            $options = $filter->getOptions();
            if (empty($options)) {
                return null; // No values available - block query
            }
            $values = array_column($options, 'value');
            return $values; // Return array for IN clause

        case 'use_logged_in_value':
            // Use system placeholder value
            $systemKey = $settings['systemPlaceholder']; // e.g., 'logged_in_company_ids'
            return SystemPlaceholderManager::getValue($systemKey);

        case 'use_default':
            return $settings['emptyDefault'];

        case 'block':
        default:
            return null; // Will trigger validation error
    }
}
```

**Result**:

```sql
-- Original: WHERE company_id = ::company_id
-- When empty with use_all_from_query:
-- Becomes: WHERE company_id IN (1, 2, 3)  -- only user's accessible companies

-- Original: WHERE company_id IN (::company_id)
-- When empty with use_all_from_query:
-- Becomes: WHERE company_id IN (1, 2, 3)  -- same result
```

**Security**: Filter's `data_query` already respects user's licence permissions, so only accessible companies are included.

---

### Solution 3: Date Range Placeholders

**Approach**: Date range presets with configurable defaults

```php
class DateRangePresets {
    public static function getPreset($presetKey) {
        $today = date('Y-m-d');

        switch ($presetKey) {
            case 'current_month':
                return [
                    'from' => date('Y-m-01'),           // First day of month
                    'to' => date('Y-m-t')              // Last day of month
                ];
            case 'last_30_days':
                return [
                    'from' => date('Y-m-d', strtotime('-30 days')),
                    'to' => $today
                ];
            case 'current_year':
                return [
                    'from' => date('Y-01-01'),
                    'to' => date('Y-12-31')
                ];
            case 'last_year':
                return [
                    'from' => date('Y-01-01', strtotime('-1 year')),
                    'to' => date('Y-12-31', strtotime('-1 year'))
                ];
            case 'all_time':
                return [
                    'from' => '1900-01-01',
                    'to' => '2099-12-31'
                ];
            case 'today':
                return [
                    'from' => $today,
                    'to' => $today
                ];
            default:
                return null; // Block if no preset
        }
    }
}
```

**Filter Configuration**:

```json
{
    "filter_type": "daterange",
    "empty_preset": "current_month",
    "custom_from": null,
    "custom_to": null
}
```

---

### Solution 4, 5, 6: Subqueries, JOINs, UNIONs

**Approach**: Query Parser + Table Registry Validation

```php
class CompanyFilterValidator {

    /**
     * Extract all table names from SQL query
     * Handles: FROM, JOIN, subqueries
     */
    public static function extractTableReferences($query) {
        $tables = [];

        // Remove string literals to avoid false matches
        $cleanQuery = preg_replace("/'[^']*'/", "''", $query);
        $cleanQuery = preg_replace('/"[^"]*"/', '""', $cleanQuery);

        // Match FROM table patterns
        preg_match_all('/\bFROM\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/i', $cleanQuery, $fromMatches);
        $tables = array_merge($tables, $fromMatches[1]);

        // Match JOIN table patterns
        preg_match_all('/\bJOIN\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/i', $cleanQuery, $joinMatches);
        $tables = array_merge($tables, $joinMatches[1]);

        // Normalize and deduplicate
        return array_unique(array_map('strtolower', $tables));
    }

    /**
     * Check if table has company_id filter in query
     */
    public static function tableHasCompanyFilter($query, $tableName, $columnName = 'company_id') {
        $patterns = [
            // With table name or alias prefix
            '/\b' . preg_quote($tableName, '/') . '\.' . preg_quote($columnName, '/') . '\s*(=|IN\s*\()\s*::/i',
            // Common alias patterns
            '/\b[a-z]\.' . preg_quote($columnName, '/') . '\s*(=|IN\s*\()\s*::/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }

        // For simple queries without alias
        if (preg_match('/\b' . preg_quote($columnName, '/') . '\s*(=|IN\s*\()\s*::/i', $query)) {
            return true;
        }

        return false;
    }

    /**
     * Validate that all company-filtered tables have the required filter
     */
    public static function validateCompanyFilters($query) {
        $companyTables = TableSchemaManager::getTablesWithCompanyId();
        $referencedTables = self::extractTableReferences($query);
        $missing = [];

        foreach ($referencedTables as $table) {
            if (isset($companyTables[$table])) {
                $column = $companyTables[$table];
                if (!self::tableHasCompanyFilter($query, $table, $column)) {
                    $missing[$table] = $column;
                }
            }
        }

        return [
            'valid' => empty($missing),
            'missing' => $missing,
            'message' => empty($missing) ? null :
                'Missing company filter for tables: ' . implode(', ', array_keys($missing))
        ];
    }
}
```

**Best Practice for JOINs**:

```sql
-- INSECURE: Filter only in WHERE
SELECT o.*, c.name
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
WHERE o.company_id = ::company_id

-- SECURE: Filter in JOIN condition
SELECT o.*, c.name
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
    AND c.company_id = ::company_id
WHERE o.company_id = ::company_id

-- SECURE: Filter both in WHERE
SELECT o.*, c.name
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
WHERE o.company_id = ::company_id
  AND c.company_id = ::company_id
```

---

### Solution 7: Multi-Select Filters

**Approach**: Configurable behavior per filter

```php
// Filter config
{
    "filter_type": "multiselect",
    "empty_behavior": "use_all_from_query",  // or "show_none", "use_default"
    "empty_default": null
}

// In QueryHelper
if ($filterType === 'multiselect' && self::isValueEmpty($value)) {
    switch ($emptyBehavior) {
        case 'use_all_from_query':
            $allOptions = $filter->getOptions();
            $value = array_column($allOptions, 'value');
            break;
        case 'show_none':
            return "0 = 1"; // Always false - no results
        case 'use_default':
            $value = $filter->getEmptyDefault();
            break;
    }
}
```

---

### Solution 8: Text Search Filters

**Approach**: Skip condition when empty (match everything)

```sql
-- Option 1: Use OR condition in SQL
WHERE (::search = '' OR order_notes LIKE CONCAT('%', ::search, '%'))

-- Option 2: PHP-side condition building
if (!empty($searchValue)) {
    $conditions[] = "order_notes LIKE CONCAT('%', " . $db->quote($searchValue) . ", '%')";
}
```

---

## Auto-Detecting Tables with company_id

Instead of manually maintaining a registry, auto-detect from database schema:

```php
class TableSchemaManager {

    private static $tablesWithCompanyId = null;

    /**
     * Get all tables that have company_id column
     */
    public static function getTablesWithCompanyId($forceRefresh = false) {
        if (self::$tablesWithCompanyId !== null && !$forceRefresh) {
            return self::$tablesWithCompanyId;
        }

        $db = Rapidkart::getInstance()->getDB();

        // MySQL: Query information_schema
        $sql = "SELECT TABLE_NAME, COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND COLUMN_NAME = 'company_id'";

        $res = $db->query($sql);
        self::$tablesWithCompanyId = [];

        while ($row = $db->fetchAssocArray($res)) {
            self::$tablesWithCompanyId[strtolower($row['TABLE_NAME'])] = $row['COLUMN_NAME'];
        }

        return self::$tablesWithCompanyId;
    }

    /**
     * Get tables requiring filter (exclude exempt tables)
     */
    public static function getTablesRequiringCompanyFilter() {
        $allTables = self::getTablesWithCompanyId();

        // Some tables have company_id but don't require filtering
        $exemptTables = ['dgc_audit_log', 'dgc_system_cache'];

        return array_diff_key($allTables, array_flip($exemptTables));
    }
}
```

---

## Database Schema Changes

### New Table: Company Filtered Tables Registry (Optional)

```sql
CREATE TABLE dgc_company_filtered_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    column_name VARCHAR(100) DEFAULT 'company_id',
    is_required TINYINT(1) DEFAULT 1,
    is_exempt TINYINT(1) DEFAULT 0,
    created_ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_table (table_name)
) ENGINE=InnoDB;
```

### DataFilter Table: Add Empty Behavior Columns

```sql
-- Add empty behavior configuration
ALTER TABLE dgc_data_filter ADD COLUMN empty_behavior VARCHAR(50) DEFAULT 'use_all_from_query';
-- Values: 'use_all_from_query', 'use_logged_in_value', 'use_default', 'block'

ALTER TABLE dgc_data_filter ADD COLUMN empty_default TEXT DEFAULT NULL;
-- JSON or simple value for default when empty

ALTER TABLE dgc_data_filter ADD COLUMN is_security_filter TINYINT(1) DEFAULT 0;
-- Mark filters that MUST have values (like company_id)

ALTER TABLE dgc_data_filter ADD COLUMN date_preset VARCHAR(50) DEFAULT NULL;
-- For date range filters: 'current_month', 'last_30_days', etc.
```

---

## Implementation Summary

### By Filter Type

| Filter Type | Empty Behavior | Default Action |
|-------------|----------------|----------------|
| **Security (company_id)** | `use_all_from_query` or `use_logged_in_value` | Never bypass - always filter |
| **Single Select** | `use_all_from_query` | Get all options from filter query |
| **Multi Select** | `use_all_from_query` | Get all options from filter query |
| **Text/Search** | `skip_condition` | Don't apply filter (match all) |
| **Date Range** | `use_preset` | Use configured preset (current_month, etc.) |
| **Numeric Range** | `skip_condition` | Don't apply filter (show all) |

### Validation Flow

```
╔══════════════════════════════════════════════════════════════╗
║                    QUERY SAVE TIME                           ║
╠══════════════════════════════════════════════════════════════╣
║ 1. validateQuerySecurity()                                    ║
║    └─ Check SELECT only, no dangerous keywords               ║
║                                                               ║
║ 2. CompanyFilterValidator::validateCompanyFilters()           ║
║    ├─ Extract all table references (FROM, JOIN)              ║
║    ├─ Check each table against registry                       ║
║    └─ WARN if company filter missing for any table           ║
║                                                               ║
║ 3. validateMandatoryFiltersInQuery()                          ║
║    └─ Check mandatory filters present                         ║
║                                                               ║
║ 4. SAVE (with warnings displayed)                             ║
╚══════════════════════════════════════════════════════════════╝

╔══════════════════════════════════════════════════════════════╗
║                   QUERY EXECUTION TIME                        ║
╠══════════════════════════════════════════════════════════════╣
║ 1. SecurityFilterManager::injectSecurityValues()              ║
║    └─ Auto-fill security filter values from session          ║
║                                                               ║
║ 2. For each empty filter:                                     ║
║    ├─ Get emptyBehavior setting                               ║
║    └─ Apply: use_all_from_query / use_default / block        ║
║                                                               ║
║ 3. replaceQueryPlaceholders()                                 ║
║    └─ Replace placeholders with actual escaped values        ║
║                                                               ║
║ 4. EXECUTE QUERY                                              ║
╚══════════════════════════════════════════════════════════════╝
```

---

## Security Filter Auto-Injection (Optional Enhancement)

```php
class SecurityFilterManager {

    /**
     * Auto-inject security filter values before query execution
     */
    public static function injectSecurityValues(&$filterValues) {
        $securityFilters = DataFilterManager::getSecurityFilters();

        foreach ($securityFilters as $filter) {
            $placeholder = '::' . $filter->getFilterKey();

            // If not provided or empty, inject logged-in user's value
            if (!isset($filterValues[$placeholder]) ||
                self::isValueEmpty($filterValues[$placeholder])) {

                $source = $filter->getAutoValueSource();
                switch ($source) {
                    case 'logged_in_company_id':
                        $filterValues[$placeholder] = $_SESSION['company_id'];
                        break;
                    case 'logged_in_licence_companies':
                        $filterValues[$placeholder] = LicenceManager::getCompanyIds($_SESSION['licence_id']);
                        break;
                }
            }
        }

        return $filterValues;
    }
}
```

---

## Questions for Team Discussion

### 1. Should empty on `::company_id` ever be allowed?

| Option | Description | Pros | Cons |
|--------|-------------|------|------|
| **A** | No, always require selection | Most secure, forces user choice | Poor UX for "all companies" |
| **B** | Yes, default to user's accessible companies | Good UX, still secure | Requires extra query |
| **C** | Use logged-in user's value only | Simple, very secure | May be too restrictive |

**Recommendation**: Option B with `use_all_from_query`

---

### 2. For multi-select filters, what does "empty" mean?

| Option | Description | Use Case |
|--------|-------------|----------|
| **A** | All options | Show everything matching any value |
| **B** | No options | Show nothing (require selection) |
| **C** | Configurable | Per-filter setting |

**Recommendation**: Option C with default `use_all_from_query`

---

### 3. Should we parse and validate complex queries for company_id?

| Option | Description | Pros | Cons |
|--------|-------------|------|------|
| **A** | Yes, hard error | Prevents all data leaks | May block valid queries |
| **B** | Yes, warning only | Alerts admin, allows override | Admin may ignore |
| **C** | No, trust developer | Simplest implementation | Security risk |

**Recommendation**: Option B - Warn but allow with acknowledgment

---

### 4. How to handle date range empty values?

| Option | Description | Pros | Cons |
|--------|-------------|------|------|
| **A** | Per-filter presets | Flexible, context-appropriate | Configuration overhead |
| **B** | Global default | Simple, consistent | May not fit all use cases |
| **C** | Always require | Most explicit | Poor UX |

**Recommendation**: Option A with sensible defaults

---

### 5. Should we create database views for data isolation?

| Aspect | Details |
|--------|---------|
| **Pros** | Strongest security, no code changes needed |
| **Cons** | DB complexity, maintenance overhead, can't pass dynamic company_id easily |

**Recommendation**: Not recommended for DGC - better to handle in application layer

---

### 6. Performance vs Security trade-off?

Using `use_all_from_query` means executing the filter query when empty.

| Concern | Mitigation |
|---------|------------|
| Extra query | Cache filter options during session |
| Large IN lists | Use subquery instead of IN for 100+ values |
| Frequent calls | Eager load common filters |

**Recommendation**: Acceptable overhead for security benefit

---

## Decision Matrix

| Question | Option A | Option B | Option C | **Recommended** |
|----------|----------|----------|----------|-----------------|
| Detect company_id tables | Manual registry | Auto-detect from DB | Hybrid (auto + exemptions) | **Option C** |
| Empty company filter | Block execution | Use all from filter query | Use logged-in value | **Option B** |
| Empty multi-select | Show all | Show none | Configurable | **Option C** |
| Empty date range | Block | Use preset | Configurable preset | **Option C** |
| Query validation | Hard error | Warning only | Configurable | **Option B** |

---

## Next Steps After Team Discussion

1. ✅ Finalize approach for security filters (company_id)
2. ✅ Define per-placeholder empty behavior options
3. ✅ Decide on date range handling
4. ✅ Determine if query validation is needed
5. ⬜ Create detailed implementation plan based on decisions
6. ⬜ Prioritize and estimate implementation effort
7. ⬜ Begin implementation

---

## Appendix: Code Changes Summary

### Files to Create

| File | Purpose |
|------|---------|
| `system/classes/CompanyFilterValidator.php` | Query validation for company filters |
| `system/classes/TableSchemaManager.php` | Auto-detect tables with company_id |
| `system/classes/DateRangePresets.php` | Date range default presets |
| `system/classes/SecurityFilterManager.php` | Auto-inject security filter values |

### Files to Modify

| File | Changes |
|------|---------|
| `system/classes/QueryHelper.php` | Add empty behavior handling, integrate validators |
| `system/classes/DataFilter.php` | Add empty behavior getters/setters |
| `system/classes/DataFilterManager.php` | Update placeholder replacement logic |
| `migrate.php` | Add database schema changes |

---

*Document Version: 1.0*
*Created: January 2026*
*For Team Discussion*
