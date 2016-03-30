<?php

class UtilMultiLanguageUtil {
	
	const LANGUAGE_JP = "jp";
	const LANGUAGE_EN = "en";
	const LANGUAGE_ZH = "zh";
	const LANGUAGE_ZH_TW = "zh-tw";
	
	const MODE_PC = "pc";
	const MODE_SMARTPHONE = "smartphone";
	
	const IS_USE = 1;
	const NO_USE = 0;

	public static function getConfig(){
		return SOYShop_DataSets::get("util_multi_language.config", array(
			self::LANGUAGE_JP => array(
							"prefix" => "",
							"is_use" => self::IS_USE
						),
			self::LANGUAGE_EN => array(
							"prefix" => "en",
							"is_use" => self::IS_USE
						),
			//中華人民共和国の中国語（簡体字）
			self::LANGUAGE_ZH => array(
							"prefix" => "zh",
							"is_use" => self::NO_USE
						),
			//台湾の中国語（繁体字）
			self::LANGUAGE_ZH_TW => array(
							"prefix" => "zh-tw",
							"is_use" => self::NO_USE
						),
			"check_browser_language_config" => 0
		));
	}
	
	public static function allowLanguages($all = false){
		$list = array(
			self::LANGUAGE_JP => "日本語",
			self::LANGUAGE_EN => "英語",
			self::LANGUAGE_ZH => "中国語(簡体字)",
			self::LANGUAGE_ZH_TW => "中国語(繁体字)"
		);
		
		if(!$all){
			foreach(self::getConfig() as $lang => $config){
				
				if(isset($config["is_use"]) && $config["is_use"] == self::NO_USE) {
					unset($list[$lang]);
				}
			}
		}else{
			//管理画面を開いている時はすべて許可
		}
		
		return $list;
	}
	
	public static function getLanguageText($lang){
		static $langList;
		if(is_null($langList)){
			$langList = self::allowLanguages(true);
		}
		return (isset($langList[$lang])) ? $langList[$lang] : null;
	}
	
	public static function saveConfig($values){
		if(!isset($values["check_browser_language_config"])) $values["check_browser_language_config"] = 0;
		SOYShop_DataSets::put("util_multi_language.config", $values);
	}
	
	public static function getMailConfig($target, $type, $lang){
		$key = "util_multi_language." . $target . "_" . $lang . "_" . $type;
		return array(
			"active" => SOYShop_DataSets::get($key . ".active", null),
			"title" => SOYShop_DataSets::get($key . ".title", null),
			"header" => SOYShop_DataSets::get($key . ".header", null),
			"footer" => SOYShop_DataSets::get($key . ".footer", null)
		);		
	}
	
	public static function saveMailConfig($target, $type, $lang, $values){
		$key = "util_multi_language." . $target . "_" . $lang . "_" . $type;
		SOYShop_DataSets::put($key . ".active", @$values["active"]);
		SOYShop_DataSets::put($key . ".title", @$values["title"]);
		SOYShop_DataSets::put($key . ".header", @$values["header"]);
		SOYShop_DataSets::put($key . ".footer", @$values["footer"]);
	}
	
	public static function getPageTitle($mode, $lang){
		$key = "util_multi_language." . $mode . "_title." . $lang;
		return SOYShop_DataSets::get($key, null);
	}
	
	public static function savePageTitle($mode, $lang, $value){
		$key = "util_multi_language." . $mode . "_title." . $lang;
		SOYShop_DataSets::put($key, $value);
	}
	
	public static function translate($key){
		static $_translateWords;
		if(!$_translateWords){
			$array = self::translateWords();
			$_translateWords = $array[SOYSHOP_MAIL_LANGUAGE];
		}
		return (isset($_translateWords[$key])) ? $_translateWords[$key] : "";
	}
	
	public static function translateWords(){
		return array(
			self::LANGUAGE_JP => array(
				"order_number" => "注文番号",
				"order_date" => "注文日時",
				"item_name" => "商品名",
				"item_code" => "商品コード",
				"item_count" => "数量",
				"item_price" => "価格",
				"pcs" => "点",
				"yen" => "円",
				"subtotal" => "小計",
				"total" => "合計",
				"shipping" => "お届け先住所",
				"customer" => "ご注文者",
				"office" => "法人名",
				"name" => "お名前",
				"honorific" => "様",
				"reading" => "フリガナ",
				"zip" => "郵便番号",
				"address" => "住所",
				"phone" => "電話番号",
				"mailaddress" => "メールアドレス",
				"memo" => "備考"
			),
			self::LANGUAGE_EN => array(
				"order_number" => "Order number",
				"order_date" => "Order date",
				"item_name" => "Item name",
				"item_code" => "Item code",
				"item_count" => "Count",
				"item_price" => "Price",
				"pcs" => "pcs",
				"yen" => "yen",
				"subtotal" => "Subtotal",
				"total" => "Total",
				"shipping" => "Shipping address",
				"customer" => "Customer",
				"office" => "Office name",
				"name" => "Name",
				"honorific" => "",
				"reading" => "",
				"zip" => "Zip code",
				"address" => "Address",
				"phone" => "Phone number",
				"mailaddress" => "e-mail",
				"memo" => "Memo"
			)
		);
	}
}
?>