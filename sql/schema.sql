-- Dynamic Graph Creator - Database Schema
-- Compatible with MySQL 5.5+

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS dynamic_graph_creator CHARACTER SET utf8 COLLATE utf8_general_ci;
USE dynamic_graph_creator;

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
    updated_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_uid INT(11) DEFAULT NULL,
    updated_uid INT(11) DEFAULT NULL,
    INDEX idx_gsid (gsid),
    INDEX idx_graph_type (graph_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores graph definitions';

-- Generic filter table (reusable for any entity)
CREATE TABLE IF NOT EXISTS filter (
    fid INT(11) AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL COMMENT 'graph, report, dashboard, etc.',
    entity_id INT(11) NOT NULL COMMENT 'ID of the parent entity',
    filter_key VARCHAR(50) NOT NULL COMMENT 'Placeholder key e.g. :date_from',
    filter_label VARCHAR(100) NOT NULL COMMENT 'Display label',
    filter_type ENUM('text', 'number', 'date', 'date_range', 'select', 'multi_select') NOT NULL DEFAULT 'text',
    filter_options TEXT COMMENT 'JSON for select options or config',
    default_value VARCHAR(255) DEFAULT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    sequence INT(11) NOT NULL DEFAULT 0,
    fsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_fsid (fsid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Generic filter definitions';

-- Sample data for testing (optional)
-- Uncomment to insert sample graph

/*
INSERT INTO graph (name, description, graph_type, config, query, data_mapping, created_uid) VALUES
(
    'Sample Bar Chart',
    'A sample bar chart showing monthly data',
    'bar',
    '{"title":"Monthly Sales","showLegend":true,"legendPosition":"top","showTooltip":true,"orientation":"vertical","barWidth":60,"showBackground":false,"borderRadius":4}',
    'SELECT month_name as category, total_amount as value FROM sales_summary WHERE year = :year ORDER BY month_number',
    '{"x_column":"category","y_column":"value"}',
    1
);

INSERT INTO filter (entity_type, entity_id, filter_key, filter_label, filter_type, default_value, sequence) VALUES
('graph', 1, ':year', 'Year', 'select', '2024', 1);
*/
