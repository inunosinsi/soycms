<?php

class reCAPTCHAUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("reCAPTCHA.config", array(
			"site_key" => "",
			"secret_key" => "",
			"page_id" => "",		//お問い合わせページのページID
		));
	}

	public static function saveConfig($values){
		$values["page_id"] = (isset($values["page_id"]) && is_numeric($values["page_id"])) ? (int)$values["page_id"] : null;
		SOYShop_DataSets::put("reCAPTCHA.config", $values);
	}
}
