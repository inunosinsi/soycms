ALTER TABLE soyshop_user ADD COLUMN mail_error_count INTEGER default 0;
ALTER TABLE soyshop_user ADD COLUMN not_send INTEGER default 0;