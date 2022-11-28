<?php
SOY2::RootDir(CMS_COMMON);
include_once(CMS_COMMON . "config/normal.php");
include_once(CMS_COMMON . "config/db/".SOYCMS_DB_TYPE.".php");

//SOY2の設定
SOY2::RootDir(dirname(__FILE__) . "/src/");
SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/");
SOY2DAOConfig::DaoDir(SOY2::RootDir() . "domain/");
SOY2DAOConfig::EntityDir(SOY2::RootDir() . "domain/");

//DSNの設定

if(SOYCMS_DB_TYPE == "sqlite"){
	define("SOYSHOP_COMMON_DSN", CMS_COMMON . "db/".APPLICATION_ID.".db");
	SOY2DAOConfig::Dsn("sqlite:" . SOYSHOP_COMMON_DSN);
}else{

	include_once(CMS_COMMON . "config/db/".SOYCMS_DB_TYPE.".php");
	SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
	SOY2DAOConfig::user(ADMIN_DB_USER);
	SOY2DAOConfig::pass(ADMIN_DB_PASS);

	define("SOYSHOP_COMMON_DSN", ADMIN_DB_DSN);

}

if(!defined("SOYSHOP_COMMON_DIR")){
	define("SOYSHOP_COMMON_DIR","");
}

SOY2::imports("domain.*");
SOY2::imports("base.*");
SOY2::import("util.ShopUtil");

/**
 * Shopのアクセス権限をチェックする
 */
ShopUtil::tryDefaultLogin();

/**
 * マルチショップ対応のための既存サイトの引き継ぎ
 */
if(file_exists(CMS_COMMON."db/soyshop.db")){

	$tmp = CMS_COMMON."db/soyshop.db";
	$oldDsn = SOY2DAOConfig::Dsn("sqlite:" . $tmp);

	$dao = new SOY2DAO();
	$sql = "select id from soyshop_site;";

	try{
		$res = $dao->executeQuery($sql,array());
		SOY2DAOConfig::Dsn($oldDsn);
	}catch(Exception $e){
		//update for multi
		$dao->releaseDataSource();

		$array = unserialize(file_get_contents($tmp));

		if(!is_array($array) || !array_key_exists("id",$array))return false;

		$siteId = $array["id"];
		$confPath = dirname(CMS_COMMON)."/soyshop/webapp/conf/shop/".$siteId.".conf.php";

		if(!file_exists($confPath))return false;
		include_once($confPath);

		$siteUrl = SOYSHOP_SITE_URL;
		$siteDir = SOYSHOP_SITE_DIRECTORY;
		if($siteDir[strlen($siteDir)-1] != "/")$siteDir .= "/";

		$siteDsn = "";
		if(defined("SOYSHOP_SITE_DSN")){
			$siteDsn = SOYSHOP_SITE_DSN;//mysql
		}else{
			$siteDsn = "sqlite:".$siteDir.".db/sqlite.db";//sqlite
		}

		$logic  = SOY2Logic::createInstance("logic.InitLogic");

		if(!$logic->checkSiteId($siteId)){
			//サイトIDがかぶっているから登録出来ない
			SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/_init/");
			CMSApplication::setTabs(array(
				array(
					"label" => "HOME",
					"href" => SOY2PageController::createLink("shop")
				)
			));
			//soyshop.dbは既存シングルサイトの証明なので消さない
			return;
		};

		@unlink($tmp);
		SOY2DAOConfig::Dsn($oldDsn);
		$logic->init();
		$logic->registSite($siteId,$siteDir,$siteUrl,$siteDsn);
		$logic->outputConfig($siteId,$siteDir,$siteUrl);

	}
}


//init
if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
	$logic  = SOY2Logic::createInstance("logic.InitLogic");
	$logic->init();
}
