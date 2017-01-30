<?php

class RemovePage extends WebPage{

    function __construct($args) {
    	
    	if(!soy2_check_token()){
    		SOY2PageController::jump("User");
    	}

    	$id = @$args[0];
    	$logic = SOY2Logic::createInstance("logic.user.UserLogic");
    	$logic->remove($id);

    	SOY2PageController::jump("User");
    }
}
?>