create table soyshop_user_token(
	user_id integer not null,
	token varchar(255) not null,
	time_limit integer not null,
	UNIQUE(user_id, token)
) ENGINE=INNODB;
