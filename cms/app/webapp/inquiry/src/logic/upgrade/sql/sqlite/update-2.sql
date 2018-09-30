CREATE TABLE soyinquiry_comment (
	id INTEGER primary key,
	inquiry_id INTEGER not null,
	title VARCHAR,
	author VARCHAR,
	content VARCHAR,
	create_date INTEGER
)
