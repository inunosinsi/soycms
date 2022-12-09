ALTER TABLE soyshop_coupon ADD COLUMN coupon_type tinyint not null default 0;
ALTER TABLE soyshop_coupon ADD COLUMN discount_percent integer not null default 0;
ALTER TABLE soyshop_coupon ADD COLUMN price_limit_min integer;
ALTER TABLE soyshop_coupon ADD COLUMN price_limit_max integer;