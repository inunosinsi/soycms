CREATE TABLE soymail_mail (
  id INTEGER primary key AUTO_INCREMENT,
  title VARCHAR(255),
  sender_address VARCHAR(255),
  sender_name VARCHAR(255),
  return_address VARCHAR(255),
  return_name VARCHAR(255),
  mail_content LONGTEXT,
  selector TEXT,
  configure TEXT,
  schedule INTEGER,
  status INTEGER default 0,
  mail_count INTEGER default 0,
  create_date INTEGER,
  update_date INTEGER,
  send_date INTEGER,
  sended_date INTEGER
)ENGINE = InnoDB;

CREATE INDEX soymail_mail_status on soymail_mail(status);

CREATE TABLE soymail_errormail (
	id INTEGER primary key AUTO_INCREMENT,
	mail_id INTEGER not null,
	mail_content LONGTEXT,
	mail_address VARCHAR(255),
	receive_date integer
)ENGINE = InnoDB;

CREATE TABLE soymail_user (
  id INTEGER primary key AUTO_INCREMENT,
  mail_address VARCHAR(255) UNIQUE,
  attribute1 VARCHAR(255),
  attribute2 VARCHAR(255),
  attribute3 VARCHAR(255),
  name VARCHAR(255),
  reading VARCHAR(255),
  gender VARCHAR(255),
  birthday INTEGER,
  zip_code VARCHAR(255),
  area TINYINT,
  address1 VARCHAR(255),
  address2 VARCHAR(255),
  telephone_number VARCHAR(255),
  fax_number VARCHAR(255),
  cellphone_number VARCHAR(255),
  job_name VARCHAR(255),
  job_zip_code VARCHAR(255),
  job_area TINYINT,
  job_address1 VARCHAR(255),
  job_address2 VARCHAR(255),
  job_telephone_number VARCHAR(255),
  job_fax_number VARCHAR(255),
  memo TEXT,
  mail_error_count INTEGER,
  not_send TINYINT default 0,
  is_error TINYINT default 0,
  is_disabled TINYINT default 0,
  register_date INTEGER,
  update_date INTEGER
)ENGINE = InnoDB;

CREATE TABLE soymail_serverconfig(
	config LONGTEXT
)ENGINE = InnoDB;

CREATE TABLE soy_mail_log(
	id INTEGER primary key AUTO_INCREMENT,
	log_time integer not null,
	content TEXT,
	more TEXT
)ENGINE = InnoDB;

CREATE TABLE soymail_soyshop_connector (
	config TEXT
)ENGINE=InnoDB;

CREATE TABLE soymail_reservation(
	id INTEGER primary key AUTO_INCREMENT,
	mail_id INTEGER NOT NULL,
	is_send INTEGER NOT NULL DEFAULT 0,
	offset INTEGER NOT NULL DEFAULT 0,
	reserve_date INTEGER NOT NULL,
	schedule_date INTEGER,
	send_date INTEGER
)ENGINE = InnoDB;

CREATE TABLE soymail_data_sets(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	class_name VARCHAR(255) UNIQUE,
	object_data TEXT
) ENGINE=InnoDB;
