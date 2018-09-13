<?php

class IndexPage extends WebPage{

    function __construct() {
    	
    	$dao = SOY2DAOFactory::create("SOYCalendar_TitleDAO");
    	try{
    		$titles = $dao->get();
    	}catch(Exception $e){
    		$titles = array();
    	}
    	
    	parent::__construct();
    	
    	$this->createAdd("title_list","_common.TitleListComponent",array(
    		"list" => $titles
    	));	
    }
}
?>