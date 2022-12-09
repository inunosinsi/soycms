<?php

class RemovePage extends WebPage{

    function __construct($args) {   	
    	if(soy2_check_token()){
    		$id = (isset($args[0])) ? (int)$args[0] : 0;
	    	
	    	try{
	    		SOY2DAOFactory::create("SOYGallery_GalleryDAO")->deleteById($id);	    		
	    	}catch(Exception $e){
	    		//
	    	}

	    	CMSApplication::jump("Gallery");
    	}
    }
}