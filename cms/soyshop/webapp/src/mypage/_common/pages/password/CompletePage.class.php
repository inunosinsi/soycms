<?php

class CompletePage extends MainMyPagePageBase{

    function CompletePage() {
    	WebPage::WebPage();
    	
    	$this->addLink("top_link", array(
    		"link" => soyshop_get_mypage_top_url()
    	));
    }
}
?>