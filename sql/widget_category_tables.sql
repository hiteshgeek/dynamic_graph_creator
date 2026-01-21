-- ============================================================================
-- WIDGET CATEGORY TABLES
-- ============================================================================
-- This file contains SQL for widget category feature
-- Run this file to add widget categories to an existing installation
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
