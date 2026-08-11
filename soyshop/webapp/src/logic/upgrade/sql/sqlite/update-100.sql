ALTER TABLE soyshop_order ADD INDEX idx_order_user_status_date (user_id, order_status, order_date DESC);
