<?php

class SOYShopWebPage extends WebPage{
	
	var $errors = array();
	
	/* helper */
	
	function addForm($id,$args = array()){
		
		$url = (!isset($arguments["action"])) ? @$_SERVER["REQUEST_URI"] : $arguments["action"];
		
		$this->createAdd($id, "HTMLForm", $args);
		
	}
	
	function jump($addr=""){
   		CMSApplication::jump($addr);
   		exit;
	}	

	/* session */
    function getSession(){
    	return SOY2ActionSession::getUserSession();
    }
    
    function setAttributeToSession($key,$value){
    	$session = $this->getSession();
    	$session->setAttribute($key,$value);
    }
    
    function getAttributeFromSession($key){
    	$session = $this->getSession();
    	return $session->getAttribute($key);
    }
    function clearSession($key = null){
    	$session = $this->getSession();
		if($key){
			$session->setAttribute($key,null);
		}else{
			$session->clearAttributes();
		}
    }

	function getErrors(){
		return $this->errors;
	}
	
}

?>