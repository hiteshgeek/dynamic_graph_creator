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

-- Independent filter definitions table
-- Filters are standalone reusable components
CREATE TABLE IF NOT EXISTS filter (
    fid INT(11) AUTO_INCREMENT PRIMARY KEY,
    filter_key VARCHAR(50) NOT NULL COMMENT 'Placeholder key e.g. :date_from',
    filter_label VARCHAR(100) NOT NULL COMMENT 'Display label',
    filter_type ENUM('text', 'number', 'date', 'date_range', 'select', 'multi_select', 'checkbox', 'radio', 'tokeninput') NOT NULL DEFAULT 'text',
    data_source ENUM('static', 'query') NOT NULL DEFAULT 'static' COMMENT 'How to get filter options',
    data_query TEXT COMMENT 'SQL query to fetch options (if data_source=query)',
    static_options TEXT COMMENT 'JSON array of static options [{value, label}]',
    filter_config TEXT COMMENT 'JSON config options like {inline: true}',
    default_value VARCHAR(255) DEFAULT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    fsid TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 3=deleted',
    created_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fsid (fsid),
    INDEX idx_filter_key (filter_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Standalone filter definitions';

-- Sample data for testing (optional)
-- Uncomment to insert sample data

/*
-- Sample filters
INSERT INTO filter (filter_key, filter_label, filter_type, data_source, static_options, default_value) VALUES
(':year', 'Year', 'select', 'static', '[{"value":"2024","label":"2024"},{"value":"2023","label":"2023"},{"value":"2022","label":"2022"}]', '2024'),
(':month', 'Month', 'select', 'query', NULL, NULL),
(':date_from', 'Date From', 'date', 'static', NULL, NULL),
(':date_to', 'Date To', 'date', 'static', NULL, NULL),
(':status', 'Status', 'multi_select', 'static', '[{"value":"active","label":"Active"},{"value":"inactive","label":"Inactive"},{"value":"pending","label":"Pending"}]', NULL);

-- Update month filter with query
UPDATE filter SET data_query = 'SELECT month_number as value, month_name as label FROM months ORDER BY month_number' WHERE filter_key = ':month';

-- Sample graph using placeholders that match filter keys
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
*/
