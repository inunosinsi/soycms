<?php

class MeasurePage extends CMSWebPageBase{
	
	function __construct($args){
		if(soy2_check_token() && isset($args[0]) && is_numeric($args[0])){
			if(SOY2Logic::createInstance("logic.admin.Login.ErrorLogic")->measure((int)$args[0])){
				SOY2PageController::jump("");
			}
		}
		
		SOY2PageController::jump("Site.Login.Notice");
	}
}
?>