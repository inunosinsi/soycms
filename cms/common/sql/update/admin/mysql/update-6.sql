CREATE TABLE AutoLogin (
	user_id INTEGER NOT NULL,
	session_token CHAR(32) NOT NULL,
	time_limit INTEGER,
	UNIQUE(user_id, session_token)
)ENGINE = InnoDB;
