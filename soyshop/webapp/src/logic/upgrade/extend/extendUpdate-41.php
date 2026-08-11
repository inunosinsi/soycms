<?php

//カスタムサーチフィールドがインストールされているか？
SOY2::import("util.SOYShopPluginUtil");
if(SOYShopPluginUtil::checkIsActive("custom_search_field")){
	$sqls = file_get_contents(SOY2::RootDir() . "module/plugins/custom_search_field/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	$dao = new SOY2DAO();

	if(preg_match_all('/CREATE.*?;/mis', $sqls, $tmp)){
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
