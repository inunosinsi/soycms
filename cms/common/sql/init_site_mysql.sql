create table Entry(
	id integer primary key AUTO_INCREMENT,
	title varchar(255),
	alias varchar(255),
	content TEXT,
	more TEXT,
	cdate INTEGER,
	udate INTEGER,
	description TEXT,
	openPeriodStart INTEGER,
	openPeriodEnd INTEGER,
	isPublished int default 0,
	style TEXT,
	author varchar(255)
)ENGINE = InnoDB;

create table EntryHistory(
	id                INTEGER UNSIGNED primary key AUTO_INCREMENT,
	entry_id          INTEGER UNSIGNED,
	title             varchar(255),
	content           TEXT,
	more              TEXT,
	additional        TEXT,
	is_published      TINYINT(1) NOT NULL DEFAULT 0,
	cdate             INTEGER UNSIGNED NOT NULL,
	author            varchar(255),
	user_id           INTEGER UNSIGNED,
	action_type       TINYINT(1) NOT NULL DEFAULT 0,
	action_target     INTEGER UNSIGNED,
	change_title      TINYINT(1) NOT NULL DEFAULT 0,
	change_content    TINYINT(1) NOT NULL DEFAULT 0,
	change_more       TINYINT(1) NOT NULL DEFAULT 0,
	change_additional TINYINT(1) NOT NULL DEFAULT 0,
	change_is_published TINYINT(1) NOT NULL DEFAULT 0
)ENGINE = InnoDB;
create index entry_history_entry_id on EntryHistory(entry_id);

create table EntryComment(
	id integer primary key AUTO_INCREMENT,
	entry_id integer references Entry(id),
	title varchar(255),
	author varchar(255),
	body TEXT,
	is_approved integer default 0,
	mail_address varchar(255),
	url varchar(255),
	submitdate INTEGER
)ENGINE = InnoDB;

create table EntryTrackback(
	id integer primary key AUTO_INCREMENT,
	entry_id integer references Entry(id),
	title varchar(255),
	url TEXT,
	blog_name varchar(255),
	excerpt TEXT,
	submitdate INTEGER,
	certification INTEGER default 0
)ENGINE = InnoDB;

create table EntryAttribute(
	entry_id integer,
	entry_field_id VARCHAR(255),
	entry_value TEXT,
	entry_extra_values TEXT,
	unique(entry_id, entry_field_id)
) ENGINE=InnoDB;

create index EntryAttribute_entry_id on EntryAttribute(entry_id);
create index EntryAttribute_entry_field_id on EntryAttribute(entry_field_id);

create table Label(
	id integer primary key AUTO_INCREMENT,
	caption varchar(255),
	description TEXT,
	alias varchar(255),
	icon varchar(255),
	display_order INTEGER default 2147483647,
	color INTEGER default 0,
	background_color INTEGER default 16777215
)ENGINE = InnoDB;

create table EntryLabel(
	entry_id integer references Entry(id),
	label_id integer references Label(id),
	display_order integer default 2147483647,
	unique(entry_id,label_id)
)ENGINE = InnoDB;

create table Template(
	id integer primary key AUTO_INCREMENT,
	name varchar(255),
	contents varchar(255),
	create_date INTEGER
)ENGINE = InnoDB;

create table Page(
	id INTEGER primary key AUTO_INCREMENT,
	title varchar(255),
	template LONGTEXT,
	uri varchar(255) UNIQUE,
	page_type INTEGER default 0,
	page_config TEXT,
	openPeriodStart INTEGER,
	openPeriodEnd INTEGER,
	isPublished int default 0,
	isTrash int default 0,
	parent_page_id integer ,
	udate INTEGER,
	icon varchar(255)
)ENGINE = InnoDB;

create table TemplateHistory(
	id integer primary key AUTO_INCREMENT,
	page_id integer references Page(id),
	contents LONGTEXT,
	update_date INTEGER
)ENGINE = InnoDB;

create table Block(
	id integer primary key AUTO_INCREMENT,
	soy_id varchar(255),
	page_id integer references Page(id),
	class varchar(255),
	object TEXT,
	unique(soy_id,page_id)
)ENGINE = InnoDB;

create table SiteConfig(
	name varchar(255),
	description varchar(255),
	siteConfig varchar(255),
	charset integer default 1
)ENGINE = InnoDB;

create index entry_udate on Entry(udate desc);

create table soycms_data_sets(
	id integer primary key AUTO_INCREMENT,
	class_name varchar(255) unique,
	object_data LONGTEXT
);
