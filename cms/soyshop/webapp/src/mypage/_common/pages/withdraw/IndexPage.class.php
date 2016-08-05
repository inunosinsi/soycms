<?php

class IndexPage extends MainMyPagePageBase{

    function __construct() {

    	$mypage = MyPageLogic::getMyPage();
		
		//ログインしていなかったら飛ばす
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}

    	WebPage::WebPage();
    	
    	$this->addModel("error", array(
    		"visible" => (isset($_GET["error"]))
    	));

    	$this->addActionLink("remove_link", array(
    		"link" => soyshop_get_mypage_url() . "/withdraw/remove",
    	));
    }
}
?>