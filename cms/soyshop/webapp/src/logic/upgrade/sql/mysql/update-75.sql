create table soyshop_mail_address_token(
	user_id integer not null,
	new_mail_address VARCHAR(255) not null,
	token varchar(255) not null,
	time_limit integer not null,
	UNIQUE(user_id, token)
) ENGINE=INNODB;
