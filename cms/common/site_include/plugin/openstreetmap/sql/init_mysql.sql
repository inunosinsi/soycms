CREATE TABLE OpenStreetMap(
	entry_id INTEGER NOT NULL DEFAULT 0 UNIQUE,
	latitude DOUBLE,
	longitude DOUBLE,
	label VARCHAR(32),
	icon VARCHAR(32)
)ENGINE=InnoDB;
