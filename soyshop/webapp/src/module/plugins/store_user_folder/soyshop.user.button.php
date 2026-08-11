<?php

class StoreUserFolderUserTitleButton extends SOYShopUserButtonBase{

	function buttonOnTitle($userId){
		return "<a href=\"javascript:void(0);\" class=\"btn btn-default btn-xs\" data-toggle=\"modal\" data-target=\"#storageModal\">ストレージ</a>";
	}
}
SOYShopPlugin::extension("soyshop.user.button", "store_user_folder", "StoreUserFolderUserTitleButton");
