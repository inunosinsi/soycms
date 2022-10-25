CREATE TABLE soycalendar_item (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	schedule_date INTEGER NOT NULL,
	title_id INTEGER NOT NULL,
	start VARCHAR(255),
	end VARCHAR(255),
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL,
	UNIQUE(schedule_date, title_id)
)ENGINE=InnoDB;

CREATE TABLE soycalendar_title (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(255),
	attribute VARCHAR(255),
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
)ENGINE=InnoDB;

CREATE TABLE soycalendar_custom_item (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	label VARCHAR(52) NOT NULL,
	alias VARCHAR(52) NOT NULL,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
)ENGINE=InnoDB;

CREATE TABLE soycalendar_custom_item_checked (
	item_id INTEGER NOT NULL,
	custom_id INTEGER NOT NULL,
	UNIQUE(item_id, custom_id)
)ENGINE=InnoDB;

CREATE TABLE soycalendar_data_sets(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	class_name VARCHAR(255) UNIQUE,
	object_data TEXT
) ENGINE=InnoDB;