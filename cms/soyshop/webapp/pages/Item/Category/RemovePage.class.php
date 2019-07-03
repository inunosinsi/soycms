<?php

class RemovePage extends WebPage{

    function __construct($args) {

		$id = (isset($args[0])) ? (int)$args[0] : null;

		if(soy2_check_token()){
			try{
				SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->deleteById($id);
			}catch(Exception $e){
		    	SOY2PageController::jump("Item.Category?failed");
			}
		}

    	SOY2PageController::jump("Item.Category?removed");
    }
}
