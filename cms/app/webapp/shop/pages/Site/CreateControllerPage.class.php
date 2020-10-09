<?php

class CreateControllerPage extends SOYShopWebPage{

    function __construct($args) {

    	if(soy2_check_token()){
    		$id = (isset($args[0])) ? (int)$args[0] : null;
			$site = ShopUtil::getSiteById($id);

	    	$res = (is_numeric($site->getId())) ? SOY2Logic::createInstance("logic.RootLogic")->createSOYShopController($site) : false;

	    	if($res){
				CMSApplication::jump("Site.Detail." . $id . "?created");
			}else{
				CMSApplication::jump("Site.Detail." . $id . "?error");
			}
    	}
    }
}
