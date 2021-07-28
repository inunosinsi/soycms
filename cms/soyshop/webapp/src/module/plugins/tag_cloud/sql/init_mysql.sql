CREATE TABLE soyshop_tag_cloud_dictionary(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	word VARCHAR(128) UNIQUE,
	hash CHAR(16) UNIQUE
) ENGINE=InnoDB;

CREATE TABLE soyshop_tag_cloud_linking(
	item_id INTEGER NOT NULL,
	word_id INTEGER NOT NULL,
	UNIQUE(item_id, word_id)
) ENGINE=InnoDB;
