<?php

class EntryFieldLogic extends SOY2LogicBase {

	/**
	 * @param int
	 * @return array
	 */
	function getEntriesByLabelId(int $labelId, int $lim=50){
		$entryDao = soycms_get_hash_table_dao("entry");
		$now = time();
		$sql = "SELECT ent.id, ent.title FROM Entry ent ".
				"INNER JOIN EntryLabel lab ".
				"ON ent.id = lab.entry_id ".
				"WHERE lab.label_id = " . $labelId . " ".
				"AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
				"AND ent.openPeriodStart < " . $now . " ".
				"AND ent.openPeriodEnd > " . $now . " ".
				"LIMIT " . $lim;

		try{
			return $entryDao->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}
	}

	/**
	 * @param int
	 * @return Entry
	 */
	function getEntryObjectOnlyTitleAndContentByEntryId(int $entryId){
		static $values, $dao;
		if(isset($values[$entryId])) return $values[$entryId];
		if(is_null($dao)) $dao = soycms_get_hash_table_dao("entry");
		$now = time();
		$sql = "SELECT id, title, more, content, cdate FROM Entry ".
				"WHERE id = :id ".
				"AND isPublished = " . Entry::ENTRY_ACTIVE . " ".
				"AND openPeriodStart < " . $now . " ".
				"AND openPeriodEnd > " . $now . " ".
				"LIMIT 1";
		try{
			$res = $dao->executeQuery($sql, array(":id" => $entryId));
		}catch(Exception $e){
			$res = array();
		}

		$values[$entryId] = (isset($res[0]["title"])) ? $dao->getObject($res[0]) : new Entry();
		return $values[$entryId];
	}
}
