<?php

class RemovePage extends WebPage{

    function __construct($args) {

    	if(soy2_check_token()){
    		$id = (isset($args[0])) ? $args[0] : null;
	    	try{
	    		SOY2DAOFactory::create("SOYCalendar_TitleDAO")->deleteById($id);
	    	}catch(Exception $e){
	    		//
	    	}

	    	CMSApplication::jump("Title");
    	}
    }
}
