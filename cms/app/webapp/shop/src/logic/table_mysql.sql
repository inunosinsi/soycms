CREATE TABLE soyshop_site (
  id INTEGER primary key AUTO_INCREMENT,
  site_id VARCHAR(255) unique,
  site_type INTEGER default 2,
  site_name VARCHAR(255),
  url VARCHAR(255),
  path VARCHAR(255),
  data_source_name VARCHAR(255),
  create_date INTEGER,
  update_date INTEGER
)ENGINE = InnoDB;