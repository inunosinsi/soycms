<?php

class RemovePage extends WebPage{

    function __construct($args) {
		
		if(soy2_check_token()){
	    	try{
		    	$id = $args[0];
				SOY2Logic::createInstance("logic.site.page.PageRemoveLogic")->remove($id);
	    	}catch(Exception $e){
				//
	    	}
		}

		SOYShopPlugin::load("soyshop.page.update");
		SOYShopPlugin::invoke("soyshop.page.update", array(
			"deletePageId" => $id
		));

    	SOY2PageController::jump("Site.Pages?deleted");
    	exit;
    }
}
