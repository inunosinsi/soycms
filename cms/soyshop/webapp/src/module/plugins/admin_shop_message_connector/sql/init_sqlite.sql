CREATE TABLE soymessage_post(
	id INTEGER PRIMARY KEY,
	user_id INTEGER NOT NULL,
	tracking_number VARCHAR NOT NULL,
	owner_name VARCHAR,
	content VARCHAR,
	image VARCHAR,
	bookmark INTEGER NOT NULL DEFAULT 0,
	sort INTEGER,
	read_flag INTEGER NOT NULL DEFAULT 0,
	read_flag_admin INTEGER NOT NULL DEFAULT 0,
	return_flag INTEGEE NOT NULL DEFAULT 0,
	reply_id INTEGER,
	is_disabled INTEGER NOT NULL DEFAULT 0,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
);