create table soyshop_notice_arrival(
	id INTEGER primary key AUTO_INCREMENT,
	item_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	sended TINYINT NOT NULL DEFAULT 0,
	checked TINYINT NOT NULL DEFAULT 0,
	create_date INTEGER,
	update_date INTEGER,
	unique(item_id, user_id)
)ENGINE=InnoDB;
