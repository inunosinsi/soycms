<?php
/*
 */
class ShippingSchuduleNoticeEachItemsInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=parts_shipping_schedule_notice_each_items").'">出荷予定日通知プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "parts_shipping_schedule_notice_each_items", "ShippingSchuduleNoticeEachItemsInfo");
