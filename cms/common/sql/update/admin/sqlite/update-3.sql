ALTER TABLE Administrator ADD COLUMN token VARCHAR;
CREATE UNIQUE INDEX tokenindex ON Administrator(token);