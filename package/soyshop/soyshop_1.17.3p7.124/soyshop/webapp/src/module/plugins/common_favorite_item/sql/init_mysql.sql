create table soyshop_favorite_item(
	id INTEGER primary key AUTO_INCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	purchased TINYINT DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER
) ENGINE=InnoDB;