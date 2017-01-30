<?php

class OrderLaterSendmailUtil{
	
	const MODE_REGISTER = 0;	//新規注文
	const MODE_PAYMENT = 1;		//支払確認
	const MODE_SEND = 2;		//発送
	
	public static function getConfig(){
		return SOYShop_DataSets::get("order_later_sendmail.config", array(
			"mode" => 0,
			"date" => 5
		));
	}
	
	public static function saveConfig($values){
		return SOYShop_DataSets::put("order_later_sendmail.config", $values);
	}
	
	public static function getMailTitle(){
		return SOYShop_DataSets::get("order_later_sendmail.title", "[#SHOP_NAME#] ご利用していただきありがとうございます");
	}
	
	public static function saveMailTitle($title){
		return SOYShop_DataSets::put("order_later_sendmail.title", $title);
	}
	
	public static function getMailContent(){
		$content = SOYShop_DataSets::get("order_later_sendmail.content", null);
		if(is_null($content)){
			$content = @file_get_contents(dirname(dirname(__FILE__)) . "/mail/content.txt", "utf-8");
		}
		return $content;
	}
	
	public static function saveMailContent($content){
		return SOYShop_DataSets::put("order_later_sendmail.content", $content);
	}
}
?>