ALTER TABLE soyshop_orders ADD COLUMN flag TINYINT NOT NULL DEFAULT 0 AFTER status;
