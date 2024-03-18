CREATE TABLE soycms_multi_language_label_relation(
	parent_label_id INTEGER NOT NULL,
	child_label_id INTEGER NOT NULL,
	lang INTEGER NOT NULL,
	UNIQUE(parent_label_id, child_label_id, lang)
)