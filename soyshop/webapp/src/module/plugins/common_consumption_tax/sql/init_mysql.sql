create table soyshop_consumption_tax_schedule(
	id integer primary key AUTO_INCREMENT,
	start_date INTEGER NOT NULL,
	tax_rate INTEGER NOT NULL DEFAULT 0
) ENGINE=InnoDB;