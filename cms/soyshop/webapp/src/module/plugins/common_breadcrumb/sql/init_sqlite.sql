create table soyshop_breadcrumb(
	item_id integer not null,
	page_id integer not null,
	unique(item_id, page_id)
);