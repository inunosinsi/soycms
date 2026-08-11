<?php
class CommonNotepadAdminList extends SOYShopAdminListBase{

    function getTabName(){
        return null;
    }

    function getTitle(){
        return "メモ帳";
    }

    function getContent(){
		return "dummy";
    }
}
SOYShopPlugin::extension("soyshop.admin.list", "common_notepad", "CommonNotepadAdminList");
