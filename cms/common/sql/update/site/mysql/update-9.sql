ALTER TABLE TemplateHistory ADD column user_id INTEGER UNSIGNED AFTER page_id;
ALTER TABLE TemplateHistory ADD column author VARCHAR(255) AFTER page_id;
