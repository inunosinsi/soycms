<?php

class CalendarAppUtil {

	const CUSTOM_ITEM_LABEL = 0;
	const CUSTOM_ITEM_ALIAS = 1;

	public static function getTitleList(){
		$titles = SOY2DAOFactory::create("SOYCalendar_TitleDAO")->get();
		if(!count($titles)) return array();

		$arr = array();
		foreach($titles as $title){
			$arr[$title->getId()] = $title->getTitle();
		}
		return $arr;
	}

	public static function getYearArray(){
    	$y = date("Y",time());
    	return array($y, $y+1);
    }

	/**
	 * 最も古い予定の年を取得する
	 * @return int
	 */
	public static function getFirstItemScheduleDateYear(){
		return SOY2DAOFactory::create("SOYCalendar_ItemDAO")->getFirstItemScheduleDateYear();
	}

	public static function getCustoms(int $mode=self::CUSTOM_ITEM_LABEL){
		try{
			$arr = SOY2DAOFactory::create("SOYCalendar_CustomItemDAO")->get();
		}catch(Exception $e){
			$arr = array();
		}
		if(!count($arr)) return array();

		$list = array();
		foreach($arr as $obj){
			switch($mode){
				case self::CUSTOM_ITEM_LABEL:
					$list[$obj->getId()] = htmlspecialchars($obj->getLabel(), ENT_QUOTES, "UTF-8");
					break;
				case self::CUSTOM_ITEM_ALIAS:
					$list[$obj->getId()] = htmlspecialchars($obj->getAlias(), ENT_QUOTES, "UTF-8");
					break;
			}
			
		}
		return $list;
	}
}
