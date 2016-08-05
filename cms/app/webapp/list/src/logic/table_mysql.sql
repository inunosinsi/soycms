CREATE TABLE soylist_list (
	config TEXT
)ENGINE=InnoDB;

CREATE TABLE soylist_config (
	config TEXT
)ENGINE=InnoDB;

CREATE TABLE soylist_item (
	id integer primary key AUTO_INCREMENT,
	name varchar(255),
	category varchar(255),
	image varchar(255),
	price varchar(255),
	standard varchar(255),
	description varchar(255),
	url varchar(255),
	sort integer,
	create_date integer,
	update_date integer
)ENGINE=InnoDB;

CREATE TABLE soylist_category (
	id integer primary key AUTO_INCREMENT,
	name varchar(255),
	memo varchar(255),
	sort integer,
	config text,
	create_date integer,
	update_date integer
)ENGINE=InnoDB;