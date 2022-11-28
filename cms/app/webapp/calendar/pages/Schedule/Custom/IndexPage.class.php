<?php

class IndexPage extends WebPage{

    function __construct() {
    	parent::__construct();

    	$this->createAdd("custom_list","_common.CustomItemListComponent",array(
    		"list" => self::_getCustomItems()
    	));
    }

	private function _getCustomItems(){
		try{
    		return SOY2DAOFactory::create("SOYCalendar_CustomItemDAO")->get();
    	}catch(Exception $e){
    		return array();
    	}
	}
}
