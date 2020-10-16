<?php

class MaintenancePageUtil {

	//メンテナンスページは既に作成されているか？
	public static function isMaintenancePage(){
		$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		try{
			$page = $dao->getByUri(SOYShop_Page::MAINTENANCE);
			return (!is_null($page->getId()));
		}catch(Exception $e){
			return false;
		}
	}

	//メンテナンスページを有効にしているか？
	public static function checkActive(){
		$cnf = self::_config();
		return (isset($cnf["on"]) && is_numeric($cnf["on"]) && (int)$cnf["on"] === 1);
	}

	public static function getConfig(){
		return self::_config();
	}

	private static function _config(){
		return SOYShop_DataSets::get("maintenance_page.config", array(
			"on" => 0
		));
	}

	public static function saveConfig($values){
		$values["on"] = (isset($values["on"]) && is_numeric($values["on"])) ? (int)$values["on"] : 0;
		return SOYShop_DataSets::put("maintenance_page.config", $values);
	}
}
