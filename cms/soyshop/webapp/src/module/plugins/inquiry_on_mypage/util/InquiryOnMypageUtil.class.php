<?php

class InquiryOnMypageUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("inquiry_on_mypage.config", array(
			"tab" => 1
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("inquiry_on_mypage.config", $values);
	}

	public static function getMailConfig(){
		$config = SOYShop_DataSets::get("inquiry_on_mypage.mail", null);
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
		SOYShop_DataSets::put("inquiry_on_mypage.mail", $values);
	}

	public static function getParameter($key){
		$session = SOY2ActionSession::getUserSession();
		if(isset($_POST[$key])){
			$session->setAttribute("inquiry_on_mypage_search:" . $key, $_POST[$key]);
			$params = $_POST[$key];
		}else if(isset($_GET["reset"])){
			$session->setAttribute("inquiry_on_mypage_search:" . $key, array());
			$params = array();
		}else{
			$params = $session->getAttribute("inquiry_on_mypage_search:" . $key);
			if(is_null($params)) $params = array();
		}

		return $params;
	}
}
