<?php
class DownloadAssistantInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//初期化時のみテーブルを作成する
		$dao = new SOY2DAO();

		try{
			$dao->executeQuery(self::_sql());
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
	private function _sql(){
		return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "download_assistant", "DownloadAssistantInstall");
