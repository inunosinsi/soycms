create table EntryAttribute(
	entry_id integer,
	entry_field_id varchar,
	entry_value varchar,
	entry_extra_values varchar,
	unique(entry_id,entry_field_id)
);

create index EntryAttribute_entry_id on EntryAttribute(entry_id);
create index EntryAttribute_entry_field_id on EntryAttribute(entry_field_id);