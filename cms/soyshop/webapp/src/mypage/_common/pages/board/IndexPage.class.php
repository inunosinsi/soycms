<?php
class IndexPage extends MainMyPagePageBase{

	function __construct(){
		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		// ログインチェックは不要

		parent::__construct();

		$groups = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->get();
		$this->createAdd("group_list", "_common.board.topic.GroupListComponent", array(
			"list" => $groups
		));
	}
}
