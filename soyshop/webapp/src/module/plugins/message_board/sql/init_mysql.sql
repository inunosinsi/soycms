create table soyshop_message_board(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	account_id INTEGER NOT NULL,
	message TEXT,
	create_date INTEGER NOT NULL
) ENGINE = Archive;