<?php
class DownloadAssistantInstall extends SOYShopPluginInstallerBase{
	
	function onInstall(){
		//初期化時のみテーブルを作成する
		$sql = $this->getSQL();
		$dao = new SOY2DAO();
		
		try{
			$dao->executeQuery($sql);
		}catch(Exception $e){
			//データベースが存在する場合はスルー
		}
		
		//downloadディレクトリを作成する
		$dir = SOYSHOP_SITE_DIRECTORY . "download/";
		
		if(!is_dir($dir)){
			mkdir($dir);
			
			//.htaccessを作成する
			file_put_contents($dir . ".htaccess", "deny from all");
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
SOYShopPlugin::extension("soyshop.plugin.install", "download_assistant", "DownloadAssistantInstall");
?>