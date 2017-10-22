CREATE TABLE soygallery_gallery (
	id integer primary key AUTOINCREMENT,
	gallery_id varchar unique,
	name varchar,
	memo varchar,
	config varchar,
	create_date integer not null,
	update_date integer not null
);

CREATE TABLE soygallery_image (
	id integer primary key AUTOINCREMENT,
	filename varchar,
	gallery_id integer not null,
	url varchar(255),
	sort integer,
	memo varchar,
	attributes text,
	is_public integer not null,
	create_date integer not null,
	update_date integer not null
);

CREATE VIEW soygallery_image_view AS
	SELECT
		i.id as id,
		i.filename as filename,
		i.url as url,
		i.sort as sort,
		i.memo as memo,
		i.attributes as attributes,
		i.is_public as is_public,
		i.create_date as create_date,
		i.update_date as update_date,
		g.id as g_id,
		g.gallery_id as gallery_id,
		g.name as name
	FROM soygallery_image i 
		LEFT JOIN soygallery_gallery g ON i.gallery_id = g.id
;