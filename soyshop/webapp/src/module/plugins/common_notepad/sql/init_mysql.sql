create table soyshop_notepad(
	id INTEGER primary key AUTO_INCREMENT,
	item_id INTEGER,
	category_id INTEGER,
	user_id INTEGER,
	title VARCHAR(512),
	content TEXT,
	create_date INTEGER,
	update_date INTEGER
)ENGINE=InnoDB;
