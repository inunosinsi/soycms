CREATE TABLE soymail_mail (
  id INTEGER primary key AUTOINCREMENT,
  title VARCHAR,
  sender_address VARCHAR,
  sender_name VARCHAR,
  return_address VARCHAR,
  return_name VARCHAR,
  mail_content VARCHAR,
  selector VARCHAR,
  configure VARCHAR,
  schedule VARCHAR,
  status number default 0,
  mail_count number default 0,
  create_date integer,
  update_date integer,
  send_date integer,
  sended_date integer
);

CREATE TABLE soymail_template (
  id INTEGER primary key AUTOINCREMENT,
  title VARCHAR,
  contents VARCHAR,
  display_order INTEGER default 10000
);

CREATE TABLE soymail_errormail (
	id INTEGER primary key AUTOINCREMENT,
	mail_id INTEGER not null,
	mail_content VARCHAR,
	mail_address VARCHAR,
	receive_date integer
);

CREATE TABLE soymail_user (
  id INTEGER primary key AUTOINCREMENT,
  mail_address VARCHAR unique,
  attribute1 VARCHAR,
  attribute2 VARCHAR,
  attribute3 VARCHAR,
  name VARCHAR,
  reading VARCHAR,
  gender VARCHAR,
  birthday int,
  zip_code VARCHAR,
  area INTEGER,
  address1 VARCHAR,
  address2 VARCHAR,
  telephone_number VARCHAR,
  fax_number VARCHAR,
  cellphone_number VARCHAR,
  job_name VARCHAR,
  job_zip_code VARCHAR,
  job_area INTEGER,
  job_address1 VARCHAR,
  job_address2 VARCHAR,
  job_telephone_number VARCHAR,
  job_fax_number VARCHAR,
  memo VARCHAR,
  mail_error_count VARCHAR,
  not_send INTEGER default 0,
  is_error INTEGER default 0,
  is_disabled INTEGER default 0,
  register_date INTEGER,
  update_date INTEGER
);

CREATE TABLE soymail_serverconfig(
	config VARCHAR
);

CREATE TABLE soy_mail_log(
	id INTEGER primary key AUTOINCREMENT,
	log_time integer not null,
	content VARCHAR,
	more VARCHAR
);

CREATE TABLE soymail_soyshop_connector (
	config TEXT
);

CREATE TABLE soymail_reservation(
	id INTEGER primary key AUTOINCREMENT,
	mail_id INTEGER NOT NULL,
	is_send INTEGER NOT NULL DEFAULT 0,
	offset INTEGER NOT NULL DEFAULT 0,
	reserve_date INTEGER NOT NULL,
	schedule_date INTEGER,
	send_date INTEGER
);

CREATE TABLE soymail_data_sets(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	class_name VARCHAR UNIQUE,
	object_data TEXT
);
