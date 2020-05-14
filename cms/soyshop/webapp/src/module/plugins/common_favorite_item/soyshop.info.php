<?php
/*
 */
class CommonFavoriteItemInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_favorite_item") . '">お気に入り登録の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_favorite_item", "CommonFavoriteItemInfo");
