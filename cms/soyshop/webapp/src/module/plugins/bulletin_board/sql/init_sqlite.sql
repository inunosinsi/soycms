CREATE TABLE soyboard_group (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR UNIQUE,
	display_order INTEGER NOT NULL DEFAULT 1000,
	is_disabled INTEGER NOT NULL DEFAULT 0,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
);

CREATE TABLE soyboard_topic (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	group_id INTEGER NOT NULL,
	label VARCHAR,
	create_date INTEGER NOT NULL
);

CREATE TABLE soyboard_post (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	topic_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	content TEXT,
	is_open INTEGER NOT NULL,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
);

CREATE TABLE soyboard_group_attribute (
	group_id INTEGER,
	group_field_id VARCHAR,
	group_value VARCHAR,
	group_extra_values VARCHAR,
	UNIQUE(group_id,group_field_id)
);
