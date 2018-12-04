CREATE TABLE asp_app_pre_register(
	token VARCHAR(32) NOT NULL UNIQUE,
	data TEXT NOT NULL,
	create_date INTEGER NOT NULL
)ENGINE=InnoDB;
