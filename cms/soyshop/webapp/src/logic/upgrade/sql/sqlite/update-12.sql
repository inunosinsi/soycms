create table soyshop_mail_log(
	id integer primary key,
	recipient text,
	order_id integer,
	user_id integer,
	title text,
	content text,
	is_success tinyint not null default 0,
	send_date integer NOT NULL
);