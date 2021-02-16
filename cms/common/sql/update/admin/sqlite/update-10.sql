CREATE TABLE AutoLogin (
	user_id INTEGER NOT NULL,
	token CHAR(32) NOT NULL,
	time_limit INTEGER,
	UNIQUE(user_id, token)
);
