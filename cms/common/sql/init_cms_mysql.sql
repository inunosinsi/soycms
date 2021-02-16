CREATE TABLE Site (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  site_id VARCHAR(255) UNIQUE,
  site_type INTEGER default 1,
  site_name VARCHAR(255),
  isDomainRoot INTEGER default 0,
  url VARCHAR(255),
  path VARCHAR(255),
  data_source_name VARCHAR(255) UNIQUE
)ENGINE = InnoDB;

CREATE TABLE Administrator (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  user_id VARCHAR(255) NULL unique,
  user_password VARCHAR(255) NULL,
  default_user INTEGER default 0,
  name VARCHAR(255),
  email VARCHAR(255),
  token VARCHAR(255) UNIQUE,
  token_issued_date INTEGER
)ENGINE = InnoDB;

CREATE TABLE AdministratorAttribute (
	admin_id INTEGER NOT NULL,
	admin_field_id VARCHAR(255) NOT NULL,
	admin_value TEXT,
	unique(admin_id, admin_field_id)
)ENGINE = InnoDB;

CREATE TABLE SiteRole (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  user_id INTEGER,
  site_id INTEGER,
  is_limit INTEGER DEFAULT 0,
  UNIQUE (user_id,site_id)
)ENGINE = InnoDB;

CREATE TABLE AppRole (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  app_id VARCHAR(255),
  user_id INTEGER,
  app_role INTEGER,
  app_role_config TEXT,
  unique(user_id,app_id),
  FOREIGN KEY(user_id)
    REFERENCES Administrator(id)
)ENGINE = InnoDB;

CREATE TABLE soycms_admin_data_sets(
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  class_name VARCHAR(255) unique,
  object_data LONGTEXT
)ENGINE = InnoDB;

CREATE TABLE LoginErrorLog (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	ip VARCHAR(128) UNIQUE NOT NULL,
	count INTEGER NOT NULL DEFAULT 0,
	successed INTEGER NOT NULL DEFAULT 0,
	start_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
)ENGINE = InnoDB;

CREATE TABLE AutoLogin (
	user_id INTEGER NOT NULL,
	token CHAR(32) NOT NULL,
	time_limit INTEGER,
	UNIQUE(user_id, token)
)ENGINE = InnoDB;

CREATE TABLE Memo(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	content TEXT,
	create_date INTEGER NOT NULL,
	update_date INTEGER NOT NULL
) ENGINE=InnoDB;
