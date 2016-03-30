<?php

class RemovePage extends WebPage{

    function RemovePage($args) {

		if(soy2_check_token()){

	    	try{
		    	$id = $args[0];
				$logic = SOY2Logic::createInstance("logic.site.page.PageRemoveLogic");
		    	$logic->remove($id);
	    	}catch(Exception $e){

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
?>