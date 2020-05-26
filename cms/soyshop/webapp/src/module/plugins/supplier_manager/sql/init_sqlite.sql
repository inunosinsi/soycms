CREATE TABLE soyshop_supplier(
	id INTEGER primary key AUTOINCREMENT,
	name VARCHAR,
	zip_code VARCHAR,
	area INTEGER,
	address1 VARCHAR,
	address2 VARCHAR,
	telephone_number VARCHAR,
	fax_number VARCHAR,
	cellphone_number VARCHAR,
	mail_adress VARCHAR(255),
	url VARCHAR,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
);

CREATE TABLE soyshop_supplier_relation(
	supplier_id INTEGER NOT NULL,
	item_id INTEGER NOT NULL,
	UNIQUE(supplier_id, item_id)
);
