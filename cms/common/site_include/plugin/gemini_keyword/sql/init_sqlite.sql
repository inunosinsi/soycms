CREATE TABLE GeminiKeyword(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	keyword_id INTEGER NOT NULL DEFAULT 0,
	hiragana_id INTEGER NOT NULL DEFAULT 0,
	katakana_id INTEGER NOT NULL DEFAULT 0,
	UNIQUE(keyword_id, hiragana_id, katakana_id)
);

CREATE TABLE GeminiKeywordDictionary(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	keyword TEXT NOT NULL UNIQUE
);

CREATE TABLE GeminiKeywordRelation(
	entry_id INTEGER NOT NULL DEFAULT 0,
	keyword_id INTEGER NOT NULL DEFAULT 0,
	importance INTEGER NOT NULL DEFAULT 1,
	UNIQUE(entry_id, keyword_id)
);
