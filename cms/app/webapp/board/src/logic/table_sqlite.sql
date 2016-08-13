drop table soyboard_thread;
CREATE TABLE soyboard_thread(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  page_id INTEGER,
  title VARCHAR(255) NOT NULL,
  owner VARCHAR(64) NOT NULL,
  cdate DATE NOT NULL,
  lastsubmitdate DATE NOT NULL,
  sort_date DATE NOT NULL,
  readonly INTEGER
);

drop table soyboard_response;
CREATE TABLE soyboard_response(
	thread_id INTEGER NOT NULL,
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(64) NOT NULL,
	email VARCHAR(64) NOT NULL,
	submitdate DATE NOT NULL,
	hash VARCHAR(32) NOT NULL,
	body VARCHAR(1024) NOT NULL,
	host VARCHAR(32) NOT NULL,
	response_id INTEGER NOT NULL DEFAULT 0
);

drop table soyboard_config;
CREATE TABLE soyboard_config(
	thread_id INTEGER PRIMARY KEY AUTOINCREMENT,
	show_host INTEGER NOT NULL DEFAULT 0,
	default_name VARCHAR(64) NOT NULL,
	is_stopped INTEGER NOT NULL DEFAULT 0,
	max_response INTEGER NOT NULL DEFAULT 1000,
	sage_accept INTEGER NOT NULL DEFAULT 1
);