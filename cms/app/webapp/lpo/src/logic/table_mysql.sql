CREATE TABLE soylpo_list(
	id INTEGER primary key AUTO_INCREMENT,
	title VARCHAR(255),
	content VARCHAR(4000),
	mode INTEGER not null DEFAULT 1,
	keyword VARCHAR(255),
	is_public INTEGER not null DEFAULT 0,
	create_date INTEGER not null,
	update_date INTEGER not null
)ENGINE=InnoDB;

CREATE TABLE soylpo_config(
	id INTEGER primary key AUTO_INCREMENT,
	wisywig INTEGER
)ENGINE=InnoDB;

CREATE TABLE soylpo_log(
	lpo_id INTEGER not null,
	referer VARCHAR(512),
	entry_date INTEGER not null,
	create_date INTEGER not null
)ENGINE=Archive;