<?php

class RemovePage extends CMSWebPageBase{
	
	function __construct($args){
		
		if(soy2_check_token() && isset($args[0]) && is_numeric($args[0])){
			
			try{
				self::dao()->deleteById($args[0]);
				SOY2PageController::jump("Application.Secret?success");
			}catch(Exception $e){
				//
			}
		}
		
		SOY2PageController::jump("Application.Secret?error");
	}
	
	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("service.AppDBDAO");
		return $dao;
	}
}
?>