create table soyshop_categories(
	id integer primary key,
	item_id integer not null,
	category_id integer not null,
	attribute varchar
);