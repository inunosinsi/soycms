<?php

class TagCloudInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//初期化時のみテーブルを作成する
		$sql = self::_sql();
		$dao = new SOY2DAO();

		$array = preg_split('/CREATE TABLE/', $sql, -1, PREG_SPLIT_NO_EMPTY) ;

		foreach($array as $value){
			$sql = "create table " . trim($value);
			try{
				$dao->executeQuery($sql);
			}catch(Exception $e){
				//
			}
		}
	}

	function onUnInstall(){
		//アンインストールしてもテーブルは残す
	}

	/**
	 * @return String sql for init
	 */
	private function _sql(){
		return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "tag_cloud", "TagCloudInstall");
