create table soyshop_custom_search(
    item_id INTEGER NOT NULL,
    lang INTEGER NOT NULL DEFAULT 0,
    UNIQUE(item_id, lang)
);

create table soyshop_category_custom_search(
  category_id INTEGER NOT NULL,
  lang INTEGER NOT NULL DEFAULT 0,
  UNIQUE(category_id, lang)
);
