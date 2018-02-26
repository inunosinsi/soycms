<?php

class DiscountFreeCouponUtil {

	const DATE_START = 0;
	const DATE_END = 2147483647;

	public static function getConfig(){
		return SOYShop_DataSets::get("discount.free.coupon.config", array(
			"min" => 0,
			"max" => "",
			"disitsMin" => 4,
			"disitsMax" => 16
		));
	}

	public static function saveConfig($array){
		SOYShop_DataSets::put("discount.free.coupon.config", $array);
	}

	public static function convertDate($date){
		//文字列を配列に分解
		$array = explode("-", $date);

		return mktime(0, 0, 0, $array[1], $array[2], $array[0]);
	}

	public static function convertNumber($int, $returnValue = 0){
		$int = mb_convert_kana($int, "a");
		return (isset($int) && is_numeric($int)) ? (int)$int : $returnValue;
	}

	public static function convertObject($register){
		//使用回数が空の場合、莫大な数を入れておく
		$register["count"] = self::convertNumber($register["count"], 9999999);
		$register["discount"] = self::convertNumber($register["discount"]);
		$register["discountPercent"] = self::convertNumber($register["discountPercent"]);

		//値引き率は100%を超えてはいけない
		if($register["discountPercent"] > 100){
			$register["discountPercent"] = 0;
		}

		$register["priceLimitMin"] = self::convertNumber($register["priceLimitMin"], null);
		$register["priceLimitMax"] = self::convertNumber($register["priceLimitMax"], null);

		//使用期限開始日に値が無い場合は、現在のタイムスタンプを入れる
		$register["timeLimitStart"] = (strlen($register["timeLimitStart"]) > 0) ? self::convertDate($register["timeLimitStart"]) : time();

		//使用期限終了日に値がが無い場合は、タイムスタンプの最大値を入れる
		$register["timeLimitEnd"] = (strlen($register["timeLimitEnd"]) > 0) ? self::convertDate($register["timeLimitEnd"]) : 2147483647;


		return $register;
	}

	public static function removeHyphen($value){
		return str_replace("-", "", $value);
	}

	/** クーポンコードの上限下限 **/
	public static function getDisitsMin(){
		$config = self::getConfig();
		return (isset($config["disitsMin"]) && (int)$config["disitsMin"] > 0) ? (int)$config["disitsMin"] : 4;
	}
	public static function getDisitsMax(){
		$config = self::getConfig();
		return (isset($config["disitsMax"]) && (int)$config["disitsMax"] > 0) ? (int)$config["disitsMax"] : 16;
	}
}
