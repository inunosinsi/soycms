<?php
SOY2HTMLFactory::importWebPage("message.IndexPage");
class CompletePage extends IndexPage{

    function __construct() {
    	
    	$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//ログインしていなかったら飛ばす
    	
    	$this->clearPostToSession("front_message_post");
    	
    	WebPage::WebPage();
    	
    	$this->addLink("message_link", array(
    		"link" => SOYSHOP_SITE_URL.soyshop_get_mypage_uri() . "/message"
    	));
    }
}
?>