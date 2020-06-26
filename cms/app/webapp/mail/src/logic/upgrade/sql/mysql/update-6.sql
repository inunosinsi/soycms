CREATE TABLE soymail_reservation(
	id INTEGER primary key AUTO_INCREMENT,
	mail_id INTEGER NOT NULL,
	is_send INTEGER NOT NULL DEFAULT 0,
	offset INTEGER NOT NULL DEFAULT 0,
	reserve_date INTEGER NOT NULL,
	schedule_date INTEGER,
	send_date INTEGER
)ENGINE = InnoDB;
