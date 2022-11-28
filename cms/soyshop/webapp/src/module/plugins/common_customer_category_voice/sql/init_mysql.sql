create table soyshop_category_attribute(
	category_id integer,
	category_field_id VARCHAR(255),
	category_value TEXT,
	unique(category_id,category_field_id)
) ENGINE=InnoDB;