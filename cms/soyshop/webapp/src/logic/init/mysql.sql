drop table soyshop_page;
create table soyshop_page(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	uri VARCHAR(255) NOT NULL UNIQUE,
	name VARCHAR(255),
	type VARCHAR(255) NOT NULL,
	template VARCHAR(255),
	config TEXT,
	create_date INTEGER,
	update_date INTEGER
) ENGINE=InnoDB;

drop table soyshop_item;
create table soyshop_item(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	item_name VARCHAR(512),
	item_subtitle VARCHAR(512),
	item_code VARCHAR(255) NOT NULL UNIQUE,
	item_alias VARCHAR(255) NOT NULL UNIQUE,
	item_price INTEGER NOT NULL DEFAULT 0,
	item_purchase_price INTEGER NOT NULL DEFAULT 0,
	item_sale_price INTEGER NOT NULL DEFAULT 0,
	item_selling_price INTEGER NOT NULL DEFAULT 0,
	item_sale_flag INTEGER NOT NULL DEFAULT 0,
	item_stock INTEGER NOT NULL DEFAULT 0,
	item_unit VARCHAR(32),
	item_config TEXT,
	item_type VARCHAR(255) NOT NULL,
	item_category INTEGER,
	create_date INTEGER NOT NULL DEFAULT 0,
	update_date INTEGER NOT NULL DEFAULT 0,
	order_period_start INTEGER NOT NULL DEFAULT 0,
	order_period_end INTEGER NOT NULL DEFAULT 0,
	open_period_start INTEGER NOT NULL DEFAULT 0,
	open_period_end INTEGER NOT NULL DEFAULT 0,
	detail_page_id INTEGER,
	item_is_open TINYINT NOT NULL DEFAULT 0,
	is_disabled TINYINT NOT NULL DEFAULT 0,
	UNIQUE(item_code, update_date)
) ENGINE=InnoDB;

drop table soyshop_item_attribute;
create table soyshop_item_attribute(
	item_id INTEGER NOT NULL,
	item_field_id VARCHAR(255) NOT NULL,
	item_value TEXT NOT NULL,
	item_extra_values TEXT,
	UNIQUE(item_id,item_field_id)
) ENGINE=InnoDB;

