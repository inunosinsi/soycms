create table soyshop_order_date_attribute(
	order_id INTEGER,
	order_field_id VARCHAR(255),
	order_value_1 INTEGER,
	order_value_2 INTEGER,
	unique(order_id,order_field_id)
) ENGINE=InnoDB;