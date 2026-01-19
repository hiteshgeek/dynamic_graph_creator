-- ============================================================================
-- Dynamic Graph Creator - Complete Installation Script
-- Compatible with MySQL 5.6.33+
-- ============================================================================
-- This script creates all required tables and inserts system data
-- Run this on a fresh database installation
-- ============================================================================

-- ============================================================================
-- CORE TABLES
-- ============================================================================

-- Graph table
CREATE TABLE IF NOT EXISTS graph (
    gid INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    graph_type ENUM('bar', 'line', 'pie') NOT NULL DEFAULT 'bar',
    config TEXT NOT NULL,
    query TEXT NOT NULL,
    data_mapping TEXT NOT NULL,
    gsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL,
    updated_uid INT(11) DEFAULT NULL,
    INDEX idx_gsid (gsid),
    INDEX idx_graph_type (graph_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores graph definitions';

-- DataFilter table (standalone reusable filters)
CREATE TABLE IF NOT EXISTS data_filter (
    dfid INT(11) AUTO_INCREMENT PRIMARY KEY,
    filter_key VARCHAR(50) NOT NULL COMMENT 'Placeholder key e.g. :date_from',
    filter_label VARCHAR(100) NOT NULL COMMENT 'Display label',
    filter_type ENUM('text', 'number', 'date', 'date_range', 'select', 'multi_select', 'checkbox', 'radio', 'tokeninput') NOT NULL DEFAULT 'text',
    data_source ENUM('static', 'query') NOT NULL DEFAULT 'static' COMMENT 'How to get filter options',
    data_query TEXT COMMENT 'SQL query to fetch options (if data_source=query)',
    static_options TEXT COMMENT 'JSON array of static options [{value, label}]',
    filter_config TEXT COMMENT 'JSON config options like {inline: true}',
    default_value VARCHAR(255) DEFAULT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    dfsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dfsid (dfsid),
    INDEX idx_filter_key (filter_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Standalone filter definitions';

-- ============================================================================
-- DASHBOARD TABLES
-- ============================================================================

-- Drop existing tables if they exist (in correct order due to foreign keys)
DROP TABLE IF EXISTS dashboard_instance;
DROP TABLE IF EXISTS dashboard_template;
DROP TABLE IF EXISTS dashboard_template_category;

-- Dashboard Template Category Table
CREATE TABLE dashboard_template_category (
    dtcid INT(11) AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE COMMENT 'URL-friendly identifier (e.g., "columns", "advanced")',
    name VARCHAR(100) NOT NULL COMMENT 'Display name (e.g., "Columns", "Advanced")',
    description TEXT COMMENT 'Category description',
    icon VARCHAR(50) DEFAULT NULL COMMENT 'Font Awesome icon class (e.g., "fa-columns")',
    color VARCHAR(20) DEFAULT NULL COMMENT 'Category color for UI (e.g., "#007bff")',
    display_order INT(11) DEFAULT 0 COMMENT 'Display order (lower numbers first)',
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=system category (protected), 0=custom category',
    dtcsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_display_order (display_order),
    INDEX idx_dtcsid (dtcsid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dashboard template categories';

-- Dashboard Template Table (System templates and user-created templates)
CREATE TABLE dashboard_template (
    dtid INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Template name',
    description TEXT COMMENT 'Template description',
    display_order INT(11) DEFAULT 0 COMMENT 'Display order (lower numbers first)',
    dtcid INT(11) DEFAULT NULL COMMENT 'Category ID (foreign key to dashboard_template_category)',
    thumbnail VARCHAR(255) DEFAULT NULL COMMENT 'Preview image path',
    structure TEXT NOT NULL COMMENT 'JSON dashboard structure',
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=system template (protected), 0=user template',
    dtsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL COMMENT 'User who created',
    updated_uid INT(11) DEFAULT NULL COMMENT 'User who last updated',
    INDEX idx_dtcid (dtcid),
    INDEX idx_dtsid (dtsid),
    INDEX idx_is_system (is_system),
    FOREIGN KEY (dtcid) REFERENCES dashboard_template_category(dtcid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dashboard templates';

-- Dashboard Instance Table (User dashboards created from templates)
CREATE TABLE dashboard_instance (
    diid INT(11) AUTO_INCREMENT PRIMARY KEY,
    dtid INT(11) DEFAULT NULL COMMENT 'Source template ID (nullable)',
    name VARCHAR(255) NOT NULL COMMENT 'Dashboard instance name',
    description TEXT COMMENT 'Dashboard description',
    structure TEXT NOT NULL COMMENT 'JSON dashboard structure with content',
    config TEXT COMMENT 'JSON configuration (responsive breakpoints, etc)',
    company_id INT(11) DEFAULT NULL COMMENT 'Company association for multi-tenant',
    user_id INT(11) DEFAULT NULL COMMENT 'User who owns this dashboard',
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System dashboards cannot be deleted',
    disid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL COMMENT 'User who created',
    updated_uid INT(11) DEFAULT NULL COMMENT 'User who last updated',
    INDEX idx_dtid (dtid),
    INDEX idx_company_id (company_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_system (is_system),
    INDEX idx_disid (disid),
    FOREIGN KEY (dtid) REFERENCES dashboard_template(dtid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User dashboard instances';

-- ============================================================================
-- SYSTEM DATA - Template Categories (4 total)
-- ============================================================================

INSERT INTO dashboard_template_category (slug, name, description, icon, color, display_order, is_system, dtcsid) VALUES
('columns', 'Columns', 'Simple column-based dashboards with equal or varied widths', 'fa-columns', '#007bff', 10, 0, 1),
('rows', 'Rows', 'Row-based dashboards with stacked sections', 'fa-bars', '#d63384', 20, 0, 1),
('mixed', 'Mixed', 'Mixed dashboards with sidebars and unequal column ratios', 'fa-table-columns', '#6610f2', 30, 0, 1),
('advanced', 'Advanced', 'Complex multi-section dashboards with nested areas', 'fa-th', '#6f42c1', 40, 0, 1);

-- ============================================================================
-- SYSTEM DATA - Dashboard Templates
-- ============================================================================

-- Get category IDs for templates
SET @cat_columns = (SELECT dtcid FROM dashboard_template_category WHERE slug = 'columns');
SET @cat_mixed = (SELECT dtcid FROM dashboard_template_category WHERE slug = 'mixed');
SET @cat_advanced = (SELECT dtcid FROM dashboard_template_category WHERE slug = 'advanced');
SET @cat_rows = (SELECT dtcid FROM dashboard_template_category WHERE slug = 'rows');

-- ============================================================================
-- COLUMNS CATEGORY (1, 2, 4, 6 columns)
-- ============================================================================

-- 1. Single Column Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Single Column',
    'Simple single column dashboard',
    @cat_columns,
    10,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Add content here"}}]}]}',
    0, 1
);

-- 2. Two Columns Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Two Columns',
    'Two equal columns side by side',
    @cat_columns,
    20,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Column 1"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-pie","message":"Column 2"}}]}]}',
    0, 1
);

-- 3. Four Columns Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Four Columns',
    'Four equal columns for KPIs',
    @cat_columns,
    30,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr 1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-dollar-sign","message":"KPI 1"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-users","message":"KPI 2"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-shopping-cart","message":"KPI 3"}},{"aid":"a4","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"KPI 4"}}]}]}',
    0, 1
);

