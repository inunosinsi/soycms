<?php
SOY2::import("domain.cms.DataSets");
class StructuredDataUtil {

	const DATA_SET_KEY = "structured_data";

	/**
	 * @param int
	 * @return array(int, int)
	 */
	public static function getOldestEntryCdateAndLatestEntryUdate(int $blogLabelId){
		if($blogLabelId <= 0) return 0;
	
		$dao = soycms_get_hash_table_dao("entry");
		
		try{
			$res = $dao->executeQuery(
				"SELECT cdate, udate FROM Entry ".
				"WHERE id IN (".
					"SELECT entry_id FROM EntryLabel ".
					"WHERE label_id = :labelId".
				") ".
				"AND isPublished = ".Entry::ENTRY_ACTIVE. " ".
				"AND openPeriodStart <= :now ".
				"AND openPeriodEnd >= :now ".
				"ORDER BY cdate ASC",
				array(":labelId" => $blogLabelId, ":now" => time())
			);
		}catch(Exception $e){
			$res = array();					
		}
		if(!count($res)) return array(0, 0);

		$cdate = $res[0]["cdate"];

		// 最も新しいudate
		$udate = 0;
		foreach($res as $v){
			if($v["udate"] > $udate){
				$udate = $v["udate"];
			}
		}

		return array($cdate, $udate);
	}

	public static function getConfig(){
		return DataSets::get(self::DATA_SET_KEY, array(
			"author" => array()
		));
	}

	public static function saveConfig(array $posts){
		DataSets::put(self::DATA_SET_KEY, $posts);
	}
}
