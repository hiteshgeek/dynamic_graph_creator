-- ============================================================================
-- Layout System Database Tables and Templates
-- ============================================================================
-- Creates layout_template and layout_instance tables
-- Includes all 13 system layout templates (CSS handled in SCSS)
-- ============================================================================

-- Drop existing tables if they exist
DROP TABLE IF EXISTS layout_instance;
DROP TABLE IF EXISTS layout_template;

-- Layout Template Table (System templates and user-created templates)
CREATE TABLE layout_template (
    ltid INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Template name',
    description TEXT COMMENT 'Template description',
    category VARCHAR(50) DEFAULT 'basic' COMMENT 'basic, advanced, custom',
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

-- Layout Instance Table (User layouts created from templates)
CREATE TABLE layout_instance (
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

-- ============================================================================
-- SYSTEM LAYOUT TEMPLATES (13 total)
-- CSS styling is handled entirely in SCSS, JSON only contains structure
-- ============================================================================

-- 1. Single Column Template
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Single Column',
    'Simple single column layout',
    'columns',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Two Columns',
    'Two equal columns side by side',
    'columns',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Three Columns',
    'Three equal columns for metrics',
    'columns',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Four Columns',
    'Four equal columns for KPIs',
    'columns',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Left Sidebar',
    'Narrow left sidebar with main content area',
    'mixed',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Right Sidebar',
    'Main content with narrow right sidebar',
    'mixed',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Holy Grail',
    'Classic three-column layout with navigation, content, and tools',
    'mixed',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Header + Two Columns',
    'Full-width header with two columns below',
    'advanced',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Dashboard',
    'Complete dashboard with KPIs, main chart, and secondary charts',
    'advanced',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Left Multi-Row + Right Single',
    'Left column with 3 rows, right column with single large area',
    'advanced',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Right Multi-Row + Left Single',
    'Left column with single large area, right column with 3 rows',
    'advanced',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Two Multi-Row Columns',
    'Two columns, each with 2 rows',
    'advanced',
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
INSERT INTO layout_template (name, description, category, structure, is_system, ltsid)
VALUES (
    'Focal Point with Multi-Row',
    'Large left area with right column split into 3 rows',
    'advanced',
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
-- Migration Complete - All 13 System Templates Installed
-- ============================================================================
