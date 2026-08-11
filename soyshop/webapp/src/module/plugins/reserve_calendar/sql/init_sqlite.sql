CREATE TABLE soyshop_reserve_calendar_schedule(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id INTEGER NOT NULL,
    label_id INTEGER NOT NULL,
	price INTEGER NOT NULL DEFAULT 0,
    year SMALLINT NOT NULL,
    month TINYINT NOT NULL,
    day TINYINT NOT NULL,
    unsold_seat TINYINT NOT NULL DEFAULT 1,
    UNIQUE(item_id, label_id, year, month, day)
);

CREATE TABLE soyshop_reserve_calendar_schedule_price(
	schedule_id INTEGER NOT NULL,
	label VARCHAR(128) NOT NULL,
	field_id VARCHAR NOT NULL,
	price INTEGER NOT NULL DEFAULT 0,
	UNIQUE(schedule_id, field_id, price)
);

CREATE TABLE soyshop_reserve_calendar_schedule_search(
	schedule_id INTEGER NOT NULL,
	schedule_date INTEGER NOT NULL,
	UNIQUE(schedule_id, schedule_date)
);

CREATE TABLE soyshop_reserve_calendar_reserve(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    schedule_id INTEGER NOT NULL,
    order_id INTEGER NOT NULL,
	seat INTEGER NOT NULL DEFAULT 1,
    token VARCHAR(25),
    temp TINYINT NOT NULL DEFAULT 0,
    temp_date INTEGER,
    reserve_date INTEGER,
	UNIQUE(schedule_id, order_id, reserve_date)
);

CREATE TABLE soyshop_reserve_calendar_cancel(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    schedule_id INTEGER NOT NULL,
    order_id INTEGER NOT NULL,
    cancel_date INTEGER,
	UNIQUE(schedule_id, order_id)
);

CREATE TABLE soyshop_reserve_calendar_label(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id INTEGER NOT NULL,
    label VARCHAR(52),
    display_order TINYINT NOT NULL,
	UNIQUE(item_id, label)
);
