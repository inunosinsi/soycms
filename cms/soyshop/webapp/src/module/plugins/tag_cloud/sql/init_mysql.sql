CREATE TABLE soyshop_tag_cloud_dictionary(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	word VARCHAR(128) UNIQUE,
	hash CHAR(16) UNIQUE,
	category_id INTEGER NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE soyshop_tag_cloud_linking(
	item_id INTEGER NOT NULL,
	word_id INTEGER NOT NULL,
	UNIQUE(item_id, word_id)
) ENGINE=InnoDB;

CREATE TABLE soyshop_tag_cloud_category(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	label VARCHAR(128) UNIQUE
) ENGINE=InnoDB;

CREATE TABLE soyshop_tag_cloud_language(
	word_id INTEGER NOT NULL DEFAULT 0,
	lang CHAR(2),
	label VARCHAR(128),
	UNIQUE(word_id, lang)
) ENGINE=InnoDB;

CREATE TABLE soyshop_tag_cloud_category_language(
	category_id INTEGER NOT NULL DEFAULT 0,
	lang CHAR(2),
	label VARCHAR(128),
	UNIQUE(category_id, lang)
) ENGINE=InnoDB;
