<?php
class CampaignInstall extends SOYShopPluginInstallerBase{
	
	function onInstall(){

		//初期化時のみテーブルを作成する
		$sql = $this->getSQL();
		$dao = new SOY2DAO();
		
		try{
			$dao->executeQuery($sql);
		}catch(Exception $e){
			//
		}	
	}
	
	function onUnInstall(){
		//アンインストールしてもテーブルは残す
	}
		
	/**
	 * @return String sql for init
	 */
	function getSQL(){
		$sql = file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
		return $sql;
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "campaign", "CampaignInstall");
?>