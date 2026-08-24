CREATE TABLE OpenStreetMap(
	entry_id INTEGER NOT NULL DEFAULT 0 UNIQUE,
	latitude REAL,
	longitude REAL,
	label VARCHAR,
	icon VARCHAR
);
