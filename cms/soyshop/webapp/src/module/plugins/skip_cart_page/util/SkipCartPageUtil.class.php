<?php

class SkipCartPageUtil {

	/**
	 * @return array
	 */
	public static function getConfig(){
		return self::_config();
	}

	/**
	 * @param array
	 */
	public static function saveConfig(array $values){
		if(!isset($values["skip"]) || !is_array($values["skip"])) $values["skip"] = array();
		SOYShop_DataSets::put("skip_cart_page.config", $values);
	}

	/**
	 * @param int
	 * @return bool
	 */
	public static function isSkip(int $pageNum){
		$cnf = self::_config();
		if(!isset($cnf["skip"]) || !is_array($cnf["skip"]) || !count($cnf["skip"])) return false;
		return is_numeric(array_search($pageNum, $cnf["skip"]));
	}

	/**
	 * @return string
	 */
	public static function getPaymentModuleConfig(){
		$cnf = self::_config();
		return (isset($cnf["payment"])) ? (string)$cnf["payment"] : "";
	}

	/**
	 * @return string
	 */
	public static function getDeliveryModuleConfig(){
		$cnf = self::_config();
		return (isset($cnf["delivery"])) ? (string)$cnf["delivery"] : "";
	}

	public static function getInstalledPaymentModuleList(){
		$list = self::_installedPluginList();
		return (isset($list[SOYShop_PluginConfig::PLUGIN_TYPE_PAYMENT])) ? $list[SOYShop_PluginConfig::PLUGIN_TYPE_PAYMENT] : array();
	}

	public static function getInstalledDeliveryModuleList(){
		$list = self::_installedPluginList();
		return (isset($list[SOYShop_PluginConfig::PLUGIN_TYPE_DELIVERY])) ? $list[SOYShop_PluginConfig::PLUGIN_TYPE_DELIVERY] : array();
	}

	private static function _config(){
		return SOYShop_DataSets::get("skip_cart_page.config", array(
			"skip" => array(),
			"payment" => "",
			"delivery" => ""
		));
	}

	/**
	 * @return array
	 */
	private static function _installedPluginList(){
		static $l;
		if(is_null($l)){
			$l = array();
			try{
				$res = soyshop_get_hash_table_dao("plugin")->executeQuery(
					"SELECT plugin_id as id, plugin_type as type FROM soyshop_plugins ".
					"WHERE is_active = :isActive ".
					"AND plugin_type IN ('".SOYShop_PluginConfig::PLUGIN_TYPE_PAYMENT."', '".SOYShop_PluginConfig::PLUGIN_TYPE_DELIVERY."')",
					array(":isActive" => SOYShop_PluginConfig::PLUGIN_ACTIVE)
				);
			}catch(Exception $e){
				$res = array();
			}

			if(count($res)){
				foreach($res as $v){
					if(!isset($l[$v["type"]])) $l[$v["type"]] = array();
					$l[$v["type"]][$v["id"]] = self::_getModuleName($v["id"]);
				}
			}
		}

		return $l;
	}

	/**
	 * @param string
	 * @return string
	 */
	private static function _getModuleName(string $moduleId){
		return soyshop_get_plugin_object($moduleId)->getName();
	}
}
