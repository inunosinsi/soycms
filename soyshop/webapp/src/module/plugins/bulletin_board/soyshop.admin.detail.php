<?php

class BulletinBoardAdminDetail extends SOYShopAdminDetailBase{

	function getTitle(){
		return "掲示板詳細";
	}

	function getContent(){
		SOY2::import("module.plugins.bulletin_board.page.BoardDetailPage");
		$form = SOY2HTMLFactory::createInstance("BoardDetailPage");
		$form->setPostId($this->getDetailId());
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.detail", "bulletin_board", "BulletinBoardAdminDetail");
