<?php

class EntryFieldLogic extends SOY2LogicBase {

	function getEntriesByLabelId($labelId, $limit=20){
		$entryDao = SOY2DAOFactory::create("cms.EntryDAO");
		$now = time();
		$sql = "SELECT ent.id, ent.title FROM Entry ent ".
				"INNER JOIN EntryLabel lab ".
				"ON ent.id = lab.entry_id ".
				"WHERE lab.label_id = " . $labelId . " ".
				"AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
				"AND ent.openPeriodStart < " . $now . " ".
				"AND ent.openPeriodEnd > " . $now . " ".
				"LIMIT " . $limit;

		try{
			return $entryDao->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}
	}

	function getTitleAndContentByEntryId($entryId){
		static $values, $dao;
		if(isset($values[$entryId])) return $values[$entryId];
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryDAO");
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
