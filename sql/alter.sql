ALTER TABLE auser_session
    MODIFY mac_addr VARCHAR(255) DEFAULT '',
    MODIFY fcm_token VARCHAR(255) DEFAULT '',
    MODIFY fcmskid INT(11) DEFAULT 0,
    MODIFY outlet_chkid INT(11) DEFAULT 0,
    MODIFY licid INT(11) DEFAULT 0;

-- Graph snapshot image field
ALTER TABLE graph ADD COLUMN snapshot VARCHAR(255) DEFAULT NULL COMMENT 'Saved chart image filename' AFTER placeholder_settings;

-- =============================================================================
-- Widget Type and Filter Mandatory Tables (2026-01-22)
-- =============================================================================

-- Add is_system column to data_filter table
ALTER TABLE data_filter ADD COLUMN is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System filter - only editable by admin' AFTER is_required;

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

-- =============================================================================
-- Counter Tables (2026-01-23)
-- =============================================================================

-- Add data_mapping column if counter table already exists (for Element base class compatibility)
-- ALTER TABLE counter ADD COLUMN data_mapping TEXT COMMENT 'JSON data mapping (not used for counter)' AFTER query;

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