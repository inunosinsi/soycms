create table soyshop_stock_history(
	item_id integer not null,
	update_stock integer not null default 0,
	memo varchar,
	create_date integer not null
);