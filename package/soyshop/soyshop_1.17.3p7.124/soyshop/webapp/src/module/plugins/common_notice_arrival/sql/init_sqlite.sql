create table soyshop_notice_arrival(
	id INTEGER primary key AUTOINCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	sended INTEGER NOT NULL DEFAULT 0,
	checked INTEGER NOT NULL DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER
);