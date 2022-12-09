<?php

class CustomReplaceUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("custom_replace.config", array(
			"fieldId" => "",
			"format" => "##LABEL##：##VALUE##"
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("custom_replace.config", $values);
	}

	public static function getReplacementStringList(){
		return array(
			"ITEM_NAME" => "商品名",
			"ITEM_CODE" => "商品コード",
			"LABEL" => "フィールド名",
			"FIELD_ID" => "フィールドID",
			"VALUE" => "商品ごとに設定したカスタムフィールドの値"
		);
	}
}
