<?php

class IndexPage extends MainMyPagePageBase{

	function __construct(){
		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		parent::__construct();

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_url() . "/board/"
		));

		//$users = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getISpublishUsers();
		$users = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.UserLogic")->getUsers();

		$this->createAdd("user_list", "_common.board.user.UserListComponent", array(
			"list" => $users
		));
	}
}
