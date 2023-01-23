<?php

class IndexPage extends WebPage{

    function __construct() {
    	parent::__construct();

    	$this->createAdd("title_list","_common.TitleListComponent",array(
    		"list" => self::_getTitles()
    	));
    }

	private function _getTitles(){
		try{
    		return SOY2DAOFactory::create("SOYCalendar_TitleDAO")->get();
    	}catch(Exception $e){
    		return array();
    	}
	}
}
