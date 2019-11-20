<?php

class SOYInquiryUtil{

	public static function switchConfig(){

		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		//SQLite
		if(SOYCMS_DB_TYPE == "sqlite"){
			$dsn = "sqlite:" . CMS_COMMON . "db/shop.db";
		//MySQL版
		}else{
			//サイト側にSOY Inquiryのデータベースを持つ場合
			if(defined("SOYINQUIRY_USE_SITE_DB") && SOYINQUIRY_USE_SITE_DB){
				$dsn = ADMIN_DB_DSN;
			//通常版
			}else{
				$dsn = $old["dsn"];
			}
		}

		$rootDir = str_replace("/inquiry/", "/shop/", $old["root"]);
		$entityDir = str_replace("/inquiry/", "/shop/", $old["entity"]);

		SOY2::RootDir($rootDir);
		SOY2DAOConfig::DaoDir($entityDir);
		SOY2DAOConfig::EntityDir($entityDir);
		SOY2DAOConfig::Dsn($dsn);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

		return $old;
	}

	public static function switchSOYShopConfig($shopId="shop"){

		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		if(is_null($shopId)) $shopId = "shop";
		$soyshopWebapp = dirname(CMS_COMMON) . "/soyshop/webapp/";
		include_once($soyshopWebapp."conf/shop/" . $shopId . ".conf.php");

		$entityDir = $soyshopWebapp . "src/domain/";

		SOY2::RootDir($soyshopWebapp . "/src/");
		SOY2DAOConfig::DaoDir($entityDir);
		SOY2DAOConfig::EntityDir($entityDir);
		SOY2DAOConfig::Dsn(SOYSHOP_SITE_DSN);
		SOY2DAOConfig::user(SOYSHOP_SITE_USER);
		SOY2DAOConfig::pass(SOYSHOP_SITE_PASS);


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

	/**
	 * SOY Shopがインストールされているか？
	 * @return boolen
	 */
	public static function checkSOYShopInstall(){
		return (file_exists(dirname(CMS_COMMON) . "/soyshop/"));
	}

	public static function getSOYShopSiteId(){
		if(!defined("SOYSHOP_SITE_ID")){
			$old = self::switchConfig();

			$siteDao = SOY2DAOFactory::create("SOYShop_SiteDAO");
			try{
				$site = $siteDao->getById(SOYINQUERY_SOYSHOP_CONNECT_SITE_ID);
			}catch(Exception $e){
				$site = new SOYShop_Site();
			}
			define("SOYSHOP_SITE_ID", $site->getSiteId());

			self::resetConfig($old);
		}

		return SOYSHOP_SITE_ID;
	}
}
