create table soyshop_favorite_item(
	id INTEGER primary key AUTOINCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	purchased INTEGER DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER
);