ALTER TABLE Administrator ADD COLUMN token VARCHAR(255);
CREATE UNIQUE INDEX tokenindex ON Administrator(token);