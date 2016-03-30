<?php

class SOYMailConnectorUtil{
	
	const NOT_SEND = 0;
	const SEND = 1;
	
	const NOT_INSERT = 0;
	const INSERT = 1;
	
	public static function getConfig(){
		return SOYShop_DataSets::get("soymail_connector.config", array(
			"isCheck" => 0,
			"label" => "メールマガジン",
			"description" => "配送を希望する",
			"isInsertMail" => 1		//メール文面にメールマガジン配信の有無を入れるか？
		));
	}
	
	public static function saveConfig($values){
		$values["isCheck"] = soyshop_convert_number($values["isCheck"], self::NOT_SEND);
		SOYShop_DataSets::put("soymail_connector.config", $values);
	}
}
?>