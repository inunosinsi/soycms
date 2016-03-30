<?php

class CompletePage extends MobileMyPagePageBase{
	
	function doPost(){
		
	}

    function CompletePage() {
    	WebPage::WebPage();
    	
    	$this->createAdd("login_link","HTMLLink", array(
    		"link" => soyshop_get_mypage_url() . "/login"
    	));
    	
    }
}
?>