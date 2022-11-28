CREATE TABLE soyinquiry_entry_relation (
	inquiry_id INTEGER NOT NULL,
	entry_id INTEGER NOT NULL,
	UNIQUE(inquiry_id, entry_id)
)ENGINE = InnoDB;
