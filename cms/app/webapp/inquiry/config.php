<?php
//SOY2の設定
SOY2::RootDir(dirname(__FILE__) . "/src/");

//SOY2HTMLの設定
SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/");

//SOY2DAOの設定
SOY2DAOConfig::DaoDir(SOY2::RootDir() . "domain/");
SOY2DAOConfig::EntityDir(SOY2::RootDir() . "domain/");

$dbMode = SOYCMS_DB_TYPE;

//データベース関連の設定
if(defined("SOYINQUIRY_USE_SITE_DB") && SOYINQUIRY_USE_SITE_DB){
	/* サイトのデータベースを使う */
	//管理画面のみ指定する必要がある（公開側ではすでにサイトのDSNに接続済み）

	if(!defined("_SITE_ROOT_")){
		CMSApplication::import("domain.admin.Site");
		CMSApplication::import("util.UserInfoUtil");

		if(UserInfoUtil::getSite()){
			//初期化チェックファイル（SQLite版の場合はデータベースファイルそのもの）
			define("SOYINQUIRY_DB_FILE", UserInfoUtil::getSiteDirectory() . ".db/".APPLICATION_ID.".db");

			SOY2DAOConfig::Dsn(UserInfoUtil::getSite()->getDataSourceName());
			$dbMode = (strpos(UserInfoUtil::getSite()->getDataSourceName(), "sqlite") === 0) ? "sqlite" : "mysql";
			if($dbMode == "mysql"){
				SOY2DAOConfig::user(ADMIN_DB_USER);
				SOY2DAOConfig::pass(ADMIN_DB_PASS);
			}
		}else{	//@ToDo 初期管理者のみ、この画面に遷移させたい
			//どのサイトのSOY Inquiryにログインさせるか？の選択画面へ
			SOY2PageController::redirect("../admin/index.php/Site/Application/?appId=" . APPLICATION_ID);
		}
	}
}else{
	//初期化チェックファイル（SQLite版の場合はデータベースファイルそのもの）
	define("SOYINQUIRY_DB_FILE", CMS_COMMON . "db/".APPLICATION_ID.".db");

	/* 専用のデータベースを使う（従来通り） */
	if($dbMode == "sqlite"){
		SOY2DAOConfig::Dsn("sqlite:" . SOYINQUIRY_DB_FILE);
	}else{
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);
	}
}

//SOY Mail連携
if($dbMode == "sqlite"){
	//SOY Mailがサイト側にDBを持つか

	//SQLiteでサイト側にDBを持つ
	if(defined("SOYMAIL_USE_SITE_DB") && SOYMAIL_USE_SITE_DB){
		/* サイトのデータベースを使う */
		//管理画面のみ指定する必要がある（公開側ではすでにサイトのDSNに接続済み）
		CMSApplication::import("domain.admin.Site");
		CMSApplication::import("util.UserInfoUtil");
		//データはサイトのDBに保存。専用のsoymail.dbではない。
		define("SOYMAIL_DSN","sqlite:" . UserInfoUtil::getSiteDirectory() . ".db/sqlite.db");

	}else{
		//SQLite で、管理側にDBを持つ
		if(file_exists(CMS_COMMON . "db/soymail.db")){
			define("SOYMAIL_DSN","sqlite:" . CMS_COMMON . "db/soymail.db");
		}
	}


}else{

	if(defined("SOYMAIL_USE_SITE_DB") && SOYMAIL_USE_SITE_DB){
		/* MySQLでサイトのデータベースを使う */
		//管理画面のみ指定する必要がある（公開側ではすでにサイトのDSNに接続済み）

		CMSApplication::import("domain.admin.Site");
		CMSApplication::import("util.UserInfoUtil");

		SOY2DAOConfig::Dsn(UserInfoUtil::getSite()->getDataSourceName());
		if(SOYCMS_DB_TYPE == "mysql"){
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
		}

		define("SOYMAIL_DSN",SOY2DAOConfig::Dsn(UserInfoUtil::getSite()->getDataSourceName()));


	}else{
		//MySQLで、管理側にDBを持つ//もともとの仕様
		define("SOYMAIL_DSN", ADMIN_DB_DSN);
	}
}

//DBモードを定義しておく
define("SOYINQUIRY_DB_MODE", $dbMode);

//PHP
mb_internal_encoding("UTF-8");

//font name
define("SOYINQUIRY_FONT_NAME","tuffy.ttf");

//ファイルのアップロード先のルートディレクトリ 末尾に/なし
$doc_root = str_replace("\\", "/", $_SERVER["DOCUMENT_ROOT"]);
if(strlen($doc_root)>0 && $doc_root[strlen($doc_root)-1] == "/") $doc_root = substr($doc_root, 0, -1);
define("SOY_INQUIRY_UPLOAD_ROOT_DIR", $doc_root);

SOY2::import("util.SOYInquiryUtil");
