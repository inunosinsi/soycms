CREATE TABLE soygallery_gallery (
	id integer primary key AUTO_INCREMENT,
	gallery_id varchar(255) unique,
	name varchar(255),
	memo varchar(512),
	config varchar(512),
	create_date integer not null,
	update_date integer not null
)ENGINE=InnoDB;

CREATE TABLE soygallery_image (
	id integer primary key AUTO_INCREMENT,
	filename varchar(255),
	gallery_id integer not null,
	url varchar(255),
	sort integer,
	memo varchar(512),
	attributes text,
	is_public integer not null,
	create_date integer not null,
	update_date integer not null
)ENGINE=InnoDB;

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
		g.name as name,
		g.config as config
	FROM soygallery_image i 
		LEFT JOIN soygallery_gallery g ON i.gallery_id = g.id
;