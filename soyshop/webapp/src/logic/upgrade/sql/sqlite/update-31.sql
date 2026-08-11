ALTER TABLE soyshop_custom_search DROP INDEX item_id;
ALTER TABLE soyshop_custom_search ADD COLUMN lang TINYINT NOT NULL DEFAULT 0;
ALTER TABLE soyshop_custom_search ADD UNIQUE(item_id, lang);
