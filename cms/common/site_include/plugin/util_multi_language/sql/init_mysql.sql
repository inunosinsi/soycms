CREATE TABLE MultiLanguageLabelRelation(
	parent_label_id INTEGER NOT NULL,
	child_label_id INTEGER NOT NULL,
	lang TINYINT NOT NULL,
	UNIQUE(parent_label_id, child_label_id, lang)
) ENGINE=InnoDB;

CREATE TABLE MultiLanguageEntryRelation(
	parent_entry_id INTEGER NOT NULL,
	child_entry_id INTEGER NOT NULL,
	lang TINYINT NOT NULL,
	UNIQUE(parent_entry_id, child_entry_id, lang)
) ENGINE=InnoDB;

