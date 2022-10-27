ALTER TABLE soymail_user ADD COLUMN not_send INTEGER DEFAULT 0 AFTER mail_error_count;
ALTER TABLE soymail_user MODIFY COLUMN mail_address VARCHAR(255) UNIQUE;
