create unique index soyshop_orders on soyshop_orders(order_id, item_id, cdate);
create unique index soyshop_order_state_history on soyshop_order_state_history(order_id, order_date);
