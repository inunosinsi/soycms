<?php
class CommonFavoriteItemInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//初期化時のみテーブルを作成する
		$sql = $this->getSQL();
		$dao = new SOY2DAO();

		try{
			$dao->executeQuery($sql);
		}catch(Exception $e){
			//データベースが存在する場合はスルー
		}

	}

	function onUnInstall(){
		//データが無い場合はテーブルを削除
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT id FROM soyshop_favorite_item LIMIT 1;");
			if(!count($res)){
				$dao->executeUpdateQuery("DROP TABLE soyshop_favorite_item");
			}
		}catch(Exception $e){
			//
		}
	}

	/**
	 * @return String sql for init
	 */
	function getSQL(){
		$sql = file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
		return $sql;
	}
}
SOYShopPlugin::extension("soyshop.plugin.install","common_favorite_item","CommonFavoriteItemInstall");
