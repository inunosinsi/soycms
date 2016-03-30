<?php
class CompletePage extends MainMyPagePageBase{

	function CompletePage(){

		$mypage = MyPageLogic::getMyPage();
		$mypage->clearUserInfo();
		$mypage->clearErrorMessage();
		$mypage->save();
		
		WebPage::WebPage();

		$this->addLink("login_link", array(
			"link" => soyshop_get_mypage_url() . "/login"
		));

	}
}
?>