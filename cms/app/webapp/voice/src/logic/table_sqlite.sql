CREATE TABLE soyvoice_comment (
	id integer primary key AUTOINCREMENT,
	nickname varchar,
	content varchar,
	prefecture integer,
	url varchar,
	email varchar,
	image varchar,
	user_type integer not null,
	is_published integer not null,
	is_entry integer not null,
	reply varchar,
	attribute varchar,
	comment_date integer,
	create_date integer,
	update_date integer
);

CREATE TABLE soyvoice_log (
	id integer primary key AUTOINCREMENT,
	count integer,
	export_date integer
);

CREATE TABLE soyvoice_config (
	id integer primary key AUTOINCREMENT,
	owner_name varchar,
	owner_display integer,
	count integer,
	archive integer,
	resize integer,
	is_resize integer,
	sync_site integer,
	label integer,
	is_sync integer,
	is_published integer,
	config varchar
);