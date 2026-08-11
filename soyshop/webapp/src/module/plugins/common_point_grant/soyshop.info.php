<?php
/*
 */
class CommonPointGrantInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_point_grant") . '">ポイント加算時の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_point_grant", "CommonPointGrantInfo");
?>