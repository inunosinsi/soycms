<?php 
class LogoutPage extends MobileMyPagePageBase{
	
	function __construct(){
		WebPage::__construct();
		$mypage = MyPageLogic::getMyPage();
		$mypage->logout();

		$this->createAdd("top_link","HTMLLink", array(
			"link" => SOYSHOP_SITE_URL
		));

	}
}
?>