create table soyshop_download(
	id integer primary key AUTOINCREMENT,
	order_id integer,
	item_id integer,
	user_id integer,
	file_name varchar,
	token varchar unique,
	order_date integer,
	received_date integer,
	time_limit integer,
	count integer,
	unique(token)
);