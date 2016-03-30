create table soyshop_order_attribute(
	order_id integer,
	order_field_id varchar,
	order_value1 varchar,
	order_value2 varchar,
	order_extra_values varchar,
	unique(order_id,order_field_id)
);