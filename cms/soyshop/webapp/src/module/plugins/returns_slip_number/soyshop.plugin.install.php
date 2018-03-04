<?php
class ReturnsSlipNumberInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//初期化時のみテーブルを作成する
		$dao = new SOY2DAO();

		try{
			$dao->executeQuery(self::getSQL());
		}catch(Exception $e){
			//データベースが存在する場合はスルー
		}

		try{
			$dao->executeQuery(self::getTableSQL());
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
		//オーダーカスタムフィールドのSQLを取得する
		return file_get_contents(dirname(dirname(__FILE__)) . "/common_order_customfield/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}

	private function getTableSQL(){
		return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "returns_slip_number", "ReturnsSlipNumberInstall");
