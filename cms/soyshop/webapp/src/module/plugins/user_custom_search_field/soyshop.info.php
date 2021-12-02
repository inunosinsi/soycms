<?php
/*
 */
class UserCustomSearchFieldInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=user_custom_search_field") . '">ユーザーカスタムサーチフィールド</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "user_custom_search_field", "UserCustomSearchFieldInfo");
