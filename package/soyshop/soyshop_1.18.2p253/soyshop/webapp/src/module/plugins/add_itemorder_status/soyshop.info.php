<?php
/*
 */
class AddItemOrderStatusInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=add_itemorder_status").'">注文詳細の商品毎の状態項目追加の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "add_itemorder_status", "AddItemOrderStatusInfo");
