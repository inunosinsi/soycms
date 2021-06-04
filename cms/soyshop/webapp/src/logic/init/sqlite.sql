drop table soyshop_page;
create table soyshop_page(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	uri VARCHAR NOT NULL UNIQUE,
	name VARCHAR,
	type VARCHAR NOT NULL,
	template VARCHAR,
	config VARCHAR,
	create_date INTEGER,
	update_date INTEGER
);

drop table soyshop_item;
create table soyshop_item(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	item_name VARCHAR,
	item_subtitle VARCHAR,
	item_code VARCHAR NOT NULL UNIQUE,
	item_alias VARCHAR NOT NULL UNIQUE,
	item_price INTEGER NOT NULL DEFAULT 0,
	item_sale_price INTEGER,
	item_purchase_price INTEGER,
	item_selling_price INTEGER,
	item_sale_flag INTEGER NOT NULL DEFAULT 0,
	item_stock INTEGER NOT NULL DEFAULT 0,
	item_unit VARCHAR,
	item_config VARCHAR,
	item_type VARCHAR NOT NULL,
	item_category INTEGER,
	create_date INTEGER NOT NULL DEFAULT 0,
	update_date INTEGER NOT NULL DEFAULT 0,
	order_period_start INTEGER NOT NULL DEFAULT 0,
	order_period_end INTEGER NOT NULL DEFAULT 0,
	open_period_start INTEGER NOT NULL DEFAULT 0,
	open_period_end INTEGER NOT NULL DEFAULT 0,
	detail_page_id INTEGER,
	item_is_open INTEGER NOT NULL DEFAULT 0,
	is_disabled INTEGER NOT NULL DEFAULT 0,
	UNIQUE(item_code, update_date)
);

drop table soyshop_item_attribute;
create table soyshop_item_attribute(
	item_id INTEGER NOT NULL,
	item_field_id VARCHAR NOT NULL,
	item_value VARCHAR NOT NULL,
	item_extra_values VARCHAR,
	UNIQUE(item_id,item_field_id)
);

drop table soyshop_category;
create table soyshop_category(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	category_name VARCHAR,
	category_alias VARCHAR UNIQUE,
	category_order INTEGER DEFAULT 0,
	category_parent INTEGER,
	category_config VARCHAR,
	category_is_open INTEGER DEFAULT 1
);

drop table soyshop_category_attribute;
create table soyshop_category_attribute(
	category_id INTEGER NOT NULL,
	category_field_id VARCHAR NOT NULL,
	category_value VARCHAR NOT NULL,
	category_value2 VARCHAR,
	UNIQUE(category_id,category_field_id)
);

drop table soyshop_order;
create table soyshop_order(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	order_date INTEGER NOT NULL,
	price INTEGER NOT NULL,
	order_status INTEGER NOT NULL,
	payment_status INTEGER NOT NULL,
	address VARCHAR,
	claimed_address VARCHAR,
	user_id INTEGER NOT NULL,
	attributes VARCHAR,
	modules VARCHAR,
	mail_status VARCHAR,
	tracking_number VARCHAR,
	UNIQUE(order_date, user_id)
);

drop table soyshop_orders;
create table soyshop_orders(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	order_id INTEGER NOT NULL,
	item_id INTEGER NOT NULL,
	item_count INTEGER NOT NULL,
	item_price INTEGER NOT NULL,
	total_price INTEGER NOT NULL,
	item_name INTEGER NOT NULL,
	status INTEGER NOT NULL DEFAULT 0,
	flag INTEGER NOT NULL DEFAULT 0,
	cdate INTEGER NOT NULL,
	is_sended INTEGER DEFAULT 0,
	attributes VARCHAR,
	is_addition INTEGER DEFAULT 0,
	is_confirm INTEGER DEFAULT 0,
	display_order INTEGER NOT NULL DEFAULT 0,
	UNIQUE(order_id, item_id, cdate)
);

drop table soyshop_plugins;
create table soyshop_plugins(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	plugin_id VARCHAR UNIQUE NOT NULL,
	plugin_type VARCHAR NOT NULL,
	config VARCHAR,
	display_order INTEGER DEFAULT 2147483647,
	is_active INTEGER NOT NULL DEFAULT 0
);

drop table soyshop_order_state_history;
create table soyshop_order_state_history(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	order_id INTEGER NOT NULL,
	order_date INTEGER NOT NULL,
	author VARCHAR,
	content VARCHAR,
	more VARCHAR,
	UNIQUE(order_id, order_date)
);

drop table soyshop_data_sets;
create table soyshop_data_sets(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	class_name VARCHAR UNIQUE,
	object_data text
);

