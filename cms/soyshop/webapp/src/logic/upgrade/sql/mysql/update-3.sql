ALTER TABLE soyshop_user ADD COLUMN user_type integer default 1;
ALTER TABLE soyshop_user ADD COLUMN real_register_date integer;

create table soyshop_user_token(
	id integer primary key auto_increment,
	user_id integer not null,
	token varchar(255) not null,
	time_limit integer not null
) ENGINE=INNODB default character set utf8;