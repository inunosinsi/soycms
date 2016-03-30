<?php
class CompletePage extends MainMyPagePageBase{

	function CompletePage(){

		$mypage = MyPageLogic::getMyPage();
		$mypage->clearUserInfo();
		$mypage->clearErrorMessage();
		$mypage->clear();
		$mypage->save();
		
		WebPage::WebPage();

		//EditPage.htmlへのリンクを生成する
		$this->addLink("edit_link", array(
			"link" => soyshop_get_mypage_url() . "/edit/"
		));
	}
}
?>