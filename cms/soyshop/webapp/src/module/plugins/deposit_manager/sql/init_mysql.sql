CREATE TABLE soyshop_deposit_manager_subject(
	id integer primary key AUTO_INCREMENT,
	subject VARCHAR(256) UNIQUE,
	display_order TINYINT NOT NULL DEFAULT 0
)ENGINE=InnoDB;

CREATE TABLE soyshop_deposit_manager_deposit(
	id integer primary key AUTO_INCREMENT,
	user_id INTEGER NOT NULL,
	subject_id INTEGER NOT NULL,
	deposit_price INTEGER NOT NULL,
	deposit_date INTEGER NOT NULL,
	memo VARCHAR(512),
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
)ENGINE=InnoDB;
