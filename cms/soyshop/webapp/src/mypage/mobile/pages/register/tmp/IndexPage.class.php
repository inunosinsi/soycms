<?php 
class IndexPage extends MobileMyPagePageBase{
	
	function IndexPage(){
		WebPage::WebPage();

		$mypage = MyPageLogic::getMyPage();
		$mypage->clearUserInfo();
		$mypage->save();
		
		$this->createAdd("login_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/login"
		));
	}
	
}
?>