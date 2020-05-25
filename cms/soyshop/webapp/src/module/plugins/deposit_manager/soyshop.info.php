<?php
/*
 */
class DepositManagerInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=deposit_manager").'">入金管理の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "deposit_manager", "DepositManagerInfo");
