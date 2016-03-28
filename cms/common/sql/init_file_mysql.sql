create table cmsfile(
	id INTEGER PRIMARY key AUTO_INCREMENT,
	name VARCHAR(255),
	path TEXT,
	url VARCHAR(255),
	parent_file_id integer,
	extension VARCHAR(255),
	is_dir integer,
	is_image integer,
	file_size integer,
	create_date integer,
	update_date integer
)ENGINE=InnoDB;
