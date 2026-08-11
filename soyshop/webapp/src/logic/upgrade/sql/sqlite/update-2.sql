create table soyshop_auto_login(
	id integer primary key,
	user_id integer not null,
	session_token varchar not null,
	time_limit integer
);