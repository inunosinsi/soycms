<?php

class IndexPage extends MobileMyPagePageBase{

	function doPost(){
		
		if(soy2_check_token()){
			$this->jump("withdraw/complete");
		}
		
	}

    function __construct() {

    	parent::__construct();
    	
    	$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//ログインしていなかったら飛ばす
    	
    	$this->addForm("form");
    	
    	$this->createAdd("return_link","HTMLLink", array(
    		"link" => soyshop_get_mypage_url() . "/top"
    	));
    }
}
?>