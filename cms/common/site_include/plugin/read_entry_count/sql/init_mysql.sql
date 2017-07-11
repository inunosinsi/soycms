create table ReadEntryCount(
	entry_id INTEGER NOT NULL UNIQUE,
	count INTEGER NOT NULL DEFAULT 0
) ENGINE=InnoDB;
