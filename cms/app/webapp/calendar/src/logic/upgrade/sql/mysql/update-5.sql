CREATE TABLE soycalendar_custom_item (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	label VARCHAR(52) NOT NULL,
	alias VARCHAR(52) NOT NULL,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
)ENGINE=InnoDB;