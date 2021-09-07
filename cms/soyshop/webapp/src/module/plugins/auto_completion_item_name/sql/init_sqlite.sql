CREATE TABLE soyshop_auto_complete_dictionary(
    item_id INTEGER NOT NULL UNIQUE,
    hiragana VARCHAR,
	katakana VARCHAR
)ENGINE=InnoDB;
