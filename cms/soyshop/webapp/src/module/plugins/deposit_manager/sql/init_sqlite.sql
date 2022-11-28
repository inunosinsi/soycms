CREATE TABLE soyshop_deposit_manager_subject(
	id integer primary key AUTOINCREMENT,
	subject VARCHAR UNIQUE,
	display_order INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE soyshop_deposit_manager_deposit(
	id integer primary key AUTOINCREMENT,
	user_id INTEGER NOT NULL,
	subject_id INTEGER NOT NULL,
	deposit_price INTEGER NOT NULL,
	deposit_date INTEGER NOT NULL,
	memo VARCHAR,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
);
