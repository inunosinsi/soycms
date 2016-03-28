<?php

class SOYListCommon{
	
	public static function setConfig(){
		
		$old = array();
		
		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();
		
		$newRoot = str_replace("common/","app/webapp/list/src/",$old["root"]);
		SOY2::RootDir($newRoot);
		SOY2DAOConfig::DaoDir($newRoot."domain/");
		SOY2DAOConfig::EntityDir($newRoot."domain/");
		
		if(SOYCMS_DB_TYPE == "sqlite"){
			
			SOY2DAOConfig::Dsn("sqlite:" . $old["root"] . "db/list.db");
		}else{
			include_once($old["root"] . "config/db/mysql.php");
			SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
		}
		return $old;
	}
	
	public static function resetConfig($old){
		SOY2::RootDir($old["root"]);
		SOY2DAOConfig::DaoDir($old["dao"]);
		SOY2DAOConfig::EntityDir($old["entity"]);
		SOY2DAOConfig::Dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);
	}
}

?>