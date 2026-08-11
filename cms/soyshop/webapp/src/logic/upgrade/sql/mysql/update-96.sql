ALTER TABLE soyshop_plugins ADD INDEX idx_plugins_active_order (is_active, display_order, plugin_id, id);
