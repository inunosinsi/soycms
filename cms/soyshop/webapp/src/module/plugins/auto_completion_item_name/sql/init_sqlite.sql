CREATE TABLE soyshop_auto_complete_dictionary(
    item_id INTEGER NOT NULL UNIQUE,
    hiragana VARCHAR,
	katakana VARCHAR,
	other VARCHAR
)ENGINE=InnoDB;

CREATE TABLE soyshop_auto_complete_dictionary_category(
    item_id INTEGER NOT NULL UNIQUE,
    hiragana VARCHAR,
	katakana VARCHAR,
	other VARCHAR
)ENGINE=InnoDB;
