create table soyshop_list_price_log_when_order(
	item_order_id INTEGER NOT NULL,
	list_price DOUBLE(8,1) NOT NULL,
	UNIQUE(item_order_id, list_price)
)ENGINE=InnoDB;
