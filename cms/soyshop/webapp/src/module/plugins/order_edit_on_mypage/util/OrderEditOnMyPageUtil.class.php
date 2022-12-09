<?php

class OrderEditOnMyPageUtil{

	public static function getMailConfig(){
		$config = SOYShop_DataSets::get("order_edit_on_mypage.mail", null);
		if(isset($config)) return $config;

		//footerのみ標準設定のものを挿入する
		$mailConfig = SOY2Logic::createInstance("logic.mail.MailLogic")->getUserMailConfig();
		return array(
			"title" => file_get_contents(dirname(dirname(__FILE__)) . "/template/title.txt"),
			"header" => file_get_contents(dirname(dirname(__FILE__)) . "/template/header.txt"),
			"footer" => (isset($mailConfig["footer"])) ? $mailConfig["footer"] : ""
		);
	}

	public static function saveMailConfig($values){
		SOYShop_DataSets::put("order_edit_on_mypage.mail", $values);
	}
}
