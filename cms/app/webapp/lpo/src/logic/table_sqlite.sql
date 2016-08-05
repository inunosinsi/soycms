CREATE TABLE soylpo_list(
	id integer primary key AUTOINCREMENT,
	title varchar(255),
	content varchar(4000),
	mode integer not null default 1,
	keyword varchar(255),
	is_public integer not null default 0,
	create_date integer not null,
	update_date integer not null
);

CREATE TABLE soylpo_config(
	id integer primary key AUTOINCREMENT,
	wisywig integer
);

CREATE TABLE soylpo_log(
	lpo_id integer not null,
	referer varchar(512),
	entry_date integer not null,
	create_date integer not null
);