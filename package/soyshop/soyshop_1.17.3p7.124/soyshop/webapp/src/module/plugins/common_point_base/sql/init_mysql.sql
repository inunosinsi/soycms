create table soyshop_point(
	user_id INTEGER not null,
	point INTEGER default 0,
	time_limit INTEGER default null,
	create_date INTEGER not null,
	update_date INTEGER,
	UNIQUE(user_id, point)
) ENGINE=InnoDB;