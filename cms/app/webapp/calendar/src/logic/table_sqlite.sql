CREATE TABLE soycalendar_item (
	id integer primary key AUTOINCREMENT,
	schedule integer,
	title integer,
	start varchar,
	end varchar,
	create_date integer,
	update_date integer
);

CREATE TABLE soycalendar_title (
	id integer primary key AUTOINCREMENT,
	title varchar,
	attribute varchar,
	create_date integer,
	update_date integer
);

CREATE TABLE soycalendar_data_sets(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	class_name VARCHAR UNIQUE,
	object_data TEXT
);