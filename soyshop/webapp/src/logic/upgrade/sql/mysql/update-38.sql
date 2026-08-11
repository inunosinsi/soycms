ALTER TABLE soyshop_orders ADD COLUMN display_order tinyint not null default 0 AFTER is_addition;
