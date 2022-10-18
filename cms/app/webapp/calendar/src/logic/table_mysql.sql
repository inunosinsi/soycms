CREATE TABLE soycalendar_item (
	id integer primary key AUTO_INCREMENT,
	schedule_date INTEGER NOT NULL,
	title_id INTEGER NOT NULL,
	start varchar(255),
	end varchar(255),
	create_date integer,
	update_date integer,
	UNIQUE(schedule_date, title_id)
)ENGINE=InnoDB;

CREATE TABLE soycalendar_title (
	id integer primary key AUTO_INCREMENT,
	title varchar(255),
	attribute varchar(255),
	create_date integer,
	update_date integer
)ENGINE=InnoDB;

CREATE TABLE soycalendar_data_sets(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	class_name VARCHAR(255) UNIQUE,
	object_data TEXT
) ENGINE=InnoDB;