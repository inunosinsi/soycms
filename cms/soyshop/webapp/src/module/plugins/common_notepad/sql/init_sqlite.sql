CREATE TABLE soyshop_notepad(
	id INTEGER primary key AUTOINCREMENT,
	item_id INTEGER,
	category_id INTEGER,
	user_id INTEGER,
	title VARCHAR,
	content TEXT,
	create_date INTEGER,
	update_date INTEGER
);
