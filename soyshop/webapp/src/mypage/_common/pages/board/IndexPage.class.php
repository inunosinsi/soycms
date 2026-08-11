<?php

SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
class IndexPage extends MainMyPagePageBase{

	function __construct(){
		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		// ログインチェックは不要

		parent::__construct();

		$logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic");
		$this->createAdd("group_list", "_common.board.topic.GroupListComponent", array(
			"list" => $logic->get(),
			"abstracts" => $logic->getGroupAbstracts()
		));
	}
}
