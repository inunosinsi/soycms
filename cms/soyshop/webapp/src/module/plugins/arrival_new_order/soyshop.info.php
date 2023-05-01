<?php
/*
 */
class ArrivalNewOrderInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=arrival_new_order").'">新着注文一覧表示プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "arrival_new_order", "ArrivalNewOrderInfo");
