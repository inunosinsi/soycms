create table soyshop_consumption_tax_schedule(
	id integer primary key AUTOINCREMENT,
	start_date INTERGER NOT NULL,
	tax_rate INTEGER NOT NULL DEFAULT 0
);