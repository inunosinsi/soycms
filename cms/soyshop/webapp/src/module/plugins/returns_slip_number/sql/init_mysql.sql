CREATE TABLE soyshop_returns_slip_number (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	slip_number VARCHAR(52) NOT NULL,
	order_id INTEGER NOT NULL,
	is_return TINYINT NOT NULL DEFAULT 0,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL,
	UNIQUE(slip_number, order_id)
) ENGINE=InnoDB;
