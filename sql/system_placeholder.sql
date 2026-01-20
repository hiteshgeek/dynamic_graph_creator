-- =============================================
-- System Placeholders Table
-- Special placeholders resolved at runtime using system values
-- =============================================

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

-- =============================================
-- Default System Placeholders
-- =============================================

INSERT INTO system_placeholder (placeholder_key, placeholder_label, description, resolver_method) VALUES
('logged_in_uid', 'Logged In User ID', 'Returns the currently logged in user ID', 'getLoggedInUid'),
('logged_in_company_id', 'Logged In Company ID', 'Returns the company ID of the logged in user', 'getLoggedInCompanyId'),
('logged_in_licence_id', 'Logged In Licence ID', 'Returns the licence ID of the logged in user', 'getLoggedInLicenceId'),
('logged_in_is_admin', 'Logged In Is Admin', 'Returns 1 if the logged in user is an admin, 0 otherwise', 'getLoggedInIsAdmin');
