<?php

class MaintenancePageInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=maintenance_page").'">メンテナンスページプラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "maintenance_page", "MaintenancePageInfo");
