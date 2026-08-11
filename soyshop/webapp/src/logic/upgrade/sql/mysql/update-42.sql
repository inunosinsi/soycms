create table soyshop_ban_ip_address(
	ip_address VARCHAR(15) NOT NULL UNIQUE,
	plugin_id VARCHAR(52) NOT NULL,
	log_date INTEGER
) ENGINE=InnoDB;
