-- Run this to add the filter_config column to an existing database:
ALTER TABLE filter ADD COLUMN filter_config TEXT COMMENT 'JSON config options like {inline: true}' AFTER static_options;
