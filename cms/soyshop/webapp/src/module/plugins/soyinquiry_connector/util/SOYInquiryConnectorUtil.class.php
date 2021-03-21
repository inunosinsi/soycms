<?php

class SOYInquiryConnectorUtil{

	public static function getConfig(){
		return SOYShop_DataSets::get("soyinquiry_connector_config", array(
			"url" => "https://example.com/inquiry"
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("soyinquiry_connector_config", $values);
	}
}
