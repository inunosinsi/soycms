create table PageAttribute(
	page_id integer NOT NULL,
	page_field_id VARCHAR(255) NOT NULL,
	page_value TEXT,
	page_extra_values TEXT,
	unique(page_id, page_field_id)
) ENGINE=InnoDB;

create index PageAttribute_label_id on PageAttribute(page_id);
create index PageAttribute_label_field_id on PageAttribute(page_field_id);
