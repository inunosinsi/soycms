CREATE TABLE soyshop_tag_cloud_dictionary(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	word VARCHAR UNIQUE,
	hash VARCHAR UNIQUE,
	category_id INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE soyshop_tag_cloud_linking(
	item_id INTEGER NOT NULL,
	word_id INTEGER NOT NULL,
	UNIQUE(item_id, word_id)
);

CREATE TABLE soyshop_tag_cloud_category(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	label VARCHAR UNIQUE
);

CREATE TABLE soyshop_tag_cloud_language(
	word_id INTEGER NOT NULL DEFAULT 0,
	lang VARCHAR,
	label VARCHAR,
	UNIQUE(word_id, lang)
);
