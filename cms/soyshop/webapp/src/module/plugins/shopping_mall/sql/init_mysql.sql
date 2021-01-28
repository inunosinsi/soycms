CREATE TABLE soymall_item_relation(
	item_id INTEGER NOT NULL,
	admin_id INTEGER NOT NULL,
	UNIQUE(item_id, admin_id)
)ENGINE=InnoDB;
