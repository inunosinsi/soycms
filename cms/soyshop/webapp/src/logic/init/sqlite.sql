drop table soyshop_page;
create table soyshop_page(
	id integer primary key AUTOINCREMENT,
	uri varchar not null unique,
	name varchar,
	type varchar not null,
	template varchar,
	config varchar,
	create_date integer,
	update_date integer
);

drop table soyshop_item;
create table soyshop_item(
	id integer primary key AUTOINCREMENT,
	item_name varchar,
	item_code varchar unique,
	item_alias varchar unique,
	item_price integer,
	item_sale_price integer,
	item_selling_price integer,
	item_sale_flag integer default 0,
	item_stock integer default 0,
	item_config varchar,
	item_type varchar,
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
);

drop table soyshop_item_attribute;
create table soyshop_item_attribute(
	item_id integer,
	item_field_id varchar,
	item_value varchar,
	item_extra_values varchar,
	unique(item_id,item_field_id)
);

drop table soyshop_category;
create table soyshop_category(
	id integer primary key AUTOINCREMENT,
	category_name varchar,
	category_alias varchar unique,
	category_order integer default 0,
	category_parent integer,
	category_config varchar,
	category_is_open integer default 1
);

drop table soyshop_category_attribute;
create table soyshop_category_attribute(
	category_id integer,
	category_field_id varchar,
	category_value varchar,
	category_value2 varchar,
	unique(category_id,category_field_id)
);

drop table soyshop_order;
create table soyshop_order(
	id integer primary key AUTOINCREMENT,
	order_date integer not null,
	price integer not null,
	order_status integer not null,
	payment_status integer not null,
	address varchar,
	claimed_address varchar,
	user_id integer not null,
	attributes varchar,
	modules varchar,
	mail_status varchar,
	tracking_number varchar
);

drop table soyshop_orders;
create table soyshop_orders(
	id integer primary key AUTOINCREMENT,
	order_id integer not null,
	item_id integer not null,
	item_count integer not null,
	item_price integer not null,
	total_price integer not null,
	item_name integer not null,
	cdate integer not null,
	is_sended integer default 0,
	attributes varchar,
	is_addition integer default 0
);

drop table soyshop_plugins;
create table soyshop_plugins(
	id integer primary key AUTOINCREMENT,
	plugin_id varchar unique not null,
	plugin_type varchar not null,
	config varchar,
	display_order INTEGER default 2147483647,
	is_active integer not null default 0
);

drop table soyshop_order_state_history;
create table soyshop_order_state_history(
	id integer primary key AUTOINCREMENT,
	order_id integer not null,
	order_date integer not null,
	author varchar,
	content varchar,
	more varchar
);

drop table soyshop_data_sets;
create table soyshop_data_sets(
	id integer primary key AUTOINCREMENT,
	class_name varchar unique,
	object_data text
);

drop table soyshop_user;
CREATE TABLE soyshop_user (
	id INTEGER primary key AUTOINCREMENT,
	mail_address VARCHAR unique,
	attribute1 VARCHAR,
	attribute2 VARCHAR,
	attribute3 VARCHAR,
	name VARCHAR,
	reading VARCHAR,
	nickname VARCHAR,
	account_id VARCHAR unique,
	profile_id VARCHAR unique,
	image_path VARCHAR,
	gender VARCHAR,
	birthday int,
	zip_code VARCHAR,
	area INTEGER,
	address1 VARCHAR,
	address2 VARCHAR,
	telephone_number VARCHAR,
	fax_number VARCHAR,
	cellphone_number VARCHAR,
	url VARCHAR,
	job_name VARCHAR,
	job_zip_code VARCHAR,
	job_area INTEGER,
	job_address1 VARCHAR,
	job_address2 VARCHAR,
	job_telephone_number VARCHAR,
	job_fax_number VARCHAR,
	memo VARCHAR,
	mail_error_count INTEGER default 0,
	not_send INTEGER default 0,
	is_error INTEGER default 0,
	is_disabled INTEGER,
	is_profile_display INTEGER default 0,
	register_date INTEGER,
	update_date INTEGER,
	real_register_date INTEGER,
	user_type INTEGER default 1,
	address_list TEXT,
	password VARCHAR,
	attributes VARCHAR
);

create table soyshop_user_attribute(
	user_id integer,
	user_field_id varchar,
	user_value varchar,
	unique(user_id,user_field_id)
);

drop table soyshop_auto_login;
create table soyshop_auto_login(
	id integer primary key AUTOINCREMENT,
	user_id integer not null,
	session_token varchar not null,
	time_limit integer
);

drop table soyshop_user_token;
create table soyshop_user_token(
	id integer primary key AUTOINCREMENT,
	user_id integer not null,
	token varchar(255) not null,
	time_limit integer not null
);

drop table soyshop_item_review;
create table soyshop_item_review(
	id integer primary key AUTOINCREMENT,
	item_id integer not null,
	user_id integer,
	nickname varchar,
	title varchar,
	content varchar,
	image varchar,
	movie varchar,
	evaluation integer,
	approval integer,
	vote integer,
	attributes varchar,
	is_approved integer not null,
	create_date integer not null,
	update_date integer
);

create table soyshop_review_point(
	review_id integer not null,
	point integer not null default 0
);

drop table soyshop_breadcrumb;
create table soyshop_breadcrumb(
	item_id integer not null,
	page_id integer not null,
	unique(item_id, page_id)
);

drop table soyshop_mail_log;
create table soyshop_mail_log(
	id integer primary key AUTOINCREMENT,
	recipient text,
	order_id integer,
	user_id integer,
	title text,
	content text,
	is_success tinyint not null default 0,
	send_date integer NOT NULL
);

create table soyshop_favorite_item(
	id INTEGER primary key AUTOINCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	purchased INTEGER DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER
);