CREATE TABLE soyshop_inquiry (
	id INTEGER primary key AUTO_INCREMENT,
	tracking_number VARCHAR(20) UNIQUE,
	user_id INTEGER NOT NULL,
	mail_log_id INTEGER NOT NULL DEFAULT 0,
	requirement VARCHAR(255),
	content TEXT,
	is_confirm TINYINT NOT NULL DEFAULT 0,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL,
	UNIQUE(user_id, mail_log_id, is_confirm)
) ENGINE = InnoDB;
