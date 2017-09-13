create table soyshop_user_group(
	id INTEGER primary key AUTO_INCREMENT,
	name VARCHAR(512),
	group_order INTEGER NOT NULL DEFAULT 0,
	is_disabled TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

create table soyshop_user_grouping(
	user_id INTEGER NOT NULL,
	group_id INTEGER NOT NULL,
	UNIQUE(user_id, group_id)
) ENGINE=InnoDB;

create table soyshop_user_group_custom_search(
	group_id INTEGER NOT NULL UNIQUE
) ENGINE=InnoDB;
