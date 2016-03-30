<?php

class CompletePage extends MobileMyPagePageBase{

	function doPost(){

	}

    function CompletePage() {
    	$mypage = MyPageLogic::getMyPage();

    	$logic = SOY2Logic::createInstance("logic.user.UserLogic");
    	$logic->remove($mypage->getUserId());

    	WebPage::WebPage();

    	$this->createAdd("top_link","HTMLLink", array(
    		"link" => SOYSHOP_SITE_URL.soyshop_get_mypage_uri()
    	));

    	$mypage->logout();
    }
}
?>