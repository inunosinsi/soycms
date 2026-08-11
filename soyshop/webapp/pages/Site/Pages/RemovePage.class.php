<?php

class RemovePage extends WebPage{

    function __construct($args) {
		
		if(soy2_check_token() && isset($args[0]) && is_numeric($args[0])){
			$id = $args[0];
	    	try{
				SOY2Logic::createInstance("logic.site.page.PageRemoveLogic")->remove($id);
	    	}catch(Exception $e){
				//
	    	}

			SOYShopPlugin::load("soyshop.page.update");
			SOYShopPlugin::invoke("soyshop.page.update", array(
				"deletePageId" => $id
			));

			SOYShopCacheUtil::clearCache();
		}

    	SOY2PageController::jump("Site.Pages?deleted");
    	exit;
    }
}
