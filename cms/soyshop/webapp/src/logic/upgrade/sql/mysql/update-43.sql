create unique index soyshop_order on soyshop_order(order_date, user_id);
create unique index soyshop_auto_login on soyshop_auto_login(user_id, session_token);
