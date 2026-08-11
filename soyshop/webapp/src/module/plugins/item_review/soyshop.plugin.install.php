<?php
class ItemReviewInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		//初期化時のみテーブルを作成する
		$dao = new SOY2DAO();
		
		$sqls = preg_split('/create table/', self::_sqls(), -1, PREG_SPLIT_NO_EMPTY) ;
		foreach($sqls as $sql){
			try{
				$dao->executeQuery("create table " . trim($sql));
			}catch(Exception $e){
				//
			}
		}
	}

	function onUnInstall(){
		//データが無い場合はテーブルを削除
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT id FROM soyshop_item_review LIMIT 1;");
			if(!count($res)){
				$dao->executeUpdateQuery("DROP TABLE soyshop_item_review");
				$dao->executeUpdateQuery("DROP TABLE soyshop_review_point");
			}
		}catch(Exception $e){
			//
		}
	}

	/**
	 * @return String sql for init
	 */
	private function _sqls(){
		return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "item_review", "ItemReviewInstall");
