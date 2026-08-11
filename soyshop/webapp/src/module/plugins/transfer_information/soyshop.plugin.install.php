<?php
class TransferInformationInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		$dao = new SOY2DAO();
		try{
			$dao->executeQuery(self::_sql());
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
	private function _sql(){
		return file_get_contents(dirname(dirname(__FILE__)) . "/common_user_customfield/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install","transfer_information","TransferInformationInstall");
