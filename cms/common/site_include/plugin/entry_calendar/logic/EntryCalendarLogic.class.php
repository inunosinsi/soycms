<?php

class EntryCalendarLogic extends SOY2LogicBase {

	private $entryDao;
	private $blogId;

	function __construct(){
		$this->entryDao = SOY2DAOFactory::create("cms.EntryDAO");
	}

	function getEntryList($year, $month){
		$start = mktime(0, 0, 0, $month, 1, $year);
		$end = mktime(0, 0, 0, $month + 1, 1, $year) - 1;

		$labelId = self::getLabel();
		if(is_null($labelId)) return array();

		$now = time();

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
			$res = $this->entryDao->executeQuery($sql, array(":labelId" => $labelId));
		}catch(Exception $e){
var_dump($e);
			return array();
		}

		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			if(!isset($v["id"]) || !isset($v["cdate"])) continue;
			$d = (int)date("j", $v["cdate"]);
			$list[$d][] = $this->entryDao->getObject($v);
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
				$res = $this->entryDao->executeQuery($sql, array(":id" => $this->blogId));
			}catch(Exception $e){
				$res = array();
			}

			if(isset($res[0])){
				$uri = $res[0]["uri"];
				$pageConfig = soy2_unserialize($res[0]["page_config"]);
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
