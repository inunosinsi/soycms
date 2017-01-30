create table soyshop_item_review(
	id integer primary key AUTO_INCREMENT,
	item_id integer not null,
	user_id integer,
	nickname varchar(255),
	title varchar(255),
	content varchar(255),
	image varchar(255),
	movie varchar(255),
	evaluation integer,
	approval integer,
	vote integer,
	attributes varchar(255),
	is_approved integer not null,
	create_date integer not null,
	update_date integer
) ENGINE = InnoDB;

create table soyshop_review_point(
	review_id integer not null,
	point integer not null default 0
)ENGINE = InnoDB;
