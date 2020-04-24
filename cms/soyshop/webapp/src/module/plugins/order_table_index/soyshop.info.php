<?php
/*
 */
class OrderTableIndexInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=order_table_index") . '">注文関連のテーブル最適化プラグイン</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "order_table_index", "OrderTableIndexInfo");
