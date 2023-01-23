CREATE TABLE soycalendar_item (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	schedule_date INTEGER,
	title_id INTEGER,
	start VARCHAR,
	end VARCHAR,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL,
	UNIQUE(schedule_date, title_id)
);

CREATE TABLE soycalendar_title (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	title VARCHAR,
	attribute VARCHAR,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
);

CREATE TABLE soycalendar_custom_item (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	label VARCHAR NOT NULL,
	alias VARCHAR NOT NULL,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
);

CREATE TABLE soycalendar_custom_item_checked (
	item_id INTEGER NOT NULL,
	custom_id INTEGER NOT NULL,
	UNIQUE(item_id, custom_id)
);

CREATE TABLE soycalendar_data_sets(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	class_name VARCHAR UNIQUE,
	object_data TEXT
);