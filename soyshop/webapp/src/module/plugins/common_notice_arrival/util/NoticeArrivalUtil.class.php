<?php

class NoticeArrivalUtil{

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	public static function getConfig(){
		return SOYShop_DataSets::get("common_notice_arrival.config", array(
			"send_mail" => 1
		));
	}

	public static function saveConfig(array $values=array()){
		$values["send_mail"] = (isset($values["send_mail"]) && $values["send_mail"]) ? 1 : 0;
		SOYShop_DataSets::put("common_notice_arrival.config", $values);
	}
}
