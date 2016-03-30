<?php

class TopPage extends MobileMyPagePageBase{

    function TopPage() {
    	WebPage::WebPage();

		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");

		$user = $this->getUser();
		
		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));

    	
    	$this->createAdd("order_link","HTMLLink", array(
    		"link" => soyshop_get_mypage_url() . "/order"
    	));

    	$this->createAdd("edit_link","HTMLLink", array(
    		"link" => soyshop_get_mypage_url() . "/edit"
    	));
    	
    	$this->createAdd("address_link","HTMLLink", array(
    		"link" => soyshop_get_mypage_url() . "/address"
    	));
    	
    	$this->createAdd("withdraw_link","HTMLLink", array(
    		"link" => soyshop_get_mypage_url() . "/withdraw"
    	));
    	
    	$this->createAdd("logout_link","HTMLLink", array(
    		"link" => soyshop_get_mypage_url() . "/logout"
    	));
    	

    }
}
?>