<?php 
class CompletePage extends MobileMyPagePageBase{
	
	function CompletePage(){
		WebPage::WebPage();
		
		$mypage = MyPageLogic::getMyPage();
		$mypage->clearUserInfo();
		$mypage->save();		

		//EditPage.htmlへのリンクを生成する
		$this->createAdd("edit_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/edit/"
		));
		
		$this->createAdd("return_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/top"
		));
	}
}
?>