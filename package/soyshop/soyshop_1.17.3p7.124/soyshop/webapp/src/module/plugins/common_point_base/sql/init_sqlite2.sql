create table soyshop_point_history(
	user_id integer not null,
	order_id integer,
	point INTEGER SIGNED not null default 0,
	content varchar,
	create_date integer not null
);