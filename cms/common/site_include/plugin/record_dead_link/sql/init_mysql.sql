create table RecordDeadLink(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	referer VARCHAR NOT NULL,
	url VARCHAR NOT NULL,
	register_date INTEGER
)ENGINE=ARCHIVE;