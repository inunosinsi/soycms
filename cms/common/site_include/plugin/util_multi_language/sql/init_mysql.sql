CREATE TABLE MultiLanguageLabelRelation(
	parent_label_id INTEGER NOT NULL,
	child_label_id INTEGER NOT NULL,
	lang TINYINT NOT NULL,
	UNIQUE(parent_label_id, child_label_id, lang)
)