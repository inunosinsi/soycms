create table soyshop_user_attribute(
	user_id INTEGER NOT NULL,
	user_field_id VARCHAR(255),
	user_value TEXT,
	unique(user_id,user_field_id)
) ENGINE=InnoDB;