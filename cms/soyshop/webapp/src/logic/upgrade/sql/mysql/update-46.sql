ALTER TABLE soyshop_item_review ADD COLUMN vote integer AFTER approval;
ALTER TABLE soyshop_item_review ADD COLUMN attributes varchar(255) AFTER vote;
