create table LabelAttribute(
	label_id integer NOT NULL,
	label_field_id VARCHAR(255) NOT NULL,
	label_value TEXT,
	label_extra_values TEXT,
	unique(label_id, label_field_id)
) ENGINE=InnoDB;

create index LabelAttribute_label_id on LabelAttribute(label_id);
create index LabelAttribute_label_field_id on LabelAttribute(label_field_id);
