<?php 
class LogoutPage extends MobileMyPagePageBase{
	
	function LogoutPage(){
		WebPage::WebPage();
		$mypage = MyPageLogic::getMyPage();
		$mypage->logout();

		$this->createAdd("top_link","HTMLLink", array(
			"link" => SOYSHOP_SITE_URL
		));

	}
}
?>