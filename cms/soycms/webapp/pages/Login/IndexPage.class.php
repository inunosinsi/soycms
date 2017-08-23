<?php

/**
 * ASP版のログイン用
 */
class IndexPage extends WebPage{
	
	function doPost(){
		SOY2::import("action.login.LoginAction");
		$action = SOY2ActionFactory::createInstance('LoginAction');
		$result = $action->run();
		
		if($result->success()){//$result->success()){){			
			SOY2PageController::jump("?session_failed");
			exit;
		}else{
			$register = str_replace("admin","register",SOY2PageController::createLink("", true));
			SOY2PageController::redirect($register."?failed");
		}
		
		exit;
	}
	
	function __construct(){
		parent::__construct();
		
		if(defined("SOYCMS_ASP_MODE")){
			$register = str_replace("admin","register",SOY2PageController::createLink("", true));
			
			if(isset($_GET["session_failed"])){
				$register .= "?session_failed";
			}
			
			SOY2PageController::redirect($register);
		}else{
			SOY2PageController::jump("");
		}
	}
    
}

?>