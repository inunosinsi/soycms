<?php
/*
 */
class ReturnsSlipNumberInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=returns_slip_number").'">返送伝票番号記録プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "returns_slip_number", "ReturnsSlipNumberInfo");
