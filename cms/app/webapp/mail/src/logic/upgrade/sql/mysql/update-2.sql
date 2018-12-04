CREATE TABLE soy_mail_log(
	id INTEGER primary key AUTO_INCREMENT,
	log_time integer not null,
	content TEXT,
	more TEXT
)ENGINE = InnoDB;
