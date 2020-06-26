<?php
//SOY2の設定
SOY2::RootDir(dirname(__FILE__) . "/src/");

//SOY2HTMLの設定
SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/");

//SOY2DAOの設定
SOY2DAOConfig::DaoDir(SOY2::RootDir() . "domain/");
SOY2DAOConfig::EntityDir(SOY2::RootDir() . "domain/");

$dbMode = SOYCMS_DB_TYPE;

if(defined("SOYMAIL_USE_SITE_DB") && SOYMAIL_USE_SITE_DB){

	if(!defined("_SITE_ROOT_")){
		//サイトDB側を使用する場合
		CMSApplication::import("domain.admin.Site");
		CMSApplication::import("util.UserInfoUtil");

		if(UserInfoUtil::getSite()){

			//初期化チェックファイル（SQLite版の場合はデータベースファイルそのもの）
			define("SOYMAIL_DB_FILE", UserInfoUtil::getSiteDirectory() . ".db/soymail.db");

			SOY2DAOConfig::Dsn(UserInfoUtil::getSite()->getDataSourceName());
			$dbMode = (strpos(UserInfoUtil::getSite()->getDataSourceName(), "sqlite") === 0) ? "sqlite" : "mysql";
			if($dbMode == "mysql"){
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

	if($dbMode == "sqlite"){
		SOY2DAOConfig::Dsn("sqlite:" . SOYMAIL_DB_FILE);
	}else{
		//Execで送信する時、MySQLの設定が読み込まれていないらしい。
		if(!defined("ADMIN_DB_DSN")){
			if(file_exists(CMS_COMMON . "config/db/mysql.php")) include_once(CMS_COMMON . "config/db/mysql.php");
		}
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);
	}
}

//DBモードを定義しておく
define("SOYMAIL_DB_MODE", $dbMode);

//PHP
mb_internal_encoding("UTF-8");

//Mailアプリケーションの設定
//binディレクトリの設定
define("SOYMAIL_BIN_DIR", dirname(__FILE__) . "/bin");

SOY2::import("util.SOYMailUtil");
