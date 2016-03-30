<?php

class CreateControllerPage extends SOYShopWebPage{

    function CreateControllerPage($args) {
    	
    	if(soy2_check_token()){
    		$id = (isset($args[0])) ? (int)$args[0] : null;
	    	
	    	$dao = SOY2DAOFactory::create("SOYShop_SiteDAO");
	    	try{
	    		$shopSite = $dao->getById($id);
	    	}catch(Exception $e){
	    		return;
	    	}
	    	
	    	$logic = SOY2Logic::createInstance("logic.ShopLogic");
	    	$site = $logic->getSite($shopSite->getSiteId());
	    	
	    	$res = $logic->createSOYShopController($site);
	    	
	    	if($res){
				CMSApplication::jump("Site.Detail." . $id . "?created");
			}else{
				CMSApplication::jump("Site.Detail." . $id . "?error");
			}
    	}
    }
}
?>