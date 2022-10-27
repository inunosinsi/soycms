<?php

class SOYMailUtil{
	
	public static function switchConfig(){
		
		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();
				
		if(SOYCMS_DB_TYPE == "sqlite"){
			$dsn = str_replace("soymail.db","shop.db",$old["dsn"]);
		}else{
			//dsnは同じ
			$dsn = $old["dsn"];
		}
		
		$rootDir = str_replace("/mail/","/shop/",$old["root"]);
		$entityDir = str_replace("/mail/","/shop/",$old["entity"]);
		
		SOY2::RootDir($rootDir);
		SOY2DAOConfig::DaoDir($entityDir);
		SOY2DAOConfig::EntityDir($entityDir);
		SOY2DAOConfig::Dsn($dsn);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

		return $old;
	}
	
	public static function switchSOYShopConfig(){
		
		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();
				
		$soyshopWebapp = dirname(CMS_COMMON)."/soyshop/webapp/";
		include_once($soyshopWebapp."conf/shop/" . SOYSHOP_SITE_ID . ".conf.php");
		
		$entityDir = $soyshopWebapp . "src/domain/";
		
		SOY2::RootDir($soyshopWebapp . "src/");
		SOY2DAOConfig::DaoDir($entityDir);
		SOY2DAOConfig::EntityDir($entityDir);
		SOY2DAOConfig::Dsn(SOYSHOP_SITE_DSN);
		SOY2DAOConfig::user(SOYSHOP_SITE_USER);
		SOY2DAOConfig::pass(SOYSHOP_SITE_PASS);
		
		//SOYShop_Areaで必要なので、ここでインポートしておく
		SOY2::import("logic.plugin.SOYShopPlugin");
		
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