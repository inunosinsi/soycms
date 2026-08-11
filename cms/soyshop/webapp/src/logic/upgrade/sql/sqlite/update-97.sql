ALTER TABLE soyshop_mail_log ADD INDEX idx_maillog_user_date (user_id, send_date DESC);
