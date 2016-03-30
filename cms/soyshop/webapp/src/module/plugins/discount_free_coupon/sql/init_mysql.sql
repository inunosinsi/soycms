create table soyshop_coupon(
	id integer primary key AUTO_INCREMENT,
	coupon_code varchar(255) not null UNIQUE,
	coupon_type tinyint not null default 0,
	name varchar(255) not null,
	discount integer not null,
	discount_percent tinyint not null default 0,
	price_limit_min integer,
	price_limit_max integer,
	time_limit_start integer not null,
	time_limit_end integer not null,
	count integer not null,
	memo varchar(255),
	is_delete integer not null default 0,
	create_date integer,
	update_date integer
) ENGINE = InnoDB;

create table soyshop_coupon_history(
	user_id integer not null,
	coupon_id integer not null,
	order_id integer not null,
	private integer not null default 0,
	create_date integer not null
)ENGINE = InnoDB;