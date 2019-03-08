CREATE TABLE soyshop_reserve_calendar_schedule(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	item_id INTEGER NOT NULL,
	label_id INTEGER NOT NULL,
	year SMALLINT NOT NULL,
	month TINYINT NOT NULL,
	day TINYINT NOT NULL,
	unsold_seat TINYINT NOT NULL DEFAULT 1,
	UNIQUE(item_id, label_id, year, month, day)
)ENGINE=InnoDB;

CREATE TABLE soyshop_reserve_calendar_reserve(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	schedule_id INTEGER NOT NULL,
	order_id INTEGER NOT NULL,
	token VARCHAR(25),
	temp TINYINT NOT NULL DEFAULT 0,
	temp_date INTEGER,
	reserve_date INTEGER,
	UNIQUE(schedule_id, order_id)
)ENGINE=InnoDB;

CREATE TABLE soyshop_reserve_calendar_cancel(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	schedule_id INTEGER NOT NULL,
	order_id INTEGER NOT NULL,
	cancel_date INTEGER,
	UNIQUE(schedule_id, order_id)
)ENGINE=InnoDB;

CREATE TABLE soyshop_reserve_calendar_label(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	item_id INTEGER NOT NULL,
	label VARCHAR(52),
	display_order TINYINT NOT NULL,
	UNIQUE(item_id, label)
)ENGINE=InnoDB;
