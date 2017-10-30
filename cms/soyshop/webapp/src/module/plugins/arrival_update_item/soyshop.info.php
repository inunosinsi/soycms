<?php
/*
 */
class ArrivalUpdateItemInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=arrival_update_item").'">最近更新した商品表示プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "arrival_update_item", "ArrivalUpdateItemInfo");
