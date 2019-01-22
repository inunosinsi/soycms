create unique index soyshop_order_1 on soyshop_order(order_date, user_id);
create unique index soyshop_auto_login_1 on soyshop_auto_login(user_id, session_token);
create unique index soyshop_mail_log_1 on soyshop_mail_log(order_id, user_id, send_date);
