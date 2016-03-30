<?php

class NoticeArrivalUtil{
	
	function NoticeArrivalUtil(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	public static function getMailTitle(){
		return SOYShop_DataSets::get("common_notice_arrival.title", "[#SHOP_NAME#] #ITEM_NAME#を入荷しました。");
	}
	
	public static function saveMailTitle($title){
		return SOYShop_DataSets::put("common_notice_arrival.title", $title);
	}
	
	public static function getMailContent(){
		$content = SOYShop_DataSets::get("common_notice_arrival.content", null);
		if(is_null($content)){
			$content = @file_get_contents(dirname(dirname(__FILE__)) . "/mail/content.txt", "utf-8");
		}
		return $content;
	}
	
	public static function saveMailContent($content){
		return SOYShop_DataSets::put("common_notice_arrival.content", $content);
	}
}
?>