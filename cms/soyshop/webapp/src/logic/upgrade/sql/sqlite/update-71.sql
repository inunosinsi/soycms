create table soyshop_auto_login(
	user_id INTEGER NOT NULL,
	token VARCHAR NOT NULL,
	time_limit INTEGER,
	UNIQUE(user_id, token)
);
