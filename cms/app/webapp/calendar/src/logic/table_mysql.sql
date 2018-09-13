CREATE TABLE soycalendar_item (
	id integer primary key AUTO_INCREMENT,
	schedule integer,
	title integer,
	start varchar(255),
	end varchar(255),
	create_date integer,
	update_date integer
)ENGINE=InnoDB;

CREATE TABLE soycalendar_title (
	id integer primary key AUTO_INCREMENT,
	title varchar(255),
	attribute varchar(255),
	create_date integer,
	update_date integer
)ENGINE=InnoDB;