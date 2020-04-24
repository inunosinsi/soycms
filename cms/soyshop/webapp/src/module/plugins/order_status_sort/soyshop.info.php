<?php
/*
 */
class OrderStatusSortInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=order_status_sort") . '">注文状態並び順の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "order_status_sort", "OrderStatusSortInfo");
