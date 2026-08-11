<?php
class BulletinBoardAdminList extends SOYShopAdminListBase{

    function getTabName(){
        return "掲示板";
    }

    function getTitle(){
        return "掲示板";
    }

    function getContent(){
		SOY2::import("module.plugins.bulletin_board.page.BoardListPage");
		$form = SOY2HTMLFactory::createInstance("BoardListPage");
		$form->execute();
		return $form->getObject();
    }
}
SOYShopPlugin::extension("soyshop.admin.list", "bulletin_board", "BulletinBoardAdminList");
