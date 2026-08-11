<?php
class CompletePage extends MainMyPagePageBase{

	function __construct(){

		$mypage = $this->getMyPage();
		$mypage->clearUserInfo();
		$mypage->clearErrorMessage();
		$mypage->clear();
		$mypage->save();

		parent::__construct();

		//EditPage.htmlへのリンクを生成する
		$this->addLink("edit_link", array(
			"link" => soyshop_get_mypage_url() . "/edit/"
		));
	}
}
