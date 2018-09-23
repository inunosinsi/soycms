create table soyshop_ban_ip_address(
	ip_address VARCHAR NOT NULL UNIQUE,
	plugin_id VARCHAR NOT NULL,
	log_date INTEGER
);
