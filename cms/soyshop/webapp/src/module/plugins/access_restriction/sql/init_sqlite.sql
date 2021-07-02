CREATE TABLE soyshop_access_restriction (
	ip_address VARCHAR NOT NULL,
	token VARCHAR NOT NULL,
	create_date INTEGER NOT NULL DEFAULT 0,
	UNIQUE(ip_address, token)
) ENGINE=InnoDB;
