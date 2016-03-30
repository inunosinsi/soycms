<?php
SOY2HTMLFactory::importWebPage("message.detail.IndexPage");
class CompletePage extends IndexPage{

	private $id;

	function CompletePage($args) {
		
		$this->id = $args[0];
		
		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//ログインしていなかったら飛ばす
		
		WebPage::WebPage();
		
		$this->clearPostToSession("front_message_post");
    	
    	WebPage::WebPage();
    	
    	$this->addLink("message_link", array(
    		"link" => SOYSHOP_SITE_URL.soyshop_get_mypage_uri() . "/message/detail/" . $this->id
    	));
	}
}
?>