CREATE TABLE stepmail_mail (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	mail_id VARCHAR(128) UNIQUE,
	title VARCHAR(256),
	overview VARCHAR(512),
	is_disabled TINYINT NOT NULL DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER
)ENGINE=InnoDB;

CREATE TABLE stepmail_step (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	mail_id INTEGER NOT NULL,
	title VARCHAR(256),
	overview VARCHAR(256),
	content TEXT,
	days_after TINYINT,
	is_disabled TINYINT NOT NULL DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER
)ENGINE=InnoDB;

CREATE TABLE stepmail_next_send (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	user_id INTEGER NOT NULL,
	mail_id INTEGER NOT NULL,
	step_id INTEGER NOT NULL,
	next_send_date INTEGER NOT NULL,
	is_sended TINYINT DEFAULT 0
)ENGINE=InnoDB;

CREATE TABLE stepmail_send_history (
	send_id INTEGER NOT NULL,
	send_date INTEGER
)ENGINE=InnoDB;

CREATE TABLE stepmail_data_sets (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	class_name VARCHAR(128) UNIQUE,
	value TEXT
)ENGINE=InnoDB;