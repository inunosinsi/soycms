create table soyshop_coupon(
	id integer primary key AUTO_INCREMENT,
	coupon_code varchar(255) not null UNIQUE,
	coupon_type tinyint not null default 0,
	name varchar(255) not null,
	category_id INTEGER,
	discount integer not null,
	discount_percent tinyint not null default 0,
	is_free_delivery tinyint not null default 0,
	price_limit_min integer,
	price_limit_max integer,
	time_limit_start integer not null,
	time_limit_end integer not null,
	count integer not null,
	memo varchar(255),
	is_delete integer not null default 0,
	create_date integer not null,
	update_date integer not null
) ENGINE = InnoDB;

create table soyshop_coupon_category(
	id integer primary key AUTO_INCREMENT,
	category_name varchar(255) not null,
	coupon_code_prefix varchar(16) UNIQUE,
	create_date integer not null,
	update_date integer not null
)ENGINE = InnoDB;

create table soyshop_coupon_history(
	user_id integer not null,
	coupon_id integer not null,
	order_id integer not null,
	price integer not null default 0,
	is_free_delivery tinyint not null default 0,
	create_date integer not null
)ENGINE = InnoDB;
