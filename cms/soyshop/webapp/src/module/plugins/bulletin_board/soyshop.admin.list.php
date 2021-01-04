<?php
class BulletinBoardAdminList extends SOYShopAdminListBase{

    function getTabName(){
        return "掲示板";
    }

    function getTitle(){
        return "掲示板";
    }

    function getContent(){
		return "新着等";
    }
}
SOYShopPlugin::extension("soyshop.admin.list", "bulletin_board", "BulletinBoardAdminList");
