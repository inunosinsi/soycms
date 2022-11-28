<?php
class MessageBoardAdminList extends SOYShopAdminListBase{

	function getTabName(){
		return "連絡";
	}

	function getTitle(){
		return "連絡掲示板";
	}

	function getContent(){
		SOY2::import("module.plugins.message_board.page.BoardPage");
		$form = SOY2HTMLFactory::createInstance("BoardPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.list", "message_board", "MessageBoardAdminList");
?>