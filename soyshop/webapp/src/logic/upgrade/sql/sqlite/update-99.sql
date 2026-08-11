ALTER TABLE soyshop_orders ADD INDEX idx_orders_sended_item (is_sended, item_id, order_id);
