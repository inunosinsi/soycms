<?php
class SlipNumberInstall extends SOYShopPluginInstallerBase{
	
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
		//アンインストールしてもテーブルは残す
	}
		
	/**
	 * @return String sql for init
	 */
	function getSQL(){
		//オーダーカスタムフィールドのSQLを取得する
		$sql = file_get_contents(dirname(dirname(__FILE__)) . "/common_order_customfield/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
		return $sql;
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "slip_number", "SlipNumberInstall");
?>