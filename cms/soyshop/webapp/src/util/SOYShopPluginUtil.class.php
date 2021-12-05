<?php

class SOYShopPluginUtil {

    public static function checkIsActive($pluginId){
		if(!function_exists("soyshop_get_plugin_object")) SOY2::import("base.func.dao", ".php");
		return (soyshop_get_plugin_object($pluginId)->getIsActive() == SOYShop_PluginConfig::PLUGIN_ACTIVE);
    }

    public static function checkPluginListFile(){
		return (file_exists(SOY2::RootDir() . "logic/init/plugin/plugin.ini"));
	}

	public static function getPluginById($pluginId){
		if(!function_exists("soyshop_get_plugin_object")) SOY2::import("base.func.dao", ".php");
		return soyshop_get_plugin_object($pluginId);
	}

	/**
	 * soyshop.order.searchで配列で受け取ったparamsを文字列に変換する
	 * paramsの値が一つで、その一つの値のインデックスが0の場合は文字列に変換する
	 * @param array
	 * @return string
	 */
	public static function convertArray2String(array $params){
		if(count($params) !== 1) return "";
		$idx = key($params);
		if(!is_numeric($idx) || $idx !== 0) return "";
		if(!is_string($params[$idx])) return "";
		return htmlspecialchars((string)$params[0], ENT_QUOTES, "UTF-8");
	}

	/**
	 * @param string $str, string $div
	 * @return array
	 * $divで指定した方法でキーワードを分割する
	 */
	public static function devideKeywords(string $str, string $div=" "){
		if($div = " ") $str = str_replace("　", " ", $str);	//全角スペースを半角スペースに変えておく
		return (strlen($str)) ? explode($div, $str) : array();
	}
}
