CREATE TABLE GravatarAccount(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(128),
	mail_address VARCHAR(512) UNIQUE
);
