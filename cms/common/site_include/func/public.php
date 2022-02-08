<?php
/**
 * 記事一覧から記事IDの一覧を取得
 * @param array
 * @return array
 */
function soycms_get_entry_id_by_entries(array $entries){
	if(!count($entries)) return array();

	$ids = array();
	foreach($entries as $entry){
		$ids[] = (int)$entry->getId();
	}
	return $ids;
}