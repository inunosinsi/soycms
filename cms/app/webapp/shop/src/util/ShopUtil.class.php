<?php

class ShopUtil{

	const SHOP_APP_ID = "shop";

	/**
	 * App操作者でログインできるショップが１つだけならそこにログインする
	 */
	public static function tryDefaultLogin(){
		CMSApplication::import("domain.admin.Site");

		$session = SOY2ActionSession::getUserSession();

		$userId = $session->getAttribute("userid");
		$siteRoles = self::getSiteRole($userId);
		if(count($siteRoles)==1){
			$siteRole = array_shift($siteRoles);
			$site = self::_getSite($siteRole->getSiteId());

			//直前でサイトの管理権限をチェックする
			$session->setAttribute("app_shop_auth_level",$siteRole->getIsLimitUser());

			//App操作者の場合はログインする
			if(self::isAppUser($userId)){
				if($site && $site->getSiteType() == Site::TYPE_SOY_SHOP){
					SOY2PageController::redirect("../soyshop?site_id=".$site->getSiteId());
				}
			}
		}
		return false;
	}

	/**
	 * 指定したユーザーのAppRoleの権限を返す
	 */
	private static function getAppRole($userId){
		$old = self::switchConfig();
		self::setCMSDsn();

		try{
			$approle = SOY2DAOFactory::create("admin.AppRoleDAO")->getRole(self::SHOP_APP_ID,$userId);
		}catch(Exception $e){
			$approle = new AppRole();
		}
		self::resetConfig($old);

		return $approle->getAppRole();
	}

	/**
	 * 指定したユーザーのSiteRoleを返す
	 */
	private static function getSiteRole($userId){
		$old = self::switchConfig();
		self::setCMSDsn();

		try{
			$siteRoles = SOY2DAOFactory::create("admin.SiteRoleDAO")->getByUserId($userId);
		}catch(Exception $e){
			$siteRoles = array();
		}
		self::resetConfig($old);

		return $siteRoles;
	}

	/**
	 * 指定したサイトの管理権限を返す
	 */
	public static function getSiteAuthLevel($siteId,$userId){
		$siteRoles = self::getSiteRole($userId);
	}

	/**
	 * サイト情報を返す
	 */
	public static function getSites(){
		$old = self::switchConfig();
		self::setCMSDsn();

		try{
			$sites = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteType(Site::TYPE_SOY_SHOP);
		}catch(Exception $e){
			$sites = array();
		}
		self::resetConfig($old);

		return $sites;
 	}
	public static function getSiteById($siteId){
 		return self::_getSite($siteId);
 	}

	private static function _getSite($siteId){
		$old = self::switchConfig();
		self::setCMSDsn();

		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getById($siteId);
		}catch(Exception $e){
			$site = new Site();
		}
		self::resetConfig($old);

		return $site;
	}

	public static function getSiteBySiteId($siteId){
		$old = self::switchConfig();
		self::setCMSDsn();

		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
		}catch(Exception $e){
			$site = new Site();
		}
		self::resetConfig($old);

		return $site;
	}

	/**
	 * 初期管理者かどうか
	 */
	public static function isDefaultUser(){
		return SOY2ActionSession::getUserSession()->getAttribute("isdefault");
	}

	/**
	 * App管理者かどうか
	 */
	public static function isAppSuperUser($userId){
		$appRole = self::getAppRole($userId);
		return ($appRole == AppRole::APP_SUPER_USER);
	}

	/**
	 * App操作者かどうか
	 */
	public static function isAppUser($userId){
		$appRole = self::getAppRole($userId);
		return ($appRole == AppRole::APP_USER);
	}

	/**
	 * ルート設定用のサイトURLを返す
	 * @param object Site
	 * @return string site_url
	 */
	public static function getSiteUrl(Site $site){
		$siteUrl = $site->getUrl();
		if($site->getIsDomainRoot()){
			$shopId = "/" . SOYSHOP_ID . "/";
			$posId = strrpos($siteUrl, $shopId);
			if((strlen($siteUrl) - strlen($shopId)) == $posId){
				$siteUrl = substr($siteUrl, 0, $posId) . "/";
			}
		}
		return $siteUrl;
	}

	/**
	 * 現在のDB接続情報を返す
	 */
	public static function switchConfig(){

		$old = array();

		$old["rooDir"] = SOY2::RootDir();
		$old["daoDir"] = SOY2DAOConfig::DaoDir();
		$old["entityDir"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		return $old;
	}

	/**
	 * 保存しておいたDB接続情報に戻す
	 */
	public static function resetConfig($old){

		SOY2::RootDir($old["rooDir"]);
		SOY2DAOConfig::DaoDir($old["daoDir"]);
		SOY2DAOConfig::EntityDir($old["entityDir"]);
		SOY2DAOConfig::Dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

	}

	/**
	 * 管理側のDBに接続する
	 */
	public static function setCMSDsn(){

		SOY2::RootDir(CMS_COMMON);
		SOY2DAOConfig::DaoDir(CMS_COMMON."domain/");
		SOY2DAOConfig::EntityDir(CMS_COMMON."domain/");
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);

	}

	/**
	 * ショップのDBに接続する
	 */
	public static function setShopSiteDsn(Site $site){

		//SOYShop_ShopConfig::saveで利用する定数の設定
		if(!defined("SOYSHOP_ID")) define("SOYSHOP_ID", $site->getSiteId());
		if(!defined("SOYSHOP_ROOT")) define("SOYSHOP_ROOT", str_replace("common", "soyshop", CMS_COMMON));
		if(!defined("SOYSHOP_ADMIN_URL")) define("SOYSHOP_ADMIN_URL", CMSApplication::getShopRoot() . "index.php");

		$shopDir = str_replace("common/","soyshop/webapp/",CMS_COMMON);

		SOY2::RootDir($shopDir."src/");
		SOY2DAOConfig::DaoDir($shopDir."src/domain/");
		SOY2DAOConfig::EntityDir($shopDir."src/domain/");
		SOY2DAOConfig::Dsn($site->getDataSourceName());

		$pass = "";
		$user = "";
		include_once($shopDir."/conf/shop/".$site->getSiteId().".conf.php");
		if(defined($site->getSiteId()."_SOYSHOP_SITE_PASS")){
			//mysql
			eval("\$pass = ".$site->getSiteId()."_SOYSHOP_SITE_PASS;");
			eval("\$user = ".$site->getSiteId()."_SOYSHOP_SITE_USER;");
		}

		SOY2DAOConfig::user($user);
		SOY2DAOConfig::pass($pass);

		//SOYShop_ShopConfig::saveで利用するクラスの読み込み
		SOY2::import("domain.config.SOYShop_DataSets");
		SOY2::import("domain.config.SOYShop_ShopConfig");
	}

	/**
	 * ショップの定数宣言
	 */
	public static function setShopSiteConfig(Site $site){
		define("SOYSHOP_ID",$site->getSiteId());
		define("SOYSHOP_SITE_DIRECTORY",$site->getPath());
		$webappDir = str_replace("common/","soyshop/webapp/",CMS_COMMON);
		define("SOYSHOP_WEBAPP",$webappDir);
		define("SOYSHOP_SITE_CONFIG_FILE",$webappDir . "conf/shop/" . SOYSHOP_ID . ".conf.php");
		return $webappDir;
	}
}
