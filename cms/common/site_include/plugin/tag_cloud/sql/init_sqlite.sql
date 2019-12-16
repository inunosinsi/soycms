CREATE TABLE TagCloudDictionary(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	word VARCHAR UNIQUE
);

CREATE TABLE TagCloudLinking(
	entry_id INTEGER NOT NULL,
	word_id INTEGER NOT NULL,
	UNIQUE(entry_id, word_id)
);
