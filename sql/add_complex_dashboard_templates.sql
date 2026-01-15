-- Add complex nested dashboard templates
-- These templates support multi-row subdivisions within columns

-- Template: Left Multi-Row + Right Single
-- 1fr (with 3 rows) | 2fr (single large area)
INSERT INTO dashboard_template (name, description, category, structure, is_system, dtsid)
VALUES (
    'Left Multi-Row + Right Single',
    'Left column with 3 rows, right column with single large area',
    'advanced',
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 2fr",
                "gap": "16px",
                "minHeight": "400px",
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

-- Template: Right Multi-Row + Left Single
-- 2fr (single large area) | 1fr (with 3 rows)
INSERT INTO dashboard_template (name, description, category, structure, is_system, dtsid)
VALUES (
    'Right Multi-Row + Left Single',
    'Left column with single large area, right column with 3 rows',
    'advanced',
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "2fr 1fr",
                "gap": "16px",
                "minHeight": "400px",
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
                                "emptyState": {"icon": "fa-info-circle", "message": "Add info here"}
                            },
                            {
                                "rowId": "r2",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-pie", "message": "Add chart here"}
                            },
                            {
                                "rowId": "r3",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-list", "message": "Add list here"}
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

-- Template: Complex Grid with Variable Heights
-- Left column: 2 rows (1fr, 2fr), Right column: single
INSERT INTO dashboard_template (name, description, category, structure, is_system, dtsid)
VALUES (
    'Variable Height Multi-Row',
    'Left column with 2 rows (small + large), right column single',
    'advanced',
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 2fr",
                "gap": "16px",
                "minHeight": "400px",
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
                                "emptyState": {"icon": "fa-calculator", "message": "Add metrics"}
                            },
                            {
                                "rowId": "r2",
                                "height": "2fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-chart-line", "message": "Add trend chart"}
                            }
                        ]
                    },
                    {
                        "aid": "a2",
                        "colSpanFr": "2fr",
                        "content": {"type": "empty"},
                        "emptyState": {"icon": "fa-chart-area", "message": "Add main visualization"}
                    }
                ]
            }
        ]
    }',
    1,
    1
);

-- Template: Both Columns Multi-Row
-- Both columns have subdivisions
INSERT INTO dashboard_template (name, description, category, structure, is_system, dtsid)
VALUES (
    'Dual Multi-Row Dashboard',
    'Both columns with multiple rows for maximum flexibility',
    'advanced',
    '{
        "sections": [
            {
                "sid": "s1",
                "gridTemplate": "1fr 1fr",
                "gap": "16px",
                "minHeight": "400px",
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
                                "emptyState": {"icon": "fa-chart-bar", "message": "Add chart"}
                            },
                            {
                                "rowId": "r2",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-table", "message": "Add table"}
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
                                "emptyState": {"icon": "fa-chart-line", "message": "Add trend"}
                            },
                            {
                                "rowId": "r4",
                                "height": "1fr",
                                "content": {"type": "empty"},
                                "emptyState": {"icon": "fa-list-ul", "message": "Add list"}
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
