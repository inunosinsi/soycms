create table cmsfile(
	id INTEGER PRIMARY key AUTOINCREMENT,
	name varchar,
	path varchar,
	url varchar,
	parent_file_id integer,
	extension varchar,
	is_dir integer,
	is_image integer,
	file_size integer,
	create_date integer,
	update_date integer
);
