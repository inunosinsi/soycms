create table soyshop_download(
	id integer primary key AUTO_INCREMENT,
	order_id integer,
	item_id integer,
	user_id integer,
	file_name varchar(255),
	token varchar(255) unique,
	order_date integer not null,
	received_date integer,
	time_limit integer,
	count integer,
	unique(token)
) ENGINE = InnoDB;