<?php

class SwitchPageUtil {

	const PERIOD_START = 0;
	const PERIOD_END = 2147483647;

	public static function getConfig(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get("switch_page.config", array());
	}

	public static function saveConfig($values){
		SOY2::import("domain.cms.DataSets");
		DataSets::put("switch_page.config", $values);
	}

	public static function getPageList(){
		$pages = SOY2DAOFactory::create("cms.PageDAO")->get();
		if(!count($pages)) return array();

		$list = array();
		foreach($pages as $page){
			$list[$page->getId()] = $page->getTitle();
		}

		return $list;
	}

	public static function getPageUriByPageId($pageId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.PageDAO");
		try{
			return $dao->getById($pageId)->getUri();
		}catch(Exception $e){
			return null;
		}
	}

	public static function convertDateStringToTimestamp($str, $mode){
		preg_match('/[\d]{4}-[\d]{1,2}-[\d]{1,2} [\d]{1,2}:[\d]{1,2}:[\d]{1,2}/', $str, $tmp);
		if(!isset($tmp[0])) return ($mode == "start") ? self::PERIOD_START : self::PERIOD_END;

		$divide = explode(" ", $tmp[0]);
		$dateStr = $divide[0];
		$timeStr = $divide[1];

		$dateArray = explode("-", $dateStr);
		$timeArray = explode(":", $timeStr);

		return mktime($timeArray[0], $timeArray[1], $timeArray[2], $dateArray[1], $dateArray[2], $dateArray[0]);
	}
}
