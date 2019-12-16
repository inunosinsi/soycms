<?php

class ItemReviewUtil{

	public static function getConfig(){
		return self::_getConfig();
    }

	public static function saveConfig($values){
		$values["code"] = str_replace("#", "", mb_convert_kana($values["code"], "a"));
		if(!preg_match("/^([a-fA-F0-9])/", $values["code"])){
			$values["code"] = "ffff00";
		}

		//初期値の標準化
		foreach(array("login", "publish", "edit", "captcha_img", "evaluation_star") as $t){
			$values[$t] = (isset($values[$t])) ? 1 : null;
		}

		$values["captcha"] = (isset($values["captcha"])) ? trim($values["captcha"]) : "";
		$values["point"] = (isset($values["point"]) && is_numeric($values["point"])) ? (int)$values["point"] : 0;

		SOYShop_DataSets::put("item_review.config", $values);
	}

	public static function buildEvaluationString($rank){
		static $code;
		if(is_null($code)){
			$config = self::_getConfig();
			$code = $config["code"];
		}

		$notRank = 5 - (int)$rank;
		//評価分
		$str1 = "";
		$str2 = "";
		for($i = 0; $i < $rank; $i++){
			$str1 .= "★";
		}
		for($j = 0; $j < $notRank; $j++){
			$str2 .= "☆";
		}
		return "<span style=\"color:#" . $code . ";\">" . $str1 . "</span>" . $str2;
	}

	private static function _getConfig(){
		return SOYShop_DataSets::get("item_review.config", array(
    		"code" => "146685",
    		"nickname" => "名無しさん",
			"login" => 1,
    		"publish" => 1,
    		"edit" => 1,
    		"point" => 0,
			"evaluation_star" => 0
    	));
	}
}
