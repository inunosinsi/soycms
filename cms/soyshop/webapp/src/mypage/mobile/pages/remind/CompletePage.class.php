<?php

class CompletePage extends MobileMyPagePageBase{
	
	function doPost(){
		
	}

    function __construct() {
    	parent::__construct();
    	
    	$this->createAdd("login_link","HTMLLink", array(
    		"link" => soyshop_get_mypage_url() . "/login"
    	));
    	
    }
}
?>