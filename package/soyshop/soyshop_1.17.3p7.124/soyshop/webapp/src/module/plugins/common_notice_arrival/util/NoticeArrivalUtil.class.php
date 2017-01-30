<?php

class NoticeArrivalUtil{
	
	function NoticeArrivalUtil(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	public static function getConfig(){
		return SOYShop_DataSets::get("common_notice_arrival.config", array(
			"send_mail" => 1
		));
	}
	
	public static function saveConfig($values = array()){
		$values["send_mail"] = (isset($values["send_mail"]) && $values["send_mail"]) ? 1 : 0;
		SOYShop_DataSets::put("common_notice_arrival.config", $values);
	}
	
	public static function getMailTitle(){
		return SOYShop_DataSets::get("common_notice_arrival.title", "[#SHOP_NAME#] #ITEM_NAME#を入荷しました。");
	}
	
	public static function saveMailTitle($title){
		SOYShop_DataSets::put("common_notice_arrival.title", $title);
	}
	
	public static function getMailContent(){
		$content = SOYShop_DataSets::get("common_notice_arrival.content", null);
		if(is_null($content)){
			$content = @file_get_contents(dirname(dirname(__FILE__)) . "/mail/content.txt", "utf-8");
		}
		return $content;
	}
	
	public static function saveMailContent($content){
		SOYShop_DataSets::put("common_notice_arrival.content", $content);
	}
}
?>