<?php
class TakeOverLinkUtil {

	const TIMEOUT_CONFIG = 5;

	public static function getConfig(){
		return SOYShop_DataSets::get("take_over_set_link.config", array(
			"url" => null,	//データ引き継ぎ先のURL
			"timeout" => self::TIMEOUT_CONFIG,	//
			"description" => "<p>引き続き、〇〇の注文を行います。このページは○秒後に自動で転送されます。</p>\n<p><a href=\"##TAKE_OVER_URL##\">注文を進める。</a></p>"
		));
	}

	public static function saveConfig($values){
		$values["timeout"] = (isset($values["timeout"])) ? soyshop_convert_number($values["timeout"], self::TIMEOUT_CONFIG) : self::TIMEOUT_CONFIG;
		SOYShop_DataSets::put("take_over_set_link.config", $values);
	}
}
