<?php

class DetailPage extends MainMyPagePageBase{

	function __construct($args){
		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		if(!isset($args[0]) && !is_numeric($args[0])) $this->jumpToTop();
		$this->id = (int)$args[0];

		$user = soyshop_get_user_object($this->id);
		if(!$user->isPublished()) $this->jumpToTop();

		parent::__construct();

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_url() . "/board/"
		));

		$this->addLink("user_list_link", array(
			"link" => soyshop_get_mypage_url() . "/board/user/"
		));

		$this->addLabel("user_name", array(
			"text" => $user->getDisplayName()
		));

		DisplayPlugin::toggle("url", strlen($user->getUrl()));
		$this->addLink("url", array(
			"link" => $user->getUrl(),
			"text" => $user->getUrl(),
			"target" => "_blank",
			"attr:rel" => "noopener"
		));

		$this->addLabel("register_date", array(
			"text" => (is_numeric($user->getRegisterDate())) ? date("Y-m-d H:i:s", $user->getRegisterDate()) :""
		));

		SOYShopPlugin::load("soyshop.user.customfield");
		$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
			"mode" => "build_named_form",
			"app" => $this->getMyPage(),
			"pageObj" => $this,
			"userId" => $user->getId()
		));
	}
}
