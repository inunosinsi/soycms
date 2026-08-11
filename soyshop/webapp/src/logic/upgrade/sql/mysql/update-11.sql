ALTER TABLE soyshop_user ADD COLUMN mail_error_count INTEGER default 0 AFTER memo;
ALTER TABLE soyshop_user ADD COLUMN not_send TINYINT default 0 AFTER mail_error_count;