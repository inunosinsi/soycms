<?php
class BulletinBoardTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Config.Detail?plugin=bulletin_board");
	}

	function getLinkTitle(){
		return "掲示板の設定";
	}

	function getTitle(){
		return "掲示板";
	}

	function getContent(){
		SOY2::import("module.plugins.bulletin_board.page.BoardTopPage");
		$form = SOY2HTMLFactory::createInstance("BoardTopPage");
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "bulletin_board", "BulletinBoardTop");
