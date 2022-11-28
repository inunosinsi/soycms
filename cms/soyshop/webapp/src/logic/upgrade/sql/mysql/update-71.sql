create table soyshop_auto_login(
	user_id INTEGER NOT NULL,
	token CHAR(32) NOT NULL,
	time_limit INTEGER,
	UNIQUE(user_id, token)
) ENGINE=InnoDB;
