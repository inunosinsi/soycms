<?php
//SOY2の設定
SOY2::RootDir(dirname(__FILE__) . "/src/");

//SOY2HTMLの設定
SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/");

//SOY2DAOの設定
SOY2DAOConfig::DaoDir(SOY2::RootDir() . "domain/");
SOY2DAOConfig::EntityDir(SOY2::RootDir() . "domain/");

if(defined("SOYMAIL_USE_SITE_DB") && SOYMAIL_USE_SITE_DB){
	
	if(!defined("_SITE_ROOT_")){
		//サイトDB側を使用する場合
		CMSApplication::import("domain.admin.Site");
		CMSApplication::import("util.UserInfoUtil");		
		
		if(UserInfoUtil::getSite()){
			
			//初期化チェックファイル（SQLite版の場合はデータベースファイルそのもの）
			define("SOYMAIL_DB_FILE", UserInfoUtil::getSiteDirectory() . ".db/soymail.db");			
			
			SOY2DAOConfig::Dsn(UserInfoUtil::getSite()->getDataSourceName());
			if(SOYCMS_DB_TYPE == "mysql"){
				SOY2DAOConfig::user(ADMIN_DB_USER);
				SOY2DAOConfig::pass(ADMIN_DB_PASS);
			}
		}else{
			//どのサイトのSOY Mailにログインさせるか？の選択画面へ
			SOY2PageController::redirect("../admin/index.php/Site/Application/?appId=" . APPLICATION_ID);
		}
	}
}else{	
	//初期化チェックファイル（SQLite版の場合はデータベースファイルそのもの）
	define("SOYMAIL_DB_FILE", CMS_COMMON . "db/soymail.db");
	
	if(SOYCMS_DB_TYPE == "sqlite"){
		SOY2DAOConfig::Dsn("sqlite:" . SOYMAIL_DB_FILE);
	}else{
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);
	}
}
//PHP
mb_internal_encoding("UTF-8");

//Mailアプリケーションの設定
//binディレクトリの設定
define("SOYMAIL_BIN_DIR", dirname(__FILE__) . "/bin");

SOY2::import("util.SOYMailUtil");
?>