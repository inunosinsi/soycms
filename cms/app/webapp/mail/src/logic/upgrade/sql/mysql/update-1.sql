CREATE TABLE soymail_data_sets(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	class_name VARCHAR(255) UNIQUE,
	object_data TEXT
) ENGINE=InnoDB;