-- 4. Six Columns Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Six Columns',
    'Six equal columns for detailed metrics',
    @cat_columns,
    40,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr 1fr 1fr 1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"Metric 1"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Metric 2"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-pie","message":"Metric 3"}},{"aid":"a4","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-area","message":"Metric 4"}},{"aid":"a5","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-dollar-sign","message":"Metric 5"}},{"aid":"a6","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-users","message":"Metric 6"}}]}]}',
    0, 1
);

-- ============================================================================
-- ROWS CATEGORY (2, 3, 4 rows)
-- ============================================================================

-- 5. Two Rows Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Two Rows',
    'Two stacked rows',
    @cat_rows,
    10,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Row 1"}}]},{"sid":"s2","gridTemplate":"1fr","areas":[{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-table","message":"Row 2"}}]}]}',
    0, 1
);

-- 6. Three Rows Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Three Rows',
    'Three stacked rows',
    @cat_rows,
    20,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-heading","message":"Row 1"}}]},{"sid":"s2","gridTemplate":"1fr","areas":[{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"Row 2"}}]},{"sid":"s3","gridTemplate":"1fr","areas":[{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-table","message":"Row 3"}}]}]}',
    0, 1
);

-- 7. Four Rows Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Four Rows',
    'Four stacked rows',
    @cat_rows,
    30,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-heading","message":"Row 1"}}]},{"sid":"s2","gridTemplate":"1fr","areas":[{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Row 2"}}]},{"sid":"s3","gridTemplate":"1fr","areas":[{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"Row 3"}}]},{"sid":"s4","gridTemplate":"1fr","areas":[{"aid":"a4","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-table","message":"Row 4"}}]}]}',
    0, 1
);

-- ============================================================================
-- MIXED CATEGORY (sidebars and unequal ratios)
-- ============================================================================

-- 8. Left Sidebar Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Left Sidebar',
    'Narrow left sidebar with main content area',
    @cat_mixed,
    10,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 3fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-bars","message":"Sidebar"}},{"aid":"a2","colSpanFr":"3fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Main Content"}}]}]}',
    0, 1
);

