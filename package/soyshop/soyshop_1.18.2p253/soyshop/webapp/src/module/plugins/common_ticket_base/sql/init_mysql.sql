CREATE TABLE soyshop_ticket(
	user_id INTEGER not null,
	count INTEGER default 0,
	update_date INTEGER,
	UNIQUE(user_id, count)
) ENGINE=InnoDB;

CREATE TABLE soyshop_ticket_history(
	user_id INTEGER not null,
	order_id INTEGER,
	count INTEGER SIGNED not null default 0,
	content VARCHAR(255),
	create_date INTEGER not null
) ENGINE=Archive;
