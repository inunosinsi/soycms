CREATE TABLE soyshop_access_restriction (
	ip_address VARCHAR(40) NOT NULL,
	token VARCHAR(12) NOT NULL,
	create_date INTEGER NOT NULL DEFAULT 0,
	UNIQUE(ip_address, token)
) ENGINE=InnoDB;
