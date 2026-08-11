create table soyshop_order_attribute(
	order_id INTEGER,
	order_field_id VARCHAR(255),
	order_value1 TEXT,
	order_value2 TEXT,
	order_extra_values TEXT,
	unique(order_id,order_field_id)
) ENGINE=InnoDB;