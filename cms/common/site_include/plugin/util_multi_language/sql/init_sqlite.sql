CREATE TABLE MultiLanguageLabelRelation(
	parent_label_id INTEGER NOT NULL,
	child_label_id INTEGER NOT NULL,
	lang INTEGER NOT NULL,
	UNIQUE(parent_label_id, child_label_id, lang)
);

CREATE TABLE MultiLanguageEntryRelation(
	parent_entry_id INTEGER NOT NULL,
	child_entry_id INTEGER NOT NULL,
	lang INTEGER NOT NULL,
	UNIQUE(parent_entry_id, child_entry_id, lang)
);