create table Entry(
	id integer primary key AUTOINCREMENT,
	title varchar(255),
	alias varchar(255),
	content varchar(4000),
	more varchar(4000),
	cdate date,
	udate date,
	description varchar(4000),
	openPeriodStart date,
	openPeriodEnd date,
	isPublished int default 0,
	style varchar(4000),
	author varchar(255)
);

create table EntryHistory(
	id integer primary key AUTOINCREMENT,
	entry_id integer,
	title varchar,
	content varchar,
	more varchar,
	additional varchar,
	is_published int default 0,
	cdate date,
	author varchar,
	user_id int,
	action_type int,
	action_target int,
	change_title int default 0,
	change_content int default 0,
	change_more int default 0,
	change_additional int default 0,
	change_is_published int default 0
);
create index entry_history_entry_id on EntryHistory(entry_id);

create table EntryComment(
	id integer primary key AUTOINCREMENT,
	entry_id integer references Entry(id),
	title varchar(255),
	author varchar(255),
	body varchar(4000),
	is_approved integer default 0,
	mail_address varchar(255),
	url varchar(255),
	submitdate date
);

create table EntryTrackback(
	id integer primary key AUTOINCREMENT,
	entry_id integer references Entry(id),
	title varchar(255),
	url varchar(255),
	blog_name varchar(255),
	excerpt varchar(4000),
	submitdate date,
	certification number default 0
);

create table EntryAttribute(
	entry_id integer,
	entry_field_id varchar,
	entry_value varchar,
	entry_extra_values varchar,
	unique(entry_id,entry_field_id)
);

create index EntryAttribute_entry_id on EntryAttribute(entry_id);
create index EntryAttribute_entry_field_id on EntryAttribute(entry_field_id);

create table Label(
	id integer primary key AUTOINCREMENT,
	caption varchar(255),
	description varchar(4000),
	alias varchar(255),
	icon varchar(255),
	display_order INTEGER default 2147483647,
	color INTEGER default 0,
	background_color INTEGER default 16777215
);

create table EntryLabel(
	entry_id integer references Entry(id),
	label_id integer references Label(id),
	display_order integer default 2147483647,
	unique(entry_id,label_id)
);

create table Template(
	id integer primary key AUTOINCREMENT,
	name varchar(255),
	contents varchar(255),
	create_date date
);

create table Page(
	id integer primary key AUTOINCREMENT,
	title varchar(255),
	template text,
	uri varchar(255) unique,
	page_type integer default 0,
	page_config varvhar(255) default 0,
	openPeriodStart date,
	openPeriodEnd date,
	isPublished int default 0,
	isTrash int default 0,
	parent_page_id integer ,
	udate date,
	icon varchar(255)
);

create table TemplateHistory(
	id integer primary key AUTOINCREMENT,
	page_id integer references Page(id),
	contents varchar(4000),
	update_date date
);

create table Block(
	id integer primary key AUTOINCREMENT,
	soy_id varchar(255),
	page_id integer references Page(id),
	class varchar(255),
	object varchar(4000),
	unique(soy_id,page_id)
);

create table SiteConfig(
	name varchar(255),
	description varchar(255),
	siteConfig varchar(255),
	charset integer default 1
);

create index entry_udate on Entry(udate desc);

create table soycms_data_sets(
	id integer primary key AUTOINCREMENT,
	class_name varchar unique,
	object_data text
);
