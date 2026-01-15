-- Migration: Add is_system flag to dashboard_instance table
-- This allows marking certain dashboards as system dashboards that cannot be deleted

ALTER TABLE dashboard_instance
ADD COLUMN is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System dashboards cannot be deleted'
AFTER user_id;

-- Add index for is_system column
ALTER TABLE dashboard_instance
ADD INDEX idx_is_system (is_system);
