<?php

class SupplierManagerInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//初期化時のみテーブルを作成する
		$sql = self::getSQL();
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
	private function getSQL(){
		return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "supplier_manager", "SupplierManagerInstall");
