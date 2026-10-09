CREATE TABLE OpenStreetMap(
	entry_id INTEGER NOT NULL DEFAULT 0 UNIQUE,
	latitude REAL,
	longitude REAL,
	zoom INTEGER NOT NULL DEFAULT 17,
	label VARCHAR,
	icon VARCHAR
);
