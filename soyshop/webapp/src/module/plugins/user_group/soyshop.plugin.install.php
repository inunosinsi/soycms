<?php
class UserGroupInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		$sqls = self::getSQL();
        $dao = new SOY2DAO();

        if(preg_match_all('/Create.*?;/mis', $sqls, $tmp)){
            if(count($tmp[0])){
                foreach($tmp[0] as $sql){
                    try{
                        $dao->executeQuery(trim($sql));
                    }catch(Exception $e){
                        //データベースが存在する場合はスルー
                    }
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
         return file_get_contents(dirname(__FILE__) . "/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
     }
}
SOYShopPlugin::extension("soyshop.plugin.install", "user_group", "UserGroupInstall");
