create table soyshop_user_storage(
	id INTEGER primary key AUTO_INCREMENT,
	user_id INTEGER NOT NULL,
	file_name VARCHAR(512) NOT NULL,
	token VARCHAR(255) NOT NULL unique,
	upload_date INTEGER NOT NULL
) ENGINE = InnoDB;