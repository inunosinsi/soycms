CREATE TABLE soylist_list (
	config TEXT
);

CREATE TABLE soylist_config (
	config TEXT
);

CREATE TABLE soylist_item (
	id integer primary key AUTOINCREMENT,
	name varchar,
	category varchar,
	image varchar,
	price varchar,
	standard varchar,
	description varchar,
	url varchar,
	sort integer,
	create_date integer,
	update_date integer
);

CREATE TABLE soylist_category (
	id integer primary key AUTOINCREMENT,
	name varchar,
	memo varchar,
	sort integer,
	config text,
	create_date integer,
	update_date integer
);