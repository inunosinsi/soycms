CREATE TABLE soyshop_point(
	user_id integer not null,
	point integer default 0,
	time_limit integer default null,
	create_date integer not null,
	update_date integer,
	UNIQUE(user_id, point)
);

CREATE TABLE soyshop_point_history(
	user_id integer not null,
	order_id integer,
	point INTEGER SIGNED not null default 0,
	content varchar,
	create_date integer not null
);
