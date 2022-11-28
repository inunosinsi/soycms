create table soyshop_message_board(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	account_id INTEGER NOT NULL,
	message TEXT,
	create_date INTEGER NOT NULL
) ENGINE = Archive;