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
		foreach(array("login", "publish", "edit", "captcha_img", "evaluation_star", "active_other_page") as $t){
			$values[$t] = (isset($values[$t])) ? 1 : null;
		}

		$values["captcha"] = (isset($values["captcha"])) ? trim($values["captcha"]) : "";
		$values["point"] = (isset($values["point"]) && is_numeric($values["point"])) ? (int)$values["point"] : 0;
		$values["review_count"] = (isset($values["review_count"]) && is_numeric($values["review_count"])) ? (int)$values["review_count"] : "";

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

	/**
	 * Captcha用の画像を生成してファイルに保存する
	 * 要GD（imagejpeg）
	 */
	public static function generateCaptchaImage($captcha_value, $captcha_filename){
		SOY2::import("module.plugins.item_review.logic.SimpleCaptchaGenerator");
		$gen = SimpleCaptchaGenerator::getInstance();
		if(DIRECTORY_SEPARATOR == '\\'){
			//Windowsの場合：GDFONTPATHが効かないようだ
			$gen->setFonts(array(SOY2::RootDir() . "module/plugins/item_review/fonts/tuffy.ttf"));
		}else{
			putenv("GDFONTPATH=".str_replace("\\", "/", SOY2::RootDir() . "module/plugins/item_review/fonts/"));
			$gen->setFonts(array("tuffy.ttf"));
		}
		$gen->setBgRange(255, 255);
		$gen->setFgRange(0, 0);
		$gen->setBorderRange(0, 0);
		$gen->setMaxLineWidth(1);
		imagejpeg($gen->generate($captcha_value), SOY2HTMLConfig::CacheDir() . $captcha_filename . ".jpg");
	}

	/**
	 * ランダムな文字列を取得
	 */
	public static function getRandomString($length){
		$alpha = range(ord('A'), ord('Z'));

		$res = "";
		for($i = 0; $i < $length; $i++){
			$res .= chr($alpha[array_rand($alpha)]);
		}

		return $res;
	}

	private static function _getConfig(){
		return SOYShop_DataSets::get("item_review.config", array(
    		"code" => "146685",
    		"nickname" => "名無しさん",
			"login" => 1,
    		"publish" => 1,
    		"edit" => 1,
    		"point" => 0,
			"evaluation_star" => 0,
			"active_other_page" => 0	//別ページでレビューを表示する
    	));
	}
}
