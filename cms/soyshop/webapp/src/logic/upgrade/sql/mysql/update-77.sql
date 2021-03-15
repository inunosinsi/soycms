drop table soyshop_mypage_login_log;
create table soyshop_mypage_login_log(
	user_id INTEGER,
	log_date INTEGER NOT NULL,
	UNIQUE(user_id, log_date)
) ENGINE=InnoDB;
