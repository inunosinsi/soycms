<?php

class StoreUserFolderUserTitleButton extends SOYShopUserButtonBase{

	function buttonOnTitle($userId){
		return "<a href=\"javascript:void(0);\" onclick=\"return OptionWindow.popup();\" class=\"button\">ストレージ</a>";
	}
}
SOYShopPlugin::extension("soyshop.user.button", "store_user_folder", "StoreUserFolderUserTitleButton");
