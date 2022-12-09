create table soyshop_record_dead_link(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	referer VARCHAR NOT NULL,
	url VARCHAR NOT NULL,
	register_date INTEGER
)ENGINE=ARCHIVE;