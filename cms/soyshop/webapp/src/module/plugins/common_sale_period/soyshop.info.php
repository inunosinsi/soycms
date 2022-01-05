<?php
/*
 */
class SalePeriodInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_sale_period") . '">セール価格期間設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_sale_period", "SalePeriodInfo");
