<?php
/*
 */
class FixedPointGrantInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=fixed_point_grant") . '">固定ポイント加算時の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "fixed_point_grant", "FixedPointGrantInfo");
