<?php
class DepositManagerInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//初期化時のみテーブルを作成する
		$dao = new SOY2DAO();

		$array = preg_split('/CREATE TABLE/', self::getSQL(), -1, PREG_SPLIT_NO_EMPTY) ;

		$isFirst = true;
		foreach($array as $value){
			$sql = "CREATE TABLE " . trim($value);
			try{
				$dao->executeQuery($sql);
			}catch(Exception $e){
				$isFirst = false;
			}
		}

		//初回実行時
		if($isFirst){
			SOY2::import("module.plugins.deposit_manager.domain.SOYShop_DepositManagerSubjectDAO");
			$dao = SOY2DAOFactory::create("SOYShop_DepositManagerSubjectDAO");
			$list = explode("\n", file_get_contents(dirname(__FILE__) . "/subject/list.txt"));
			foreach($list as $subject){
				$subject = trim($subject);
				if(!strlen($subject)) continue;

				$obj = new SOYShop_DepositManagerSubject();
				$obj->setSubject($subject);
				try{
					$dao->insert($obj);
				}catch(Exception $e){

				}
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
		return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE.".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "deposit_manager", "DepositManagerInstall");
