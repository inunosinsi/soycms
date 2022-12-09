ALTER TABLE soyshop_coupon ADD COLUMN coupon_type tinyint not null default 0 AFTER coupon_code;
ALTER TABLE soyshop_coupon ADD COLUMN discount_percent tinyint not null default 0 AFTER discount;
ALTER TABLE soyshop_coupon ADD COLUMN price_limit_min integer AFTER time_limit_end;
ALTER TABLE soyshop_coupon ADD COLUMN price_limit_max integer AFTER price_limit_min;