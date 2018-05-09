CREATE TABLE soyshop_custom_search(
	item_id INTEGER NOT NULL,
	lang TINYINT NOT NULL DEFAULT 0,
	UNIQUE(item_id, lang)
) ENGINE=InnoDB;

CREATE TABLE soyshop_category_custom_search(
	category_id INTEGER NOT NULL,
	lang TINYINT NOT NULL DEFAULT 0,
	UNIQUE(category_id, lang)
) ENGINE=InnoDB;

CREATE TABLE soyshop_custom_search_attribute(
	search_field_id VARCHAR(255),
	search_custom_field_id VARCHAR(255),
	search_field_value TEXT,
	search_field_value2 TEXT,
	UNIQUE(search_field_id, search_custom_field_id)
) ENGINE=InnoDB;
