create table soyshop_user_attribute(
	user_id INTEGER NOT NULL,
	user_field_id varchar,
	user_value varchar,
	unique(user_id,user_field_id)
);