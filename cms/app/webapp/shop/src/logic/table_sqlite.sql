CREATE TABLE soyshop_site (
  id INTEGER primary key AUTOINCREMENT,
  site_id VARCHAR unique,
  site_type number default 2,
  site_name VARCHAR,
  url VARCHAR,
  path VARCHAR,
  data_source_name VARCHAR(255),
  create_date INTEGER,
  update_date INTEGER
);