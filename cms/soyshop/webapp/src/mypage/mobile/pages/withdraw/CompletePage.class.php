<?php

class CompletePage extends MobileMyPagePageBase{

	function doPost(){

	}

    function __construct() {
    	$mypage = MyPageLogic::getMyPage();

    	$logic = SOY2Logic::createInstance("logic.user.UserLogic");
    	$logic->remove($mypage->getUserId());

    	WebPage::__construct();

    	$this->createAdd("top_link","HTMLLink", array(
    		"link" => SOYSHOP_SITE_URL.soyshop_get_mypage_uri()
    	));

    	$mypage->logout();
    }
}
?>