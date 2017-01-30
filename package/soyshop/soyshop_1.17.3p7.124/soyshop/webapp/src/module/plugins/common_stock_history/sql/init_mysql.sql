create table soyshop_stock_history(
	item_id integer not null,
	memo varchar(512),
	create_date integer not null
)ENGINE = Archive;