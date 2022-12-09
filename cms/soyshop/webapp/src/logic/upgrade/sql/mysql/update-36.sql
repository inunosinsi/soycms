ALTER TABLE soyshop_coupon ADD COLUMN is_free_delivery TINYINT NOT NULL DEFAULT 0 AFTER discount_percent;
ALTER TABLE soyshop_coupon_history ADD COLUMN is_free_delivery TINYINT NOT NULL DEFAULT 0 AFTER price;
