<?php
/*
 */
class CommonPointBaseInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_point_base") . '">ポイント制導入の購入時のポイント加算設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_point_base", "CommonPointBaseInfo");
?>