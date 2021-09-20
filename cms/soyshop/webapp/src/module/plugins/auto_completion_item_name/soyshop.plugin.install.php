<?php
class AutoCompletionInstall extends SOYShopPluginInstallerBase{

    function onInstall(){
		//初期化時のみテーブルを作成する
		$dao = new SOY2DAO();
		$sqls = preg_split('/CREATE TABLE/', self::_sqls(), -1, PREG_SPLIT_NO_EMPTY) ;
		foreach($sqls as $sql){
			try{
				$dao->executeQuery("create table " . trim($sql));
			}catch(Exception $e){
				//
			}
		}
    }

    function onUnInstall(){
        //アンインストールしてもテーブルは残す
    }

    /**
     * @return String sql for init
     */
    private function _sqls(){
        return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
    }
}
SOYShopPlugin::extension("soyshop.plugin.install", "auto_completion_item_name", "AutoCompletionInstall");
