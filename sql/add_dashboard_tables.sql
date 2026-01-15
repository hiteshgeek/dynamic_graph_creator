-- ============================================================================
-- Dashboard System Database Tables and Templates
-- ============================================================================
-- Creates dashboard_template and dashboard_instance tables
-- Includes all 13 system dashboard templates (CSS handled in SCSS)
-- ============================================================================

-- Drop existing tables if they exist (in correct order due to foreign keys)
DROP TABLE IF EXISTS dashboard_instance;
DROP TABLE IF EXISTS dashboard_template;
DROP TABLE IF EXISTS dashboard_template_category;

-- Dashboard Template Category Table
CREATE TABLE dashboard_template_category (
    ltcid INT(11) AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE COMMENT 'URL-friendly identifier (e.g., "columns", "advanced")',
    name VARCHAR(100) NOT NULL COMMENT 'Display name (e.g., "Columns", "Advanced")',
    description TEXT COMMENT 'Category description',
    icon VARCHAR(50) DEFAULT NULL COMMENT 'Font Awesome icon class (e.g., "fa-columns")',
    color VARCHAR(20) DEFAULT NULL COMMENT 'Category color for UI (e.g., "#007bff")',
    display_order INT(11) DEFAULT 0 COMMENT 'Display order (lower numbers first)',
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=system category (protected), 0=custom category',
    ltcsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_display_order (display_order),
    INDEX idx_ltcsid (ltcsid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Layout template categories';

-- Dashboard Template Table (System templates and user-created templates)
CREATE TABLE dashboard_template (
    ltid INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Template name',
    description TEXT COMMENT 'Template description',
    ltcid INT(11) DEFAULT NULL COMMENT 'Category ID (foreign key to dashboard_template_category)',
    thumbnail VARCHAR(255) DEFAULT NULL COMMENT 'Preview image path',
    structure TEXT NOT NULL COMMENT 'JSON layout structure',
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=system template (protected), 0=user template',
    ltsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status: 1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL COMMENT 'User who created',
    updated_uid INT(11) DEFAULT NULL COMMENT 'User who last updated',
    INDEX idx_ltcid (ltcid),
    INDEX idx_ltsid (ltsid),
    INDEX idx_is_system (is_system),
    FOREIGN KEY (ltcid) REFERENCES dashboard_template_category(ltcid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dashboard dashboard templates';

-- Dashboard Instance Table (User layouts created from templates)
CREATE TABLE dashboard_instance (
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
    FOREIGN KEY (ltid) REFERENCES dashboard_template(ltid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User layout instances';

-- ============================================================================
-- SYSTEM TEMPLATE CATEGORIES (4 total)
-- ============================================================================

INSERT INTO dashboard_template_category (slug, name, description, icon, color, display_order, is_system, ltcsid) VALUES
('columns', 'Columns', 'Simple column-based layouts with equal or varied widths', 'fa-columns', '#007bff', 10, 1, 1),
('mixed', 'Mixed', 'Mixed layouts with sidebars and unequal column ratios', 'fa-table-columns', '#6610f2', 20, 1, 1),
('advanced', 'Advanced', 'Complex multi-section layouts with nested areas', 'fa-th', '#6f42c1', 30, 1, 1),
('rows', 'Rows', 'Row-based layouts with stacked sections', 'fa-bars', '#d63384', 40, 1, 1);

-- ============================================================================
-- SYSTEM LAYOUT TEMPLATES (13 total)
-- CSS styling is handled entirely in SCSS, JSON only contains structure
-- ============================================================================

-- 1. Single Column Template
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Single Column',
    'Simple single column layout',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'columns'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-bar", "message": "Add content here"}
                    }
                ]
            }
        ]
    }',
    1,
    1
);

