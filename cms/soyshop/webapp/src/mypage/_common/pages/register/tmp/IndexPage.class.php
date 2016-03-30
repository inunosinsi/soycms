<?php 
class IndexPage extends MainMyPagePageBase{
	
	function IndexPage(){

		$mypage = MyPageLogic::getMyPage();
		$mypage->clearUserInfo();
		$mypage->save();
		
		WebPage::WebPage();
		
		$this->addLink("login_link", array(
			"link" => SOYSHOP_SITE_URL.soyshop_get_mypage_uri() . "/login"
		));

		$this->addLink("top_link", array(
			"link" => SOYSHOP_SITE_URL
		));
	}
	
}
?>