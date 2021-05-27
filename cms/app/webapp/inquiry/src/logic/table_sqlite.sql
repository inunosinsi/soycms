CREATE TABLE soyinquiry_form (
  id INTEGER primary key AUTOINCREMENT,
  form_id VARCHAR unique,
  name VARCHAR,
  config VARCHAR
);

CREATE TABLE soyinquiry_column(
  id INTEGER primary key AUTOINCREMENT,
  form_id VARCHAR,
  column_id VARCHAR,
  label VARCHAR,
  column_type VARCHAR,
  config VARCHAR,
  is_require INTEGER default 0,
  display_order INTEGER default 0
);

CREATE TABLE soyinquiry_inquiry (
  id INTEGER primary key AUTOINCREMENT,
  tracking_number VARCHAR,
  form_id VARCHAR NOT NULL,
  ip_address VARCHAR NOT NULL,
  content TEXT,
  data TEXT,
  flag INTEGER default 1,
  create_date INTEGER NOT NULL,
  form_url VARCHAR,
  UNIQUE(form_id, create_date)
);

CREATE TABLE soyinquiry_entry_relation (
	inquiry_id INTEGER NOT NULL,
	site_id INTEGER,
	page_id INTEGER,
	entry_id INTEGER NOT NULL,
	UNIQUE(inquiry_id, entry_id)
);

CREATE INDEX soyinquiry_tracking_number_idx on soyinquiry_inquiry(tracking_number);

CREATE TABLE soyinquiry_serverconfig(
	config VARCHAR
);

CREATE TABLE soyinquiry_comment (
	id INTEGER primary key AUTOINCREMENT,
	inquiry_id INTEGER not null,
	title VARCHAR,
	author VARCHAR,
	content VARCHAR,
	create_date INTEGER NOT NULL,
	UNIQUE(inquiry_id, create_date)
);

CREATE TABLE soyinquiry_data_sets(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	class_name VARCHAR UNIQUE,
	object_data TEXT
);

CREATE TABLE soyinquiry_ban_ip_address(
	ip_address VARCHAR NOT NULL UNIQUE,
	log_date INTEGER
);
