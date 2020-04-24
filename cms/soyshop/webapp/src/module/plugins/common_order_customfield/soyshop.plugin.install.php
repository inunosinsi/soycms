<?php
class CommonOrderCustomfieldInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//初期化時のみテーブルを作成する
		$dao = new SOY2DAO();

		try{
			$dao->executeQuery(self::getSQL());
		}catch(Exception $e){
			//データベースが存在する場合はスルー
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
SOYShopPlugin::extension("soyshop.plugin.install", "common_order_customfield", "CommonOrderCustomfieldInstall");
