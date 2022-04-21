<?php

class SOYShopAddressUtil {

	public static function getAddressItems(){
		SOYShopPlugin::load("soyshop.address");
		$items = SOYShopPlugin::invoke("soyshop.address")->getAddressItems();
		if(is_array($items)) return $items;

		return array(
			array("label" => "市区郡", "required" => true, "example" => "京都市左京区"),
			array("label" => "町番地", "required" => false, "example" => "高野東開町8-5"),
			array("label" => "建物名", "required" => false, "example" => "SOYビル1F")
		);
	}

	public static function getEmptyAddressItem(){
		return array("label" => "", "required" => false, "example" => "");
	}
}