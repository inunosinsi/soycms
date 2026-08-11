<?php
class CompletePage extends MainMyPagePageBase{

	function __construct(){
		//お問い合わせ番号がなければ表示しない
		if(!isset($_GET["tracking_number"])) $this->jump("inquiry");

		$mypage = $this->getMyPage();
		$mypage->clearAttribute("inquiry.content");
		$mypage->clear();
		$mypage->save();

		parent::__construct();

		$this->addLabel("tracking_number", array(
			"text" => $_GET["tracking_number"]
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
	}
}
