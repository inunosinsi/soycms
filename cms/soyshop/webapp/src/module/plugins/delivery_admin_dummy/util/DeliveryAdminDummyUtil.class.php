<?php

class DeliveryAdminDummyUtil{

	public static function getConfig(){
		return SOYShop_DataSets::get("delivery_admin_dummy.config", array(
			"label" => "金額指定",
			"show_description" => 0
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("delivery_admin_dummy.config", $values);
	}
}
