create table EntryHistory(
	id                INTEGER UNSIGNED primary key AUTO_INCREMENT,
	entry_id          INTEGER UNSIGNED,
	title             varchar(255),
	content           TEXT,
	more              TEXT,
	additional        TEXT,
	is_published      TINYINT(1) NOT NULL DEFAULT 0,
	cdate             INTEGER UNSIGNED NOT NULL,
	author            varchar(255),
	user_id           INTEGER UNSIGNED,
	action_type       TINYINT(1) NOT NULL DEFAULT 0,
	action_target     INTEGER UNSIGNED,
	change_title      TINYINT(1) NOT NULL DEFAULT 0,
	change_content    TINYINT(1) NOT NULL DEFAULT 0,
	change_more       TINYINT(1) NOT NULL DEFAULT 0,
	change_additional TINYINT(1) NOT NULL DEFAULT 0
)ENGINE = InnoDB;
create index entry_history_entry_id on EntryHistory(entry_id);
