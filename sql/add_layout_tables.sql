-- =================================================================
-- Layout Module Database Tables
-- =================================================================
-- This migration adds layout builder functionality to the system
-- Run this file to create layout_template and layout_instance tables
-- =================================================================

-- Layout templates table (pre-defined layout templates)
CREATE TABLE IF NOT EXISTS layout_template (
    ltid INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Template name',
    description TEXT COMMENT 'Template description',
    category ENUM('columns', 'rows', 'mixed', 'advanced') NOT NULL DEFAULT 'columns' COMMENT 'Template category for grouping',
    thumbnail VARCHAR(255) DEFAULT NULL COMMENT 'Preview image path',
    structure TEXT NOT NULL COMMENT 'JSON layout structure',
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=system template (cannot be deleted), 0=user template',
    ltsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL COMMENT 'User who created',
    updated_uid INT(11) DEFAULT NULL COMMENT 'User who last updated',
    INDEX idx_category (category),
    INDEX idx_ltsid (ltsid),
    INDEX idx_is_system (is_system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dashboard layout templates';

-- Layout instances table (user-created layouts from templates)
CREATE TABLE IF NOT EXISTS layout_instance (
    liid INT(11) AUTO_INCREMENT PRIMARY KEY,
    ltid INT(11) DEFAULT NULL COMMENT 'Source template ID (nullable)',
    name VARCHAR(255) NOT NULL COMMENT 'Layout instance name',
    description TEXT COMMENT 'Layout description',
    structure TEXT NOT NULL COMMENT 'JSON layout structure with content',
    config TEXT COMMENT 'JSON configuration (responsive breakpoints, etc)',
    company_id INT(11) DEFAULT NULL COMMENT 'Company association for multi-tenant',
    user_id INT(11) DEFAULT NULL COMMENT 'User who owns this layout',
    lisid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL COMMENT 'User who created',
    updated_uid INT(11) DEFAULT NULL COMMENT 'User who last updated',
    INDEX idx_ltid (ltid),
    INDEX idx_company_id (company_id),
    INDEX idx_user_id (user_id),
    INDEX idx_lisid (lisid),
    FOREIGN KEY (ltid) REFERENCES layout_template(ltid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User layout instances';

-- =================================================================
-- Insert System Templates
-- =================================================================

-- Single Column Layout
INSERT INTO layout_template (name, description, category, structure, is_system) VALUES
('Single Column', 'Full width single column layout', 'columns',
'{"version":"1.0","responsive":{"mobile":{"enabled":true,"breakpoint":768},"tablet":{"enabled":true,"breakpoint":1024},"desktop":{"enabled":true,"breakpoint":1920}},"sections":[{"sid":"s1","type":"row","height":"auto","heightFr":1,"minHeight":"200px","areas":[{"aid":"a1","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"300px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-bar","message":"Add content here"}}],"gridTemplate":"1fr","gap":"16px"}],"globalConfig":{"gap":"16px","padding":"16px","backgroundColor":"#f5f5f5","minSectionHeight":"200px"}}',
1);

-- Two Equal Columns
INSERT INTO layout_template (name, description, category, structure, is_system) VALUES
('Two Equal Columns', '50/50 split layout', 'columns',
'{"version":"1.0","responsive":{"mobile":{"enabled":true,"breakpoint":768},"tablet":{"enabled":true,"breakpoint":1024},"desktop":{"enabled":true,"breakpoint":1920}},"sections":[{"sid":"s1","type":"row","height":"auto","heightFr":1,"minHeight":"200px","areas":[{"aid":"a1","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"300px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-bar","message":"Chart"}},{"aid":"a2","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"300px","responsive":{"mobile":{"colSpan":1,"order":2},"tablet":{"colSpan":1,"order":2},"desktop":{"colSpan":1,"order":2}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-table","message":"Table"}}],"gridTemplate":"1fr 1fr","gap":"16px"}],"globalConfig":{"gap":"16px","padding":"16px","backgroundColor":"#f5f5f5","minSectionHeight":"200px"}}',
1);

-- Three Equal Columns
INSERT INTO layout_template (name, description, category, structure, is_system) VALUES
('Three Equal Columns', '33/33/33 split layout', 'columns',
'{"version":"1.0","responsive":{"mobile":{"enabled":true,"breakpoint":768},"tablet":{"enabled":true,"breakpoint":1024},"desktop":{"enabled":true,"breakpoint":1920}},"sections":[{"sid":"s1","type":"row","height":"auto","heightFr":1,"minHeight":"200px","areas":[{"aid":"a1","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"250px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-line","message":"Metric 1"}},{"aid":"a2","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"250px","responsive":{"mobile":{"colSpan":1,"order":2},"tablet":{"colSpan":1,"order":2},"desktop":{"colSpan":1,"order":2}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-pie","message":"Metric 2"}},{"aid":"a3","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"250px","responsive":{"mobile":{"colSpan":1,"order":3},"tablet":{"colSpan":1,"order":3},"desktop":{"colSpan":1,"order":3}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-area","message":"Metric 3"}}],"gridTemplate":"1fr 1fr 1fr","gap":"16px"}],"globalConfig":{"gap":"16px","padding":"16px","backgroundColor":"#f5f5f5","minSectionHeight":"200px"}}',
1);

-- Four Equal Columns
INSERT INTO layout_template (name, description, category, structure, is_system) VALUES
('Four Equal Columns', '25/25/25/25 split layout for KPIs', 'columns',
'{"version":"1.0","responsive":{"mobile":{"enabled":true,"breakpoint":768},"tablet":{"enabled":true,"breakpoint":1024},"desktop":{"enabled":true,"breakpoint":1920}},"sections":[{"sid":"s1","type":"row","height":"auto","heightFr":1,"minHeight":"150px","areas":[{"aid":"a1","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"200px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":2,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-dollar-sign","message":"KPI 1"}},{"aid":"a2","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"200px","responsive":{"mobile":{"colSpan":1,"order":2},"tablet":{"colSpan":2,"order":2},"desktop":{"colSpan":1,"order":2}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-users","message":"KPI 2"}},{"aid":"a3","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"200px","responsive":{"mobile":{"colSpan":1,"order":3},"tablet":{"colSpan":2,"order":3},"desktop":{"colSpan":1,"order":3}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-shopping-cart","message":"KPI 3"}},{"aid":"a4","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"200px","responsive":{"mobile":{"colSpan":1,"order":4},"tablet":{"colSpan":2,"order":4},"desktop":{"colSpan":1,"order":4}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-line","message":"KPI 4"}}],"gridTemplate":"1fr 1fr 1fr 1fr","gap":"16px"}],"globalConfig":{"gap":"16px","padding":"16px","backgroundColor":"#f5f5f5","minSectionHeight":"150px"}}',
1);

-- Sidebar Left (25/75)
INSERT INTO layout_template (name, description, category, structure, is_system) VALUES
('Sidebar Left', '25/75 split with left sidebar', 'mixed',
'{"version":"1.0","responsive":{"mobile":{"enabled":true,"breakpoint":768},"tablet":{"enabled":true,"breakpoint":1024},"desktop":{"enabled":true,"breakpoint":1920}},"sections":[{"sid":"s1","type":"row","height":"auto","heightFr":1,"minHeight":"400px","areas":[{"aid":"a1","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"250px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-bars","message":"Sidebar"}},{"aid":"a2","colSpan":1,"colSpanFr":"3fr","rowSpan":1,"minWidth":"500px","responsive":{"mobile":{"colSpan":1,"order":2},"tablet":{"colSpan":1,"order":2},"desktop":{"colSpan":1,"order":2}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-bar","message":"Main Content"}}],"gridTemplate":"1fr 3fr","gap":"16px"}],"globalConfig":{"gap":"16px","padding":"16px","backgroundColor":"#f5f5f5","minSectionHeight":"400px"}}',
1);

-- Sidebar Right (75/25)
INSERT INTO layout_template (name, description, category, structure, is_system) VALUES
('Sidebar Right', '75/25 split with right sidebar', 'mixed',
'{"version":"1.0","responsive":{"mobile":{"enabled":true,"breakpoint":768},"tablet":{"enabled":true,"breakpoint":1024},"desktop":{"enabled":true,"breakpoint":1920}},"sections":[{"sid":"s1","type":"row","height":"auto","heightFr":1,"minHeight":"400px","areas":[{"aid":"a1","colSpan":1,"colSpanFr":"3fr","rowSpan":1,"minWidth":"500px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-area","message":"Main Content"}},{"aid":"a2","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"250px","responsive":{"mobile":{"colSpan":1,"order":2},"tablet":{"colSpan":1,"order":2},"desktop":{"colSpan":1,"order":2}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-info-circle","message":"Info Panel"}}],"gridTemplate":"3fr 1fr","gap":"16px"}],"globalConfig":{"gap":"16px","padding":"16px","backgroundColor":"#f5f5f5","minSectionHeight":"400px"}}',
1);

-- Holy Grail (25/50/25)
INSERT INTO layout_template (name, description, category, structure, is_system) VALUES
('Holy Grail', 'Classic three column layout (1:2:1)', 'mixed',
'{"version":"1.0","responsive":{"mobile":{"enabled":true,"breakpoint":768},"tablet":{"enabled":true,"breakpoint":1024},"desktop":{"enabled":true,"breakpoint":1920}},"sections":[{"sid":"s1","type":"row","height":"auto","heightFr":1,"minHeight":"400px","areas":[{"aid":"a1","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"200px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-list","message":"Left Nav"}},{"aid":"a2","colSpan":1,"colSpanFr":"2fr","rowSpan":1,"minWidth":"400px","responsive":{"mobile":{"colSpan":1,"order":2},"tablet":{"colSpan":1,"order":2},"desktop":{"colSpan":1,"order":2}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-line","message":"Main Content"}},{"aid":"a3","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"200px","responsive":{"mobile":{"colSpan":1,"order":3},"tablet":{"colSpan":1,"order":3},"desktop":{"colSpan":1,"order":3}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-cog","message":"Tools"}}],"gridTemplate":"1fr 2fr 1fr","gap":"16px"}],"globalConfig":{"gap":"16px","padding":"16px","backgroundColor":"#f5f5f5","minSectionHeight":"400px"}}',
1);

-- Header + Two Columns
INSERT INTO layout_template (name, description, category, structure, is_system) VALUES
('Header + Two Columns', 'Full width header with two equal columns below', 'mixed',
'{"version":"1.0","responsive":{"mobile":{"enabled":true,"breakpoint":768},"tablet":{"enabled":true,"breakpoint":1024},"desktop":{"enabled":true,"breakpoint":1920}},"sections":[{"sid":"s1","type":"row","height":"auto","heightFr":1,"minHeight":"150px","areas":[{"aid":"a1","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"300px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-heading","message":"Header / Title"}}],"gridTemplate":"1fr","gap":"16px"},{"sid":"s2","type":"row","height":"auto","heightFr":2,"minHeight":"300px","areas":[{"aid":"a2","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"300px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-bar","message":"Chart 1"}},{"aid":"a3","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"300px","responsive":{"mobile":{"colSpan":1,"order":2},"tablet":{"colSpan":1,"order":2},"desktop":{"colSpan":1,"order":2}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-pie","message":"Chart 2"}}],"gridTemplate":"1fr 1fr","gap":"16px"}],"globalConfig":{"gap":"16px","padding":"16px","backgroundColor":"#f5f5f5","minSectionHeight":"200px"}}',
1);

-- Dashboard (KPI + Charts)
INSERT INTO layout_template (name, description, category, structure, is_system) VALUES
('Dashboard', 'KPI banner with charts below', 'advanced',
'{"version":"1.0","responsive":{"mobile":{"enabled":true,"breakpoint":768},"tablet":{"enabled":true,"breakpoint":1024},"desktop":{"enabled":true,"breakpoint":1920}},"sections":[{"sid":"s1","type":"row","height":"auto","heightFr":1,"minHeight":"120px","areas":[{"aid":"a1","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"150px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":2,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-dollar-sign","message":"Revenue"}},{"aid":"a2","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"150px","responsive":{"mobile":{"colSpan":1,"order":2},"tablet":{"colSpan":2,"order":2},"desktop":{"colSpan":1,"order":2}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-shopping-cart","message":"Orders"}},{"aid":"a3","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"150px","responsive":{"mobile":{"colSpan":1,"order":3},"tablet":{"colSpan":2,"order":3},"desktop":{"colSpan":1,"order":3}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-users","message":"Customers"}},{"aid":"a4","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"150px","responsive":{"mobile":{"colSpan":1,"order":4},"tablet":{"colSpan":2,"order":4},"desktop":{"colSpan":1,"order":4}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-percent","message":"Growth"}}],"gridTemplate":"1fr 1fr 1fr 1fr","gap":"16px"},{"sid":"s2","type":"row","height":"auto","heightFr":2,"minHeight":"300px","areas":[{"aid":"a5","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"300px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-line","message":"Main Chart"}}],"gridTemplate":"1fr","gap":"16px"},{"sid":"s3","type":"row","height":"auto","heightFr":1,"minHeight":"200px","areas":[{"aid":"a6","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"300px","responsive":{"mobile":{"colSpan":1,"order":1},"tablet":{"colSpan":1,"order":1},"desktop":{"colSpan":1,"order":1}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-chart-bar","message":"Chart"}},{"aid":"a7","colSpan":1,"colSpanFr":"1fr","rowSpan":1,"minWidth":"300px","responsive":{"mobile":{"colSpan":1,"order":2},"tablet":{"colSpan":1,"order":2},"desktop":{"colSpan":1,"order":2}},"content":{"type":"empty","widgetId":null,"widgetType":null,"config":{}},"emptyState":{"enabled":true,"icon":"fa-table","message":"Table"}}],"gridTemplate":"1fr 1fr","gap":"16px"}],"globalConfig":{"gap":"16px","padding":"16px","backgroundColor":"#f5f5f5","minSectionHeight":"150px"}}',
1);

-- =================================================================
-- Migration Complete
-- =================================================================