-- 9. Right Sidebar Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Right Sidebar',
    'Main content with narrow right sidebar',
    @cat_mixed,
    20,
    '{"sections":[{"sid":"s1","gridTemplate":"3fr 1fr","areas":[{"aid":"a1","colSpanFr":"3fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-area","message":"Main Content"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-info-circle","message":"Info Panel"}}]}]}',
    0, 1
);

-- 10. Holy Grail Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Holy Grail',
    'Classic three-column dashboard with navigation, content, and tools',
    @cat_mixed,
    30,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 2fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-list","message":"Left Nav"}},{"aid":"a2","colSpanFr":"2fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"Main Content"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-cog","message":"Tools"}}]}]}',
    0, 1
);

-- ============================================================================
-- ADVANCED CATEGORY (complex multi-section dashboards)
-- ============================================================================

-- 11. Header + Two Columns Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Header + Two Columns',
    'Full-width header with two columns below',
    @cat_advanced,
    10,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-heading","message":"Header / Title"}}]},{"sid":"s2","gridTemplate":"1fr 1fr","areas":[{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Chart 1"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-pie","message":"Chart 2"}}]}]}',
    0, 1
);

-- 12. Dashboard Template (KPIs + Charts)
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Dashboard',
    'Complete dashboard with KPIs, main chart, and secondary charts',
    @cat_advanced,
    20,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr 1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-dollar-sign","message":"Revenue"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-shopping-cart","message":"Orders"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-users","message":"Customers"}},{"aid":"a4","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-percent","message":"Growth"}}]},{"sid":"s2","gridTemplate":"1fr","areas":[{"aid":"a5","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"Main Chart"}}]},{"sid":"s3","gridTemplate":"1fr 1fr","areas":[{"aid":"a6","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Chart"}},{"aid":"a7","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-table","message":"Table"}}]}]}',
    0, 1
);

-- 13. Left Multi-Row + Right Single
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Left Multi-Row + Right Single',
    'Left column with 3 rows, right column with single large area',
    @cat_advanced,
    30,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 2fr","areas":[{"aid":"a1","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r1","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"Add chart here"}},{"rowId":"r2","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-table","message":"Add table here"}},{"rowId":"r3","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Add chart here"}}]},{"aid":"a2","colSpanFr":"2fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-area","message":"Add main chart here"}}]}]}',
    0, 1
);

-- 14. Right Multi-Row + Left Single
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Right Multi-Row + Left Single',
    'Left column with single large area, right column with 3 rows',
    @cat_advanced,
    40,
    '{"sections":[{"sid":"s1","gridTemplate":"2fr 1fr","areas":[{"aid":"a1","colSpanFr":"2fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-area","message":"Add main chart here"}},{"aid":"a2","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r1","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"Add chart here"}},{"rowId":"r2","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-table","message":"Add table here"}},{"rowId":"r3","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Add chart here"}}]}]}]}',
    0, 1
);

-- 15. Two Multi-Row Columns
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Two Multi-Row Columns',
    'Two columns, each with 2 rows',
    @cat_advanced,
    50,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r1","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"Top chart"}},{"rowId":"r2","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-table","message":"Bottom table"}}]},{"aid":"a2","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r3","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-bar","message":"Top chart"}},{"rowId":"r4","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-pie","message":"Bottom chart"}}]}]}]}',
    0, 1
);

-- 16. Focal Point with Multi-Row
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Focal Point with Multi-Row',
    'Large left area with right column split into 3 rows',
    @cat_advanced,
    60,
    '{"sections":[{"sid":"s1","gridTemplate":"2fr 1fr","areas":[{"aid":"a1","colSpanFr":"2fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-area","message":"Featured Content"}},{"aid":"a2","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r1","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-dollar-sign","message":"KPI 1"}},{"rowId":"r2","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-users","message":"KPI 2"}},{"rowId":"r3","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-chart-line","message":"KPI 3"}}]}]}]}',
    0, 1
);

-- ============================================================================
-- Installation Complete
-- ============================================================================
-- Tables created:
--   - graph (graph definitions)
--   - data_filter (reusable filter definitions)
--   - dashboard_template_category (4 system categories)
--   - dashboard_template (16 system templates)
--   - dashboard_instance (user dashboards)
--
-- Categories (in display order):
--   1. Columns: Single Column, Two Columns, Four Columns, Six Columns
--   2. Rows: Two Rows, Three Rows, Four Rows
--   3. Mixed: Left Sidebar, Right Sidebar, Holy Grail
--   4. Advanced: Header + Two Columns, Dashboard, Left Multi-Row + Right Single,
--                Right Multi-Row + Left Single, Two Multi-Row Columns,
--                Focal Point with Multi-Row
-- ============================================================================
