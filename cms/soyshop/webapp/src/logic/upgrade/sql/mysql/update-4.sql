create table soyshop_categories(
	id integer primary key auto_increment,
	item_id integer not null,
	category_id integer not null,
	attribute varchar(255)
) ENGINE=InnoDB default character set utf8;