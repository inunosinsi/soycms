<?php
/*
 */
class UserGroupInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=user_group") . '">顧客グループのカスタムサーチフィールド</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "user_group", "UserGroupInfo");
