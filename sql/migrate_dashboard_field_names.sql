-- Migration: Update field names for dashboard tables
-- Renames layout_* field names to dashboard_* field names

-- 1. Rename dashboard_template_category fields
ALTER TABLE dashboard_template_category 
  CHANGE COLUMN ltcid dtcid INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE COLUMN ltcsid dtcsid INT(11) DEFAULT 1;

-- 2. Rename dashboard_template fields
ALTER TABLE dashboard_template 
  CHANGE COLUMN ltid dtid INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE COLUMN ltcid dtcid INT(11) NOT NULL,
  CHANGE COLUMN ltsid dtsid INT(11) DEFAULT 1;

-- 3. Rename dashboard_instance fields
ALTER TABLE dashboard_instance 
  CHANGE COLUMN liid diid INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE COLUMN ltid dtid INT(11) NOT NULL,
  CHANGE COLUMN lisid disid INT(11) DEFAULT 1;

-- Note: Run this migration after renaming tables and before using the new code
