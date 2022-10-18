<?php

class CalendarAppUtil {

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
}
