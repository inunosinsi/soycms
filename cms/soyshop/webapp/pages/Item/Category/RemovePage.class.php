<?php

class RemovePage extends WebPage{

    function __construct($args) {

		$id = @$args[0];

		if(soy2_check_token()){
			try{
				$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
				$dao->deleteById($id);
			}catch(Exception $e){
		    	SOY2PageController::jump("Item.Category?failed");
			}
		}

    	SOY2PageController::jump("Item.Category?removed");
    }
}
?>