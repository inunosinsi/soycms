create table soyshop_category_attribute(
	category_id integer,
	category_field_id varchar,
	category_value varchar,
	unique(category_id,category_field_id)
);