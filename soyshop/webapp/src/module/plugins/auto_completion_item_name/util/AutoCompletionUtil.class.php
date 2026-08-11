<?php

class AutoCompletionUtil {

	const TYPE_HIRAGANA = "hiragana";
	const TYPE_KATAKANA = "katakana";
	const TYPE_OTHER = "other";

	const FIELD_ID = "auto_completion_item_name";

	const INCLUDE_CATEGORY = 1;

	public static function getConfig(){
		return SOYShop_DataSets::get(self::FIELD_ID . ".config", array(
			"count" => 10,	//ヒット件数,
			"include_category" => 0
		));
	}

	public static function saveConfig($values){
		if(!isset($values["count"]) || !is_numeric($values["count"])) $values["count"] = 10;
		$values["include_category"] = (isset($values["include_category"]) && is_numeric($values["include_category"])) ? (int)$values["include_category"] : 0;
		SOYShop_DataSets::put(self::FIELD_ID . ".config", $values);
	}

	public static function getItemTypes(){
		return array(
			self::TYPE_HIRAGANA => "ひらがな",
			self::TYPE_KATAKANA => "カタカナ",
			self::TYPE_OTHER => "その他"
		);
	}
}
