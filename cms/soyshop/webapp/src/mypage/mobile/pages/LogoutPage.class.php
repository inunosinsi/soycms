<?php 
class LogoutPage extends MobileMyPagePageBase{
	
	function __construct(){
		WebPage::WebPage();
		$mypage = MyPageLogic::getMyPage();
		$mypage->logout();

		$this->createAdd("top_link","HTMLLink", array(
			"link" => SOYSHOP_SITE_URL
		));

	}
}
?>