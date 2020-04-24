ALTER TABLE soyshop_user ADD COLUMN account_id VARCHAR;
ALTER TABLE soyshop_user ADD COLUMN is_profile_display INTEGER default 0;

DROP TABLE soyshop_photo;
DROP TABLE soyshop_album;