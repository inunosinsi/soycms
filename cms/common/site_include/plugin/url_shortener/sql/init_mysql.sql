create table URLShortener(
	id integer primary key AUTO_INCREMENT,
	url_from varchar(255) unique,
	url_to text,
	target_type integer not null,
	target_id integer not null,
	title text,
	memo text,
	attr text,
	cdate INTEGER,
	udate INTEGER,
	unique(target_type, target_id) 
) type = InnoDB;