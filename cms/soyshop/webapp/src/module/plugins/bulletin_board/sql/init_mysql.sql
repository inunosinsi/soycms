CREATE TABLE soyboard_group (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) UNIQUE,
	display_order INTEGER NOT NULL DEFAULT 1000,
	is_disabled INTEGER NOT NULL DEFAULT 0,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
) ENGINE=InnoDB;

CREATE TABLE soyboard_topic (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	group_id INTEGER NOT NULL,
	label VARCHAR(255),
	create_date INTEGER NOT NULL
) ENGINE=InnoDB;

CREATE TABLE soyboard_post (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	topic_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	content TEXT,
	is_open INTEGER NOT NULL,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
) ENGINE=InnoDB;

CREATE TABLE soyboard_group_attribute (
	group_id INTEGER,
	group_field_id VARCHAR(255),
	groyp_value TEXT,
	group_extra_values TEXT,
	UNIQUE(group_id,group_field_id)
) ENGINE=InnoDB;
