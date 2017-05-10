create table soyshop_custom_search(
    item_id INTEGER NOT NULL,
    lang TINYINT NOT NULL DEFAULT 0,
    UNIQUE(item_id, lang)
) ENGINE=InnoDB;
