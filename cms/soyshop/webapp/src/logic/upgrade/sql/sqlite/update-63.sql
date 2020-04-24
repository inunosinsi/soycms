drop table soyshop_mail_address_token;
create table soyshop_mail_address_token(
	id integer primary key AUTOINCREMENT,
	user_id integer not null,
	new_mail_address VARCHAR(255) not null,
	token varchar(255) not null,
	time_limit integer not null
);
