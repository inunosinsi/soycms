create table EntryHistory(
	id integer primary key,
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
	change_additional int default 0
);
create index entry_history_entry_id on EntryHistory(entry_id);
