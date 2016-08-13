<?php 
class CompletePage extends MobileMyPagePageBase{
	
	function __construct(){
		WebPage::__construct();
		
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