-- 2. Two Column Template
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Two Columns',
    'Two equal columns side by side',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'columns'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-bar", "message": "Chart"}
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-table", "message": "Table"}
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 3. Three Column Template
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Three Columns',
    'Three equal columns for metrics',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'columns'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 1fr 1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-line", "message": "Metric 1"}
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-pie", "message": "Metric 2"}
                    },
                    {
                        "aid": "a3",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-area", "message": "Metric 3"}
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 4. Four Column Template
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Four Columns',
    'Four equal columns for KPIs',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'columns'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 1fr 1fr 1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-dollar-sign", "message": "KPI 1"}
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-users", "message": "KPI 2"}
                    },
                    {
                        "aid": "a3",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-shopping-cart", "message": "KPI 3"}
                    },
                    {
                        "aid": "a4",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-line", "message": "KPI 4"}
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 5. Left Sidebar Template
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Left Sidebar',
    'Narrow left sidebar with main content area',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'mixed'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 3fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-bars", "message": "Sidebar"}
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "3fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-bar", "message": "Main Content"}
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 6. Right Sidebar Template
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Right Sidebar',
    'Main content with narrow right sidebar',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'mixed'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "3fr 1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "3fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-area", "message": "Main Content"}
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-info-circle", "message": "Info Panel"}
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 7. Holy Grail Template
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Holy Grail',
    'Classic three-column layout with navigation, content, and tools',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'mixed'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 2fr 1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-list", "message": "Left Nav"}
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "2fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-line", "message": "Main Content"}
                    },
                    {
                        "aid": "a3",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-cog", "message": "Tools"}
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 8. Multi-Section Template (Header + Two Columns)
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Header + Two Columns',
    'Full-width header with two columns below',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'advanced'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-heading", "message": "Header / Title"}
                    }
                ]
            },
            {
                "sid": "s2",
                "gridTemplate": "1fr 1fr",
                "areas": [
                    {
                        "aid": "a2",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-bar", "message": "Chart 1"}
                    },
                    {
                        "aid": "a3",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-pie", "message": "Chart 2"}
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 9. Dashboard Template
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Dashboard',
    'Complete dashboard with KPIs, main chart, and secondary charts',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'advanced'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 1fr 1fr 1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-dollar-sign", "message": "Revenue"}
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-shopping-cart", "message": "Orders"}
                    },
                    {
                        "aid": "a3",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-users", "message": "Customers"}
                    },
                    {
                        "aid": "a4",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-percent", "message": "Growth"}
                    }
                ]
            },
            {
                "sid": "s2",
                "gridTemplate": "1fr",
                "areas": [
                    {
                        "aid": "a5",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-line", "message": "Main Chart"}
                    }
                ]
            },
            {
                "sid": "s3",
                "gridTemplate": "1fr 1fr",
                "areas": [
                    {
                        "aid": "a6",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-bar", "message": "Chart"}
                    },
                    {
                        "aid": "a7",
                        "colSpanFr": "1fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-table", "message": "Table"}
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 10. Left Multi-Row + Right Single
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Left Multi-Row + Right Single',
    'Left column with 3 rows, right column with single large area',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'advanced'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 2fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "hasSubRows": true,
                        "subRows": [
                            {
                                "rowId": "r1",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-line", "message": "Add chart here"}
                            },
                            {
                                "rowId": "r2",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-table", "message": "Add table here"}
                            },
                            {
                                "rowId": "r3",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-bar", "message": "Add chart here"}
                            }
                        ]
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "2fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-area", "message": "Add main chart here"}
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 11. Right Multi-Row + Left Single
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Right Multi-Row + Left Single',
    'Left column with single large area, right column with 3 rows',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'advanced'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "2fr 1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "2fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-area", "message": "Add main chart here"}
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "1fr",
                        "hasSubRows": true,
                        "subRows": [
                            {
                                "rowId": "r1",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-line", "message": "Add chart here"}
                            },
                            {
                                "rowId": "r2",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-table", "message": "Add table here"}
                            },
                            {
                                "rowId": "r3",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-bar", "message": "Add chart here"}
                            }
                        ]
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 12. Two Multi-Row Columns
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Two Multi-Row Columns',
    'Two columns, each with 2 rows',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'advanced'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "1fr",
                        "hasSubRows": true,
                        "subRows": [
                            {
                                "rowId": "r1",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-line", "message": "Top chart"}
                            },
                            {
                                "rowId": "r2",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-table", "message": "Bottom table"}
                            }
                        ]
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "1fr",
                        "hasSubRows": true,
                        "subRows": [
                            {
                                "rowId": "r3",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-bar", "message": "Top chart"}
                            },
                            {
                                "rowId": "r4",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-pie", "message": "Bottom chart"}
                            }
                        ]
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- 13. Focal Point with Multi-Row
INSERT INTO dashboard_template (name, description, ltcid, structure, is_system, ltsid)
VALUES (
    'Focal Point with Multi-Row',
    'Large left area with right column split into 3 rows',
    (SELECT ltcid FROM dashboard_template_category WHERE slug = 'advanced'),
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "2fr 1fr",
                "areas": [
                    {
                        "aid": "a1",
                        "colSpanFr": "2fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-area", "message": "Featured Content"}
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "1fr",
                        "hasSubRows": true,
                        "subRows": [
                            {
                                "rowId": "r1",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-dollar-sign", "message": "KPI 1"}
                            },
                            {
                                "rowId": "r2",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-users", "message": "KPI 2"}
                            },
                            {
                                "rowId": "r3",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-line", "message": "KPI 3"}
                            }
                        ]
                    }
                ]
            }
        ]
    }',
    1,
     1
);

-- ============================================================================
-- Migration Complete
-- - 4 System Categories Installed
-- - 13 System Templates Installed (linked to categories)
-- ============================================================================
--
-- NOTES:
-- - Categories now have their own table with ordering, icons, colors, and descriptions
-- - Templates reference categories via foreign key (ltcid) instead of string values
-- - Categories can be managed independently and have controlled ordering
-- - ON DELETE SET NULL ensures templates won't break if a category is deleted
-- ============================================================================
