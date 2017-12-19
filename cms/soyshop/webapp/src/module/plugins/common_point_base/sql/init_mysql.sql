CREATE TABLE soyshop_point(
	user_id INTEGER not null,
	point INTEGER default 0,
	time_limit INTEGER default null,
	create_date INTEGER not null,
	update_date INTEGER,
	UNIQUE(user_id, point)
) ENGINE=InnoDB;

CREATE TABLE soyshop_point_history(
	user_id INTEGER not null,
	order_id INTEGER,
	point INTEGER SIGNED not null default 0,
	content VARCHAR(255),
	create_date INTEGER not null
) ENGINE=Archive;
