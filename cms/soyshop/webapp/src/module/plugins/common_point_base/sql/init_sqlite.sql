create table soyshop_point(
	user_id integer not null,
	point integer default 0,
	time_limit integer default null,
	create_date integer not null,
	update_date integer,
	UNIQUE(user_id, point)
);