create table soyshop_auto_login(
	id integer primary key auto_increment,
	user_id integer not null,
	session_token CHAR(32) NOT NULL,
	time_limit integer
) ENGINE=InnoDB default character set utf8;