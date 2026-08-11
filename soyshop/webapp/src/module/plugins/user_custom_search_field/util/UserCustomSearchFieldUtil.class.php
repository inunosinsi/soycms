<?php

class UserCustomSearchFieldUtil{

	const PLUGIN_PREFIX = "usf";	//usf:id="***"

	const TYPE_STRING = "string";
	const TYPE_TEXTAREA = "textarea";
	const TYPE_RICHTEXT = "richtext";
	const TYPE_INTEGER = "integer";
	const TYPE_RANGE = "range";
	const TYPE_CHECKBOX = "checkbox";
	const TYPE_RADIO = "radio";
	const TYPE_SELECT = "select";
	const TYPE_DATE = "date";
	const TYPE_URL = "url";
	const TYPE_MAILADDRESS = "mailaddress";

	//フォームの設置箇所
	const DISPLAY_ALL = 0;
	const DISPLAY_ADMIN_ONLY = 1;

	public static function getConfig(){
		return SOYShop_DataSets::get("user_custom_search.config", array());
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("user_custom_search.config", $values);
	}

	public static function getSearchConfig(){
		return SOYShop_DataSets::get("user_custom_search.search_config", array(
			"search" => array()
		));
	}

	public static function saveSearchConfig($values){
		return SOYShop_DataSets::put("user_custom_search.search_config", $values);
	}

	public static function getTypeList(){
		return array(
			self::TYPE_STRING => "文字列",
			self::TYPE_TEXTAREA => "複数行文字列",
			self::TYPE_RICHTEXT => "リッチテキスト",
			self::TYPE_INTEGER => "数字",
			self::TYPE_RANGE => "数字(範囲)",
			self::TYPE_CHECKBOX => "チェックボックス",
			self::TYPE_RADIO => "ラジオボタン",
			self::TYPE_SELECT => "セレクトボックス",
			self::TYPE_DATE => "日付",
			self::TYPE_URL => "URL",
			self::TYPE_MAILADDRESS => "メールアドレス"
		);
	}

	public static function getTypeText($key){
		$list = self::getTypeList();
		return (isset($list[$key])) ? $list[$key] : "";
	}

	public static function checkIsType($type){
		$list = self::getTypeList();
		return (isset($list[$type]));
	}
}
