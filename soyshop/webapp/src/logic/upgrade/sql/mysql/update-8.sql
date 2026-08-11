ALTER TABLE soyshop_user ADD COLUMN nickname VARCHAR(255) AFTER reading;
ALTER TABLE soyshop_user ADD COLUMN url VARCHAR(255) AFTER fax_number;
ALTER TABLE soyshop_user ADD COLUMN image_path VARCHAR(255) AFTER nickname;