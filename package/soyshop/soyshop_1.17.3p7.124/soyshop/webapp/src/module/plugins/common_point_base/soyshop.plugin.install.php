<?php
class CommonPointBaseInstall extends SOYShopPluginInstallerBase{
	
	function onInstall(){
		//初期化時のみテーブルを作成する
		$sqls[] = $this->getSQL();
		$sqls[] = $this->getSQL(2);
		$dao = new SOY2DAO();
		
		foreach($sqls as $sql){
			try{
				$dao->executeQuery($sql);
			}catch(Exception $e){
				//データベースが存在する場合はスルー
			}
		}
	}
	
	function onUnInstall(){
		//アンインストールしてもテーブルは残す
	}
		
	/**
	 * @return String sql for init
	 */
	function getSQL($version = ""){
		$sql = file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . $version . ".sql");
		return $sql;
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "common_point_base", "CommonPointBaseInstall");
?>