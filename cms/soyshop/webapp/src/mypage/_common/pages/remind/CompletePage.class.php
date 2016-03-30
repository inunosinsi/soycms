<?php

class CompletePage extends MainMyPagePageBase{
	
    function CompletePage() {
    	WebPage::WebPage();
    	
    	$this->addLink("login_link", array(
    		"link" => soyshop_get_mypage_url() . "/login"
    	));
    }
}
?>