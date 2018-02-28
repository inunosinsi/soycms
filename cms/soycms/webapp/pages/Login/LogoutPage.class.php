<?php

class LogoutPage extends WebPage{

    function __construct() {
		$action = SOY2ActionFactory::createInstance('LogoutAction');
		$action->run();
		if(!defined("SOYCMS_ASP_MODE")){
			SOY2PageController::redirect("../admin/");
		}else{
			SOY2PageController::jump("");
		}

		exit;
    }
}
