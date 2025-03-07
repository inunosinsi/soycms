CREATE TABLE GeminiKeyword(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	keyword_id INTEGER NOT NULL DEFAULT 0,
	hiragana_id INTEGER NOT NULL DEFAULT 0,
	katakana_id INTEGER NOT NULL DEFAULT 0,
	UNIQUE(keyword_id, hiragana_id, katakana_id)
)ENGINE=InnoDB;

CREATE TABLE GeminiKeywordDictionary(
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	keyword VARCHAR(128) NOT NULL UNIQUE
)ENGINE=InnoDB;

CREATE TABLE GeminiKeywordRelation(
	entry_id INTEGER NOT NULL DEFAULT 0,
	keyword_id INTEGER NOT NULL DEFAULT 0,
	importance INTEGER NOT NULL DEFAULT 1,
	UNIQUE(entry_id, keyword_id)
)ENGINE=InnoDB;


