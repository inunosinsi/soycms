ALTER TABLE soymail_user ADD COLUMN is_error INTEGER DEFAULT 0 AFTER not_send;
