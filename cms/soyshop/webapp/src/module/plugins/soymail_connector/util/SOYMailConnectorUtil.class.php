<?php

class SOYMailConnectorUtil{
	
	const NOT_SEND = 0;
	const SEND = 1;
	
	const NOT_INSERT = 0;
	const INSERT = 1;
	
	public static function getConfig(){
		return SOYShop_DataSets::get("soymail_connector.config", array(
			"isCheck" => 0,
			"first_order_add_point" => 0,
			"first_order_add_point_text" => "初回登録時にメルマガ登録で#POINT#ポイントプレゼント",
			"label" => "メールマガジン",
			"description" => "配信を希望する",
			"isInsertMail" => 1		//メール文面にメールマガジン配信の有無を入れるか？
		));
	}
	
	public static function saveConfig($values){
		$values["isCheck"] = soyshop_convert_number($values["isCheck"], self::NOT_SEND);
		$values["first_order_add_point"] = soyshop_convert_number($values["first_order_add_point"], 0);
		SOYShop_DataSets::put("soymail_connector.config", $values);
	}
}
?>