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
    placeholder_settings TEXT COMMENT 'JSON settings for placeholder behavior (allowEmpty per placeholder)',
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
    filter_type ENUM('text', 'number', 'date', 'date_range', 'main_datepicker', 'select', 'multi_select', 'checkbox', 'radio', 'tokeninput') NOT NULL DEFAULT 'text',
    data_source ENUM('static', 'query') NOT NULL DEFAULT 'static' COMMENT 'How to get filter options',
    data_query TEXT COMMENT 'SQL query to fetch options (if data_source=query)',
    static_options TEXT COMMENT 'JSON array of static options [{value, label}]',
    filter_config TEXT COMMENT 'JSON config options like {inline: true}',
    default_value VARCHAR(255) DEFAULT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System filter - only editable by admin',
    dfsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dfsid (dfsid),
    INDEX idx_filter_key (filter_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Standalone filter definitions';

-- System Placeholder table (special placeholders resolved at runtime)
CREATE TABLE IF NOT EXISTS system_placeholder (
    spid INT(11) AUTO_INCREMENT PRIMARY KEY,
    placeholder_key VARCHAR(50) NOT NULL UNIQUE COMMENT 'Key without :: prefix, e.g. logged_in_uid',
    placeholder_label VARCHAR(100) NOT NULL COMMENT 'Display label for UI',
    description TEXT COMMENT 'Description of what this placeholder returns',
    resolver_method VARCHAR(100) NOT NULL COMMENT 'Static method name in SystemPlaceholderManager',
    spsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_spsid (spsid),
    INDEX idx_placeholder_key (placeholder_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='System-level placeholders resolved at runtime';

-- Default System Placeholders
INSERT INTO system_placeholder (placeholder_key, placeholder_label, description, resolver_method) VALUES
('logged_in_uid', 'Logged In User ID', 'Returns the currently logged in user ID', 'getLoggedInUid'),
('logged_in_company_id', 'Logged In Company ID', 'Returns the company ID of the logged in user', 'getLoggedInCompanyId'),
('logged_in_licence_id', 'Logged In Licence ID', 'Returns the licence ID of the logged in user', 'getLoggedInLicenceId'),
('logged_in_is_admin', 'Logged In Is Admin', 'Returns 1 if the logged in user is an admin, 0 otherwise', 'getLoggedInIsAdmin');

-- ============================================================================
-- WIDGET CATEGORY TABLES
-- ============================================================================

-- Widget Category table (categories for dashboard widgets like graphs)
CREATE TABLE IF NOT EXISTS widget_category (
    wcid INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Category name (e.g., Sales, Finance)',
    description TEXT COMMENT 'Category description',
    icon VARCHAR(50) DEFAULT NULL COMMENT 'Font Awesome icon class (e.g., fa-dollar-sign)',
    color VARCHAR(20) DEFAULT NULL COMMENT 'Category color for UI (e.g., #28a745)',
    display_order INT(11) DEFAULT 0 COMMENT 'Display order (lower numbers first)',
    wcsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_wcsid (wcsid),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Categories for dashboard widgets';

-- Insert default categories
INSERT INTO widget_category (name, description, icon, color, display_order) VALUES
('Sales', 'Sales and revenue related graphs', 'fa-dollar-sign', '#28a745', 10),
('Purchase', 'Purchase and procurement related graphs', 'fa-shopping-cart', '#007bff', 20);

-- Graph-Widget Category Mapping table
CREATE TABLE IF NOT EXISTS graph_widget_category_mapping (
    gwcmid INT(11) AUTO_INCREMENT PRIMARY KEY,
    gid INT(11) NOT NULL COMMENT 'Graph ID (foreign key to graph)',
    wcid INT(11) NOT NULL COMMENT 'Category ID (foreign key to widget_category)',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gid (gid),
    INDEX idx_wcid (wcid),
    UNIQUE KEY unique_graph_widget_category (gid, wcid),
    FOREIGN KEY (gid) REFERENCES graph(gid) ON DELETE CASCADE,
    FOREIGN KEY (wcid) REFERENCES widget_category(wcid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maps graphs to widget categories';

-- ============================================================================
-- COUNTER TABLES
-- ============================================================================

-- Counter table (KPI/metric counters)
CREATE TABLE IF NOT EXISTS counter (
    cid INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    config TEXT NOT NULL COMMENT 'JSON config: icon, color, format, prefix, suffix, decimals',
    query TEXT NOT NULL COMMENT 'SQL query that returns single record with "counter" key',
    data_mapping TEXT COMMENT 'JSON data mapping (not used for counter, kept for Element base class compatibility)',
    placeholder_settings TEXT COMMENT 'JSON settings for placeholder behavior (allowEmpty per placeholder)',
    snapshot TEXT COMMENT 'Base64 encoded preview image',
    csid TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL,
    updated_uid INT(11) DEFAULT NULL,
    INDEX idx_csid (csid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores counter/KPI definitions';

-- Counter-Widget Category Mapping table
CREATE TABLE IF NOT EXISTS counter_widget_category_mapping (
    cwcmid INT(11) AUTO_INCREMENT PRIMARY KEY,
    cid INT(11) NOT NULL COMMENT 'Counter ID (foreign key to counter)',
    wcid INT(11) NOT NULL COMMENT 'Category ID (foreign key to widget_category)',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cid (cid),
    INDEX idx_wcid (wcid),
    UNIQUE KEY unique_counter_widget_category (cid, wcid),
    FOREIGN KEY (cid) REFERENCES counter(cid) ON DELETE CASCADE,
    FOREIGN KEY (wcid) REFERENCES widget_category(wcid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maps counters to widget categories';

-- ============================================================================
-- TABLE WIDGET TABLES
-- ============================================================================

-- Table widget (data table widgets)
CREATE TABLE IF NOT EXISTS dgc_table (
    tid INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    config TEXT NOT NULL COMMENT 'JSON config: columns, pagination, style settings',
    query TEXT NOT NULL COMMENT 'SQL query that returns multiple rows for table display',
    data_mapping TEXT COMMENT 'JSON data mapping (kept for Element base class compatibility)',
    placeholder_settings TEXT COMMENT 'JSON settings for placeholder behavior (allowEmpty per placeholder)',
    snapshot TEXT COMMENT 'Base64 encoded preview image',
    tsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL,
    updated_uid INT(11) DEFAULT NULL,
    INDEX idx_tsid (tsid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores data table widget definitions';

-- Table-Widget Category Mapping table
CREATE TABLE IF NOT EXISTS table_widget_category_mapping (
    twcmid INT(11) AUTO_INCREMENT PRIMARY KEY,
    tid INT(11) NOT NULL COMMENT 'Table ID (foreign key to dgc_table)',
    wcid INT(11) NOT NULL COMMENT 'Category ID (foreign key to widget_category)',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tid (tid),
    INDEX idx_wcid (wcid),
    UNIQUE KEY unique_table_widget_category (tid, wcid),
    FOREIGN KEY (tid) REFERENCES dgc_table(tid) ON DELETE CASCADE,
    FOREIGN KEY (wcid) REFERENCES widget_category(wcid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maps tables to widget categories';

-- ============================================================================
-- WIDGET TYPE TABLES
-- ============================================================================

-- Widget Type table (types of widgets: graph, link, table, list, counter)
CREATE TABLE IF NOT EXISTS widget_type (
    wtid INT(11) AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE COMMENT 'Type identifier (graph, link, table, list, counter)',
    name VARCHAR(100) NOT NULL COMMENT 'Display name',
    description TEXT COMMENT 'Type description',
    icon VARCHAR(50) DEFAULT NULL COMMENT 'Font Awesome icon class',
    display_order INT(11) DEFAULT 0 COMMENT 'Display order (lower numbers first)',
    wtsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_wtsid (wtsid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Widget types for dashboard elements';

-- Insert default widget types
INSERT INTO widget_type (slug, name, description, icon, display_order) VALUES
('graph', 'Graph', 'Chart/Graph widgets', 'fa-chart-bar', 10),
('link', 'Link', 'Link/URL widgets', 'fa-link', 20),
('table', 'Table', 'Data table widgets', 'fa-table', 30),
('list', 'List', 'List display widgets', 'fa-list', 40),
('counter', 'Counter', 'Counter/KPI widgets', 'fa-hashtag', 50);

-- Filter-Widget Type Mandatory mapping table
CREATE TABLE IF NOT EXISTS filter_widget_type_mandatory (
    fwtmid INT(11) AUTO_INCREMENT PRIMARY KEY,
    dfid INT(11) NOT NULL COMMENT 'Filter ID (FK to data_filter)',
    wtid INT(11) NOT NULL COMMENT 'Widget Type ID (FK to widget_type)',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dfid (dfid),
    INDEX idx_wtid (wtid),
    UNIQUE KEY unique_filter_widget_type (dfid, wtid),
    FOREIGN KEY (dfid) REFERENCES data_filter(dfid) ON DELETE CASCADE,
    FOREIGN KEY (wtid) REFERENCES widget_type(wtid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maps filters to widget types where they are mandatory';

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
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System dashboards cannot be deleted',
    disid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL COMMENT 'User who created',
    updated_uid INT(11) DEFAULT NULL COMMENT 'User who last updated',
    INDEX idx_dtid (dtid),
    INDEX idx_company_id (company_id),
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
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 2. Two Columns Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Two Columns',
    'Two equal columns side by side',
    @cat_columns,
    20,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 3. Four Columns Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Four Columns',
    'Four equal columns for KPIs',
    @cat_columns,
    30,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr 1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a4","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 4. Six Columns Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Six Columns',
    'Six equal columns for detailed metrics',
    @cat_columns,
    40,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr 1fr 1fr 1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a4","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a5","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a6","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
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
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"sid":"s2","gridTemplate":"1fr","areas":[{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 6. Three Rows Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Three Rows',
    'Three stacked rows',
    @cat_rows,
    20,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"sid":"s2","gridTemplate":"1fr","areas":[{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"sid":"s3","gridTemplate":"1fr","areas":[{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 7. Four Rows Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Four Rows',
    'Four stacked rows',
    @cat_rows,
    30,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"sid":"s2","gridTemplate":"1fr","areas":[{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"sid":"s3","gridTemplate":"1fr","areas":[{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"sid":"s4","gridTemplate":"1fr","areas":[{"aid":"a4","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
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
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 3fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a2","colSpanFr":"3fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 9. Right Sidebar Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Right Sidebar',
    'Main content with narrow right sidebar',
    @cat_mixed,
    20,
    '{"sections":[{"sid":"s1","gridTemplate":"3fr 1fr","areas":[{"aid":"a1","colSpanFr":"3fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 10. Holy Grail Template
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Holy Grail',
    'Classic three-column dashboard with navigation, content, and tools',
    @cat_mixed,
    30,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 2fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a2","colSpanFr":"2fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
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
    '{"sections":[{"sid":"s1","gridTemplate":"1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"sid":"s2","gridTemplate":"1fr 1fr","areas":[{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 12. Dashboard Template (KPIs + Charts)
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Dashboard',
    'Complete dashboard with KPIs, main chart, and secondary charts',
    @cat_advanced,
    20,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr 1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a2","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a3","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a4","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"sid":"s2","gridTemplate":"1fr","areas":[{"aid":"a5","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"sid":"s3","gridTemplate":"1fr 1fr","areas":[{"aid":"a6","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a7","colSpanFr":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 13. Left Multi-Row + Right Single
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Left Multi-Row + Right Single',
    'Left column with 3 rows, right column with single large area',
    @cat_advanced,
    30,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 2fr","areas":[{"aid":"a1","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r1","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"rowId":"r2","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"rowId":"r3","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"aid":"a2","colSpanFr":"2fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}',
    0, 1
);

-- 14. Right Multi-Row + Left Single
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Right Multi-Row + Left Single',
    'Left column with single large area, right column with 3 rows',
    @cat_advanced,
    40,
    '{"sections":[{"sid":"s1","gridTemplate":"2fr 1fr","areas":[{"aid":"a1","colSpanFr":"2fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a2","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r1","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"rowId":"r2","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"rowId":"r3","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}]}',
    0, 1
);

-- 15. Two Multi-Row Columns
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Two Multi-Row Columns',
    'Two columns, each with 2 rows',
    @cat_advanced,
    50,
    '{"sections":[{"sid":"s1","gridTemplate":"1fr 1fr","areas":[{"aid":"a1","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r1","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"rowId":"r2","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]},{"aid":"a2","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r3","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"rowId":"r4","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}]}',
    0, 1
);

-- 16. Focal Point with Multi-Row
INSERT INTO dashboard_template (name, description, dtcid, display_order, structure, is_system, dtsid)
VALUES (
    'Focal Point with Multi-Row',
    'Large left area with right column split into 3 rows',
    @cat_advanced,
    60,
    '{"sections":[{"sid":"s1","gridTemplate":"2fr 1fr","areas":[{"aid":"a1","colSpanFr":"2fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"aid":"a2","colSpanFr":"1fr","hasSubRows":true,"subRows":[{"rowId":"r1","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"rowId":"r2","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}},{"rowId":"r3","height":"1fr","content":{"type":"empty"},"emptyState":{"icon":"fa-circle-plus","message":"Add content"}}]}]}]}',
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
