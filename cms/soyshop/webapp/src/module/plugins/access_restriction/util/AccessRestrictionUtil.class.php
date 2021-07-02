<?php

class AccessRestrictionUtil {

	const COOKIE_KEY = "access_restriction_plugin";

	const ON = 1;
	const OFF = 0;

	//アクセスを許可するブラウザを登録
	public static function registerBrowser(){
		//ブラウザ側に持たせる鍵の生成
		$token = substr(md5($_SERVER["HTTP_USER_AGENT"] . time()), 0, 12);
		$ipAddr = $_SERVER["REMOTE_ADDR"];

		SOY2::import("module.plugins.access_restriction.domain.SOYShop_AccessRestrictionDAO");
		$dao = SOY2DAOFactory::create("SOYShop_AccessRestrictionDAO");
		$obj = new SOYShop_AccessRestriction();
		$obj->setToken($token);
		$obj->setIpAddress($_SERVER["REMOTE_ADDR"]);

		try{
			$dao->insert($obj);
		}catch(Exception $e){
			return false;
		}

		$cnf = self::_config();
		$n = (isset($cnf["day"]) && is_numeric($cnf["day"])) ? (int)$cnf["day"] : 3;

		soy2_setcookie(self::COOKIE_KEY, $token, array("expires" => soyshop_shape_timestamp(strtotime("+" . $n . " day"), "start") - 1, "samesite" => "Strict"));
		return true;
	}

	//ブラウザが当プラグインに登録されているか？
	public static function checkBrowser(){
		if(!isset($_COOKIE[self::COOKIE_KEY])) return false;

		SOY2::import("module.plugins.access_restriction.domain.SOYShop_AccessRestrictionDAO");
		$dao = SOY2DAOFactory::create("SOYShop_AccessRestrictionDAO");

		$cnf = self::_config();
		$n = (isset($cnf["day"]) && is_numeric($cnf["day"])) ? (int)$cnf["day"] : 3;

		//古い鍵を削除
		try{
			$dao->executeUpdateQuery("DELETE FROM soyshop_access_restriction WHERE create_date < " . soyshop_shape_timestamp(strtotime("-" . $n . " day"), "end"));
		}catch(Exception $e){
			//
		}

		try{
			$obj = $dao->get($_SERVER["REMOTE_ADDR"], $_COOKIE[self::COOKIE_KEY]);
		}catch(Exception $e){
			return false;
		}

		//二段界チェック
		return (strlen($obj->getIpAddress()) && $obj->getIpAddress() == $_SERVER["REMOTE_ADDR"]);
	}

	//ブラウザの登録を解除する
	public static function releaseBrowser(){
		//何もしない
		if(!isset($_COOKIE[self::COOKIE_KEY])) return;

		SOY2::import("module.plugins.access_restriction.domain.SOYShop_AccessRestrictionDAO");
		try{
			SOY2DAOFactory::create("SOYShop_AccessRestrictionDAO")->delete($_SERVER["REMOTE_ADDR"], $_COOKIE[self::COOKIE_KEY]);
		}catch(Exception $e){
			var_dump($e);
			//
		}
		soy2_setcookie(self::COOKIE_KEY);
	}

	public static function getPageDisplayConfig(){
		$cnf = SOYShop_DataSets::get("access_restriction.display", null);
		if(!is_null($cnf)) return $cnf;

		$pageIds = array_keys(soyshop_get_page_list());

		$cnf = array();
		foreach($pageIds as $pageId){
			$cnf[$pageId] = self::OFF;
		}

		return $cnf;
	}

	public static function savePageDisplayConfig(array $values){
		$pageIds = array_keys(soyshop_get_page_list());

		$cnf = array();
		foreach($pageIds as $pageId){
			$cnf[$pageId] = (in_array($pageId, $values)) ? self::ON : self::OFF;
		}

		SOYShop_DataSets::put("access_restriction.display", $cnf);
	}

	public static function getConfig(){
		return self::_config();
	}

	public static function saveConfig($values){
		$values["day"] = soyshop_convert_number($values["day"], 3);
		SOYShop_DataSets::put("access_restriction.config", $values);
	}

	private static function _config(){
		return SOYShop_DataSets::get("access_restriction.config", array(
			"day" => 3
		));
	}
}
