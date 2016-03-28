create table RecordDeadLink(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	referer VARCHAR NOT NULL,
	url VARCHAR NOT NULL,
	register_date INTEGER
);