CREATE TABLE AdministratorAttribute (
	admin_id INTEGER NOT NULL,
	admin_field_id VARCHAR NOT NULL,
	admin_value TEXT,
	unique(admin_id, admin_field_id)
);
