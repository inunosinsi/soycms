create table soyshop_campaign(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	title VARCHAR,
	content TEXT,
	post_period_start INTEGER,
	post_period_end INTEGER,
	is_open TINYINT NOT NULL DEFAULT 0,
	is_logged_in TINYINT NOT NULL DEFAULT 0,
	is_disabled TINYINT NOT NULL DEFAULT 0,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
);