ALTER TABLE soyshop_user ADD COLUMN account_id VARCHAR(50) AFTER nickname;
ALTER TABLE soyshop_user ADD COLUMN is_profile_display TINYINT default 0 AFTER is_disabled;

DROP TABLE soyshop_photo;
DROP TABLE soyshop_album;