drop table soyshop_user;
CREATE TABLE soyshop_user (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	mail_address VARCHAR UNIQUE,
	user_code VARCHAR UNIQUE,
	attribute1 VARCHAR,
	attribute2 VARCHAR,
	attribute3 VARCHAR,
	name VARCHAR,
	reading VARCHAR,
	honorific VARCHAR,
	nickname VARCHAR,
	account_id VARCHAR UNIQUE,
	profile_id VARCHAR UNIQUE,
	image_path VARCHAR,
	gender INTEGER,
	birthday VARCHAR,
	zip_code VARCHAR,
	area INTEGER,
	address1 VARCHAR,
	address2 VARCHAR,
	address3 VARCHAR,
	telephone_number VARCHAR,
	fax_number VARCHAR,
	cellphone_number VARCHAR,
	url VARCHAR,
	job_name VARCHAR,
	job_zip_code VARCHAR,
	job_area INTEGER,
	job_address1 VARCHAR,
	job_address2 VARCHAR,
	job_address3 VARCHAR,
	job_telephone_number VARCHAR,
	job_fax_number VARCHAR,
	memo VARCHAR,
	mail_error_count INTEGER NOT NULL DEFAULT 0,
	not_send INTEGER NOT NULL DEFAULT 0,
	is_error INTEGER NOT NULL DEFAULT 0,
	is_disabled INTEGER NOT NULL DEFAULT 0,
	is_publish INTEGER NOT NULL DEFAULT 1,
	is_profile_display INTEGER NOT NULL DEFAULT 0,
	register_date INTEGER,
	update_date INTEGER,
	real_register_date INTEGER,
	user_type INTEGER NOT NULL DEFAULT 10,
	address_list TEXT,
	password VARCHAR,
	attributes VARCHAR
);

create table soyshop_user_attribute(
	user_id INTEGER NOT NULL,
	user_field_id VARCHAR NOT NULL,
	user_value VARCHAR NOT NULL,
	UNIQUE(user_id,user_field_id)
);

drop table soyshop_auto_login;
create table soyshop_auto_login(
	user_id INTEGER NOT NULL,
	token VARCHAR NOT NULL,
	time_limit INTEGER,
	UNIQUE(user_id, token)
);

drop table soyshop_user_token;
create table soyshop_user_token(
	user_id INTEGER NOT NULL,
	token VARCHAR(255) NOT NULL,
	time_limit INTEGER NOT NULL,
	UNIQUE(user_id, token)
);

drop table soyshop_mail_address_token;
create table soyshop_mail_address_token(
	user_id INTEGER NOT NULL,
	new_mail_address VARCHAR(255) NOT NULL,
	token VARCHAR(255) NOT NULL,
	time_limit INTEGER NOT NULL,
	UNIQUE(user_id, token)
);

drop table soyshop_mail_log;
create table soyshop_mail_log(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	recipient text,
	order_id INTEGER,
	user_id INTEGER,
	title text,
	content text,
	is_success tinyint NOT NULL DEFAULT 0,
	send_date INTEGER NOT NULL,
	UNIQUE(order_id, user_id, send_date)
);

drop table soyshop_mypage_login_log;
create table soyshop_mypage_login_log(
	user_id INTEGER,
	log_date INTEGER NOT NULL,
	UNIQUE(user_id, log_date)
);

drop table soyshop_ban_ip_address;
create table soyshop_ban_ip_address(
	ip_address VARCHAR NOT NULL UNIQUE,
	plugin_id VARCHAR NOT NULL,
	log_date INTEGER
);

drop table soyshop_breadcrumb;
create table soyshop_breadcrumb(
	item_id INTEGER NOT NULL,
	page_id INTEGER NOT NULL,
	UNIQUE(item_id, page_id)
);

drop table soyshop_item_review;
create table soyshop_item_review(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER,
	nickname VARCHAR,
	title VARCHAR,
	content VARCHAR,
	image VARCHAR,
	movie VARCHAR,
	evaluation INTEGER,
	approval INTEGER,
	vote INTEGER,
	attributes VARCHAR,
	is_approved INTEGER NOT NULL,
	create_date INTEGER NOT NULL,
	update_date INTEGER,
	UNIQUE(item_id, user_id, create_date)
);

drop table soyshop_review_point;
create table soyshop_review_point(
	review_id INTEGER NOT NULL,
	point INTEGER NOT NULL DEFAULT 0
);

drop table soyshop_favorite_item;
create table soyshop_favorite_item(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	purchased INTEGER DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER,
	UNIQUE(item_id, user_id, purchased)
);

CREATE TABLE soyshop_memo(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	content TEXT,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
);
