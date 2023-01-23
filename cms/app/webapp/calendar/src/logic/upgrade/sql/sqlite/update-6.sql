CREATE TABLE soycalendar_custom_item_checked (
	item_id INTEGER NOT NULL,
	custom_id INTEGER NOT NULL,
	UNIQUE(item_id, custom_id)
);