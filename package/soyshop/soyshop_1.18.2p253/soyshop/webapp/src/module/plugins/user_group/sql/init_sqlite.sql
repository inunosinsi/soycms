create table soyshop_user_group(
	id INTEGER primary key AUTOINCREMENT,
	name VARCHAR(512),
	code VARCHAR(512),
	lat VARCHAR(32),
	lng VARCHAR(32),
	group_order INTEGER NOT NULL DEFAULT 0,
	is_disabled TINYINT NOT NULL DEFAULT 0
);

create table soyshop_user_grouping(
	user_id INTEGER NOT NULL,
	group_id INTEGER NOT NULL,
	UNIQUE(user_id, group_id)
);

create table soyshop_user_group_custom_search(
	group_id INTEGER NOT NULL UNIQUE
);
