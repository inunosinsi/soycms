CREATE TABLE soyshop_auto_complete_dictionary(
    item_id INTEGER NOT NULL UNIQUE,
    hiragana VARCHAR(512),
	katakana VARCHAR(512)
)ENGINE=InnoDB;
