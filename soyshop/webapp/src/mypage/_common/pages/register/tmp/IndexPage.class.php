<?php
class IndexPage extends MainMyPagePageBase{

	function __construct(){

		$mypage = $this->getMyPage();
		$mypage->clearUserInfo();
		$mypage->save();

		parent::__construct();

		$this->addLink("login_link", array(
			"link" => soyshop_get_mypage_url() . "/login"
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_site_url()
		));
	}
}
