<?php

class EntryCalendarLogic extends SOY2LogicBase {

	private $blogId;

	function __construct(){}

	function getEntryList($year, $month){
		$start = mktime(0, 0, 0, $month, 1, $year);
		$end = mktime(0, 0, 0, $month + 1, 1, $year) - 1;

		$labelId = self::getLabel();
		if(is_null($labelId)) return array();

		$now = time();

		$dao = soycms_get_hash_table_dao("entry");
		$sql = "SELECT ent.* FROM Entry ent ".
				"INNER JOIN EntryLabel lab ".
				"ON ent.id = lab.entry_id ".
				"WHERE ent.cdate >= " . $start . " ".
				"AND ent.cdate <= " . $end . " ".
				"AND ent.openPeriodStart <= " . $now . " ".
				"AND ent.openPeriodEnd >= " . $now . " ".
				"AND ent.isPublished > 0 ".
				"AND lab.label_id = :labelId";

		try{
			$res = $dao->executeQuery($sql, array(":labelId" => $labelId));
		}catch(Exception $e){
			return array();
		}

		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			if(!isset($v["id"]) || !isset($v["cdate"])) continue;
			$d = (int)date("j", $v["cdate"]);
			$list[$d][] = soycms_set_entry_object($dao->getObject($v));
		}

		return $list;
	}

	private function getLabel(){
		list($uri, $pageConfig) = self::getPageUrlAndConfig();
		if(property_exists($pageConfig, "blogLabelId")){
			return (int)$pageConfig->blogLabelId;
		}else{
			return null;
		}
	}

	function getEntryPageUrl(){
		list($uri, $pageConfig) = self::getPageUrlAndConfig();
		if(property_exists($pageConfig, "entryPageUri")){
			if(strlen($uri)){
				return $uri . "/" . $pageConfig->entryPageUri;
			}else{
				return $pageConfig->entryPageUri;
			}
		}else{
			return "/";
		}
	}

	private function getPageUrlAndConfig(){
		static $uri, $pageConfig;
		if(is_null($pageConfig)){
			$sql = "SELECT uri, page_config FROM Page WHERE id = :id AND page_type = 200 AND isPublished = 1 AND openPeriodStart <= " . time() . " AND openPeriodEnd >= " . time() . " LIMIT 1";
			try{
				$res = soycms_get_hash_table_dao("entry")->executeQuery($sql, array(":id" => $this->blogId));
			}catch(Exception $e){
				$res = array();
			}

			if(isset($res[0])){
				$uri = $res[0]["uri"];
				$pageConfig = soy2_unserialize((string)$res[0]["page_config"]);
			}else{
				$pageConfig = new StdClass();
			}
		}

		return array($uri, $pageConfig);
	}

	function setBlogId($blogId){
		$this->blogId = $blogId;
	}
}
