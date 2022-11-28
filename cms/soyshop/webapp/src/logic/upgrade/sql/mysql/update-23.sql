ALTER TABLE soyshop_item ADD order_period_start INTEGER DEFAULT 0 AFTER update_date;
ALTER TABLE soyshop_item ADD order_period_end INTEGER DEFAULT 2147483647 AFTER order_period_start;