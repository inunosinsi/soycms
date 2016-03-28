create table URLShortener(
	id integer primary key,
	url_from varchar unique,
	url_to text,
	target_type integer,
	target_id integer,
	title text,
	memo text,
	attr text,
	cdate date,
	udate date,
	unique(target_type, target_id) 
);

