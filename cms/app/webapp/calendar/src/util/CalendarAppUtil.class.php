<?php

class CalendarAppUtil {

	public static function getTitleList(){
		$titles = SOY2DAOFactory::create("SOYCalendar_TitleDAO")->get();
		if(!count($titles)) return array();

		$array = array();
		foreach($titles as $title){
			$array[$title->getId()] = $title->getTitle();
		}
		return $array;
	}

	public static function getYearArray(){
    	$year = date("Y",time());

    	$array = array();
    	$array[] = $year;
    	$array[] = $year+1;

    	return $array;
    }
}
