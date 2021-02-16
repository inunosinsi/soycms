create table soyshop_auto_login(
	user_id INTEGER NOT NULL UNIQUE,
	session_token CHAR(32) NOT NULL,
	time_limit INTEGER
) ENGINE=InnoDB;
