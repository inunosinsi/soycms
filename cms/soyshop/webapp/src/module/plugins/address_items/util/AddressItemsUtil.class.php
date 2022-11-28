<?php

class AddressItemsUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("address_items.config", array(
			array("label" => "市区郡", "required" => 1, "example" => "京都市左京区"),
			array("label" => "町番地", "required" => 0, "example" => "高野東開町8-5"),
			array("label" => "建物名", "required" => 0, "example" => "SOYビル1F"),
			array("label" => "", "required" => 0, "example" => "")
		));
	}

	public static function save(array $values){
		for($i = 0; $i < count($values); $i++){
			$arr = $values[$i];
			$arr["label"] = trim($arr["label"]);
			$arr["required"] = (isset($arr["required"]) && $arr["required"] == 1) ? 1 : 0;
			$arr["example"] = trim($arr["example"]);
			$values[$i] = $arr;
		}
		SOYShop_DataSets::put("address_items.config", $values);
	}
}