drop table soyshop_category;
create table soyshop_category(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	category_name VARCHAR(255),
	category_alias VARCHAR(255) NOT NULL UNIQUE,
	category_order INTEGER NOT NULL DEFAULT 0,
	category_parent INTEGER,
	category_config TEXT,
	category_is_open TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

drop table soyshop_category_attribute;
create table soyshop_category_attribute(
	category_id INTEGER NOT NULL,
	category_field_id VARCHAR(255) NOT NULL,
	category_value TEXT NOT NULL,
	category_value2 TEXT,
	UNIQUE(category_id,category_field_id)
) ENGINE=InnoDB;

drop table soyshop_order;
create table soyshop_order(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	order_date INTEGER NOT NULL,
	price INTEGER NOT NULL,
	order_status TINYINT NOT NULL,
	payment_status TINYINT NOT NULL,
	address TEXT,
	claimed_address TEXT,
	user_id INTEGER NOT NULL,
	attributes TEXT,
	modules TEXT,
	mail_status TEXT,
	tracking_number VARCHAR(255),
	UNIQUE(order_date, user_id)
) ENGINE=InnoDB;

drop table soyshop_orders;
create table soyshop_orders(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	order_id INTEGER NOT NULL,
	item_id INTEGER NOT NULL,
	item_count INTEGER NOT NULL,
	item_price INTEGER NOT NULL,
	total_price INTEGER NOT NULL,
	item_name TEXT NOT NULL,
	status TINYINT NOT NULL DEFAULT 0,
	flag TINYINT NOT NULL DEFAULT 0,
	cdate INTEGER NOT NULL,
	is_sended TINYINT DEFAULT 0,
	attributes TEXT,
	is_addition TINYINT DEFAULT 0,
	is_confirm TINYINT DEFAULT 0,
	display_order TINYINT NOT NULL DEFAULT 0,
	UNIQUE(order_id, item_id, cdate)
) ENGINE=InnoDB;

drop table soyshop_plugins;
create table soyshop_plugins(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	plugin_id VARCHAR(255) UNIQUE NOT NULL,
	plugin_type VARCHAR(255) NOT NULL,
	config VARCHAR(255),
	display_order INTEGER DEFAULT 2147483647,
	is_active TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

drop table soyshop_order_state_history;
create table soyshop_order_state_history(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	order_id INTEGER NOT NULL,
	order_date INTEGER NOT NULL,
	author VARCHAR(255),
	content TEXT,
	more VARCHAR(255),
	UNIQUE(order_id, order_date)
) ENGINE=InnoDB;

drop table soyshop_data_sets;
create table soyshop_data_sets(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	class_name VARCHAR(255) UNIQUE,
	object_data text
) ENGINE=InnoDB;

drop table soyshop_user;
create table soyshop_user (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	mail_address VARCHAR(255) NOT NULL UNIQUE,
	user_code VARCHAR(54) UNIQUE,
	attribute1 TEXT,
	attribute2 TEXT,
	attribute3 TEXT,
	name VARCHAR(255),
	reading VARCHAR(255),
	honorific VARCHAR(32),
	nickname VARCHAR(255),
	account_id VARCHAR(50) UNIQUE,
	profile_id VARCHAR(50) UNIQUE,
	image_path VARCHAR(255),
	gender TINYINT,
	birthday VARCHAR(255),
	zip_code VARCHAR(10),
	area TINYINT,
	address1 VARCHAR(255),
	address2 VARCHAR(255),
	address3 VARCHAR(255),
	telephone_number VARCHAR(255),
	fax_number VARCHAR(255),
	cellphone_number VARCHAR(255),
	url VARCHAR(255),
	job_name VARCHAR(255),
	job_zip_code VARCHAR(255),
	job_area VARCHAR(255),
	job_address1 VARCHAR(255),
	job_address2 VARCHAR(255),
	job_address3 VARCHAR(255),
	job_telephone_number VARCHAR(255),
	job_fax_number VARCHAR(255),
	memo VARCHAR(255),
	mail_error_count INTEGER NOT NULL DEFAULT 0,
	not_send TINYINT NOT NULL DEFAULT 0,
	is_error TINYINT NOT NULL DEFAULT 0,
	is_publish TINYINT NOT NULL DEFAULT 1,
	is_disabled TINYINT NOT NULL DEFAULT 0,
	is_profile_display TINYINT NOT NULL DEFAULT 0,
	register_date INTEGER NOT NULL DEFAULT 0,
	update_date INTEGER NOT NULL DEFAULT 0,
	real_register_date INTEGER,
	user_type INTEGER NOT NULL DEFAULT 10,
	address_list TEXT,
	password TEXT,
	attributes TEXT
) ENGINE=InnoDB;

drop table soyshop_user_attribute;
create table soyshop_user_attribute(
	user_id INTEGER NOT NULL,
	user_field_id VARCHAR(255) NOT NULL,
	user_value TEXT NOT NULL,
	UNIQUE(user_id,user_field_id)
) ENGINE=InnoDB;

drop table soyshop_auto_login;
create table soyshop_auto_login(
	user_id INTEGER NOT NULL,
	token CHAR(32) NOT NULL,
	time_limit INTEGER,
	UNIQUE(user_id, token)
) ENGINE=InnoDB;

drop table soyshop_user_token;
create table soyshop_user_token(
	user_id INTEGER NOT NULL,
	token varchar(255) NOT NULL,
	time_limit INTEGER NOT NULL,
	UNIQUE(user_id, token)
) ENGINE=INNODB;

drop table soyshop_mail_address_token;
create table soyshop_mail_address_token(
	user_id INTEGER NOT NULL,
	new_mail_address VARCHAR(255) NOT NULL,
	token varchar(255) NOT NULL,
	time_limit INTEGER NOT NULL,
	UNIQUE(user_id, token)
) ENGINE=INNODB;

drop table soyshop_mail_log;
create table soyshop_mail_log(
	id INTEGER primary key AUTO_INCREMENT,
	recipient text,
	order_id INTEGER,
	user_id INTEGER,
	title text,
	content text,
	is_success TINYINT NOT NULL DEFAULT 0,
	send_date INTEGER NOT NULL,
	UNIQUE(order_id, user_id, send_date)
) ENGINE=InnoDB;

drop table soyshop_mypage_login_log;
create table soyshop_mypage_login_log(
	user_id INTEGER,
	log_date INTEGER NOT NULL,
	UNIQUE(user_id, log_date)
) ENGINE=InnoDB;

drop table soyshop_ban_ip_address;
create table soyshop_ban_ip_address(
	ip_address VARCHAR(40) NOT NULL UNIQUE,
	plugin_id VARCHAR(52) NOT NULL,
	log_date INTEGER
) ENGINE=InnoDB;

drop table soyshop_breadcrumb;
create table soyshop_breadcrumb(
	item_id INTEGER NOT NULL,
	page_id INTEGER NOT NULL,
	UNIQUE(item_id, page_id)
) ENGINE=InnoDB;

drop table soyshop_item_review;
create table soyshop_item_review(
	id INTEGER primary key AUTO_INCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER,
	nickname varchar(255),
	title varchar(255),
	content varchar(255),
	image varchar(255),
	movie varchar(255),
	evaluation INTEGER,
	approval INTEGER,
	vote INTEGER,
	attributes varchar(255),
	is_approved INTEGER NOT NULL,
	create_date INTEGER NOT NULL,
	update_date INTEGER,
	UNIQUE(item_id, user_id, create_date)
) ENGINE = InnoDB;

drop table soyshop_review_point;
create table soyshop_review_point(
	review_id INTEGER NOT NULL,
	point INTEGER NOT NULL DEFAULT 0
)ENGINE = InnoDB;

drop table soyshop_favorite_item;
create table soyshop_favorite_item(
	id INTEGER primary key AUTO_INCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	purchased TINYINT DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER,
	UNIQUE(item_id, user_id, purchased)
) ENGINE=InnoDB;

CREATE TABLE soyshop_memo(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	content TEXT,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
) ENGINE=InnoDB;
