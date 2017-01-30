<?php

class PointBaseUtil {

    public static function getConfig(){
		$config = SOYShop_DataSets::get("point_config", array(
			"percentage" => 10,
			"customer" => 1,
			"limit" => null,
			"mail" => null,
			"recalculation" => 1
		));
		
		//ポイントの再計算の設定が無い時は実行する
		$config["recalculation"] = (isset($config["recalculation"])) ? (int)$config["recalculation"] : 1;
		
		return $config;
	}
	
	public static function getMailTitle(){
		return SOYShop_DataSets::get("point_config.title", "[#SHOP_NAME#] ポイントの有効期限終了日が近づいています。");
	}
	
	public static function saveMailTitle($title){
		return SOYShop_DataSets::put("point_config.title", $title);
	}
	
	public static function getMailContent(){
		$content = SOYShop_DataSets::get("point_config.content", null);
		if(is_null($content)){
			$content = @file_get_contents(dirname(dirname(__FILE__)) . "/mail/content.txt", "utf-8");
		}
		return $content;
	}
	
	public static function saveMailContent($content){
		return SOYShop_DataSets::put("point_config.content", $content);
	}
}
?>