create table soyshop_point_history(
	user_id INTEGER not null,
	order_id INTEGER,
	point INTEGER SIGNED not null default 0,
	content VARCHAR(255),
	create_date INTEGER not null
) ENGINE=Archive;