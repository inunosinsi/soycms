CREATE TABLE soyinquiry_comment (
	id INTEGER primary key AUTO_INCREMENT,
	inquiry_id INTEGER not null,
	title VARCHAR(512),
	author VARCHAR(512),
	content TEXT,
	create_date INTEGER
) TYPE = InnoDB;
