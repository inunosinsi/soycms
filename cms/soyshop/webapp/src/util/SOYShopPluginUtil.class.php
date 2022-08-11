<?php

class SOYShopPluginUtil {

	/**
	 * @param string
	 * @return bool
	 */
    public static function checkIsActive(string $pluginId){
		// インストール済みのプラグインIDを一括で取得しておく
		static $commons, $others;	//配列内でpluginIdの検索を省力化する為にcommon_とそれ以外で配列を分けておく

		// 既にインストール状況を確認したplugin_idは2度確認しない
		if(defined("IS_INSTALL_" . $pluginId)) return constant("IS_INSTALL_" . $pluginId);

		if(!is_array($commons)){
			$commons = array();
			$others = array();

			if(!class_exists("SOYShop_PluginConfig")) SOY2::import("domain.plugin.SOYShop_PluginConfig");
			$sql = "SELECT plugin_id FROM soyshop_plugins WHERE is_active = " . SOYShop_PluginConfig::PLUGIN_ACTIVE;
			// 公開側で関係ない(arrival_から始まるpluginId等)を省く
			if(!defined("SOYSHOP_ADMIN_PAGE") || !SOYSHOP_ADMIN_PAGE){
				$sql .= " AND plugin_id NOT LIKE 'arrival_%'";
			}

			try{
				$res = soyshop_get_hash_table_dao("plugin")->executeQuery($sql);
			}catch(Exception $e){
				$res = array();
			}
			
			if(count($res)){
				foreach($res as $v){
					$plgId = $v["plugin_id"];
					if(preg_match('/common_/', $v["plugin_id"], $_tmp)){	// commonsにまとめる
						$commons[] = substr($plgId, 7);
					}else{
						$others[] = $plgId;
					}
				}
			}
		}

		if(preg_match('/common_/', $pluginId, $_tmp)){
			$b = (is_numeric(array_search(substr($pluginId, 7), $commons)));
		}else{
			$b = (is_numeric(array_search($pluginId, $others)));
		}
		define("IS_INSTALL_" . $pluginId, $b);	//同じplugin_idで何度もインストールの状況を確認しない
		return constant("IS_INSTALL_" . $pluginId);
	}

	/**
	 * @return booll
	 */
    public static function checkPluginListFile(){
		return (file_exists(SOY2::RootDir() . "logic/init/plugin/plugin.ini"));
	}

	/**
	 * @param string|int?
	 * @return SOYShop_PluginConfig
	 */
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
