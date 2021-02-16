create table soyshop_auto_login(
	user_id INTEGER NOT NULL UNIQUE,
	session_token VARCHAR NOT NULL,
	time_limit INTEGER
);
