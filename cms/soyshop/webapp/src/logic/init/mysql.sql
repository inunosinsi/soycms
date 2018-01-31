drop table soyshop_page;
create table soyshop_page(
	id integer primary key auto_increment,
	uri VARCHAR(255) not null unique,
	name VARCHAR(255),
	type VARCHAR(255) not null,
	template VARCHAR(255),
	config TEXT,
	create_date integer,
	update_date integer
) ENGINE=InnoDB;

drop table soyshop_item;
create table soyshop_item(
	id integer primary key auto_increment,
	item_name TEXT,
	item_code VARCHAR(255) unique,
	item_alias VARCHAR(255) unique,
	item_price integer,
	item_sale_price integer,
	item_selling_price integer,
	item_sale_flag integer default 0,
	item_stock integer default 0,
	item_unit VARCHAR(32),
	item_config TEXT,
	item_type VARCHAR(255),
	item_category integer,
	create_date integer,
	update_date integer,
	order_period_start integer,
	order_period_end integer,
	open_period_start integer,
	open_period_end integer,
	detail_page_id integer,
	item_is_open integer default 0,
	is_disabled INTEGER default 0
) ENGINE=InnoDB;

drop table soyshop_item_attribute;
create table soyshop_item_attribute(
	item_id integer,
	item_field_id VARCHAR(255),
	item_value TEXT,
	item_extra_values TEXT,
	unique(item_id,item_field_id)
) ENGINE=InnoDB;

drop table soyshop_category;
create table soyshop_category(
	id integer primary key auto_increment,
	category_name VARCHAR(255),
	category_alias VARCHAR(255) unique,
	category_order integer default 0,
	category_parent integer,
	category_config TEXT,
	category_is_open tinyint default 1
) ENGINE=InnoDB;

drop table soyshop_category_attribute;
create table soyshop_category_attribute(
	category_id integer,
	category_field_id VARCHAR(255),
	category_value TEXT,
	category_value2 TEXT,
	unique(category_id,category_field_id)
) ENGINE=InnoDB;

drop table soyshop_order;
create table soyshop_order(
	id integer primary key auto_increment,
	order_date integer not null,
	price integer not null,
	order_status integer not null,
	payment_status integer not null,
	address TEXT,
	claimed_address TEXT,
	user_id integer not null,
	attributes TEXT,
	modules TEXT,
	mail_status TEXT,
	tracking_number VARCHAR(255)
) ENGINE=InnoDB;

drop table soyshop_orders;
create table soyshop_orders(
	id integer primary key auto_increment,
	order_id integer not null,
	item_id integer not null,
	item_count integer not null,
	item_price integer not null,
	total_price integer not null,
	item_name TEXT not null,
	cdate integer not null,
	is_sended integer default 0,
	attributes TEXT,
	is_addition integer default 0,
	display_order tinyint not null default 0
) ENGINE=InnoDB;

drop table soyshop_plugins;
create table soyshop_plugins(
	id integer primary key auto_increment,
	plugin_id VARCHAR(255) unique not null,
	plugin_type VARCHAR(255) not null,
	config VARCHAR(255),
	display_order INTEGER default 2147483647,
	is_active integer not null default 0
) ENGINE=InnoDB;

drop table soyshop_order_state_history;
create table soyshop_order_state_history(
	id integer primary key auto_increment,
	order_id integer not null,
	order_date integer not null,
	author VARCHAR(255),
	content TEXT,
	more VARCHAR(255)
) ENGINE=InnoDB;

drop table soyshop_data_sets;
create table soyshop_data_sets(
	id integer primary key auto_increment,
	class_name VARCHAR(255) unique,
	object_data text
) ENGINE=InnoDB;

drop table soyshop_user;
CREATE TABLE soyshop_user (
	id INTEGER primary key auto_increment,
	mail_address VARCHAR(255) unique,
	attribute1 TEXT,
	attribute2 TEXT,
	attribute3 TEXT,
	name VARCHAR(255),
	reading VARCHAR(255),
	nickname VARCHAR(255),
	account_id VARCHAR(50) unique,
	profile_id VARCHAR(50) unique,
	image_path VARCHAR(255),
	gender VARCHAR(255),
	birthday VARCHAR(255),
	zip_code VARCHAR(255),
	area TINYINT,
	address1 VARCHAR(255),
	address2 VARCHAR(255),
	telephone_number VARCHAR(255),
	fax_number VARCHAR(255),
	cellphone_number VARCHAR(255),
	url VARCHAR(255),
	job_name VARCHAR(255),
	job_zip_code VARCHAR(255),
	job_area VARCHAR(255),
	job_address1 VARCHAR(255),
	job_address2 VARCHAR(255),
	job_telephone_number VARCHAR(255),
	job_fax_number VARCHAR(255),
	memo VARCHAR(255),
	mail_error_count INTEGER default 0,
	not_send TINYINT default 0,
	is_error TINYINT default 0,
	is_publish TINYINT default 1,
	is_disabled TINYINT default 0,
	is_profile_display TINYINT default 0,
	register_date INTEGER,
	update_date INTEGER,
	real_register_date INTEGER,
	user_type INTEGER,
	address_list TEXT,
	password TEXT,
	attributes TEXT
) ENGINE=InnoDB;

drop table soyshop_user_attribute;
create table soyshop_user_attribute(
	user_id integer,
	user_field_id VARCHAR(255),
	user_value TEXT,
	unique(user_id,user_field_id)
) ENGINE=InnoDB;

drop table soyshop_auto_login;
create table soyshop_auto_login(
	id integer primary key auto_increment,
	user_id integer not null,
	session_token CHAR(32) NOT NULL,
	time_limit integer
) ENGINE=InnoDB;

drop table soyshop_user_token;
create table soyshop_user_token(
	id integer primary key auto_increment,
	user_id integer not null,
	token varchar(255) not null,
	time_limit integer not null
) ENGINE=INNODB;

drop table soyshop_item_review;
create table soyshop_item_review(
	id integer primary key AUTO_INCREMENT,
	item_id integer not null,
	user_id integer,
	nickname varchar(255),
	title varchar(255),
	content varchar(255),
	image varchar(255),
	movie varchar(255),
	evaluation integer,
	approval integer,
	vote integer,
	attributes varchar(255),
	is_approved integer not null,
	create_date integer not null,
	update_date integer
) ENGINE = InnoDB;

create table soyshop_review_point(
	review_id integer not null,
	point integer not null default 0
)ENGINE = InnoDB;

drop table soyshop_breadcrumb;
create table soyshop_breadcrumb(
	item_id integer not null,
	page_id integer not null,
	unique(item_id, page_id)
) ENGINE=InnoDB;

drop table soyshop_mail_log;
create table soyshop_mail_log(
	id integer primary key AUTO_INCREMENT,
	recipient text,
	order_id integer,
	user_id integer,
	title text,
	content text,
	is_success tinyint not null default 0,
	send_date integer NOT NULL
) ENGINE=InnoDB;

create table soyshop_favorite_item(
	id INTEGER primary key AUTO_INCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	purchased TINYINT DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER
) ENGINE=InnoDB;
