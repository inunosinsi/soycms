CREATE TABLE soyshop_custom_search(
    item_id INTEGER NOT NULL,
    lang INTEGER NOT NULL DEFAULT 0,
    UNIQUE(item_id, lang)
);

CREATE TABLE soyshop_category_custom_search(
	category_id INTEGER NOT NULL,
	lang INTEGER NOT NULL DEFAULT 0,
	UNIQUE(category_id, lang)
);

CREATE TABLE soyshop_custom_search_attribute(
	search_field_id VARCHAR(255),
	search_custom_field_id VARCHAR(255),
	search_field_value TEXT,
	search_field_value2 TEXT,
	UNIQUE(search_field_id, search_custom_field_id)
);
