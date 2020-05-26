CREATE TABLE soyshop_supplier(
	id INTEGER primary key AUTO_INCREMENT,
	name VARCHAR(256),
	zip_code VARCHAR(255),
	area TINYINT,
	address1 VARCHAR(255),
	address2 VARCHAR(255),
	telephone_number VARCHAR(255),
	fax_number VARCHAR(255),
	cellphone_number VARCHAR(255),
	mail_adress VARCHAR(255),
	url VARCHAR(255),
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
)ENGINE=InnoDB;

CREATE TABLE soyshop_supplier_relation(
	supplier_id INTEGER NOT NULL,
	item_id INTEGER NOT NULL,
	UNIQUE(supplier_id, item_id)
)ENGINE=InnoDB;
