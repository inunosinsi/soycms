create table soyshop_order_date_attribute(
	order_id integer,
	order_field_id varchar,
	order_value_1 integer,
	order_value_2 integer,
	unique(order_id,order_field_id)
);