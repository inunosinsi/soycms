<?php
/**
 * SOY Shop用Util
 */
class SOYShopUtil {

	/**
	 * SOY Shopがインストールされているか？
	 * @return boolen
	 */
	public static function checkSOYShopInstall(){
		$soyshopRoot = dirname(SOY2::RootDir()) . "/soyshop/";

		return (file_exists($soyshopRoot));
	}

	/**
	 *
	 */
	public static function switchShopMode($siteId){

		$old = array();

		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["cache"] = SOY2DAOConfig::DaoCacheDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		$old["page"] = SOY2HTMLConfig::PageDir();

		$soyshopRoot = dirname($old["root"]) . "/soyshop/";

		SOY2::RootDir($soyshopRoot . "webapp/src/");

		//SOYShop_ShopConfig::saveをするために必要な定数の設定をしておく
		include_once(dirname(SOY2::RootDir()) . "/conf/common.conf.php");

		SOY2DAOConfig::DaoDir(SOY2::RootDir() . "domain/");
		SOY2DAOConfig::EntityDir(SOY2::RootDir() . "domain/");
		SOY2DAOConfig::DaoCacheDir($soyshopRoot . "cache/");

		include_once(dirname(SOY2::RootDir()) . "/conf/shop/" . $siteId . ".conf.php");

		SOY2DAOConfig::Dsn(SOYSHOP_SITE_DSN);
		SOY2DAOConfig::user(SOYSHOP_SITE_USER);
		SOY2DAOConfig::pass(SOYSHOP_SITE_PASS);


		//SOYShop_ShopConfig::saveで利用するクラスの読み込み
		SOY2::import("domain.config.SOYShop_DataSetsDAO");
		SOY2::import("domain.config.SOYShop_DataSets");
		SOY2::import("domain.config.SOYShop_ShopConfig");

		return $old;
	}

	public static function resetShopMode($old){
		SOY2::RootDir($old["root"]);
		SOY2DAOConfig::DaoDir($old["dao"]);
		SOY2DAOConfig::EntityDir($old["entity"]);
		SOY2DAOConfig::DaoCacheDir($old["cache"]);
		SOY2DAOConfig::Dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

		SOY2HTMLConfig::PageDir($old["page"]);
	}

	/**
	 * SOY2::RootDir()の切り替え
	 * @param Boolean toShop switch to the path
	 * @return String old path
	 */
	public static function switchRoot($toShop = true){
		$old = SOY2::RootDir();

		if($toShop){
			SOY2::RootDir(SOYSHOP_COMMON_DIR);//SOY Shop
		}else{
			SOY2::RootDir(SOYCMS_COMMON_DIR);//SOY CMS
		}
    	return $old;
	}

	/**
	 * DAOの切り替え
	 * @param Boolean to switch to the path
	 * @return String old path
	 */
	public static function switchDao($toShop = true){
		$old = SOY2DAOConfig::DaoDir();

		if($toShop){
			SOY2DAOConfig::DaoDir(SOYSHOP_COMMON_DIR . "domain/");//SOY Shop
			SOY2DAOConfig::EntityDir(SOYSHOP_COMMON_DIR . "domain/");
		}else{
			SOY2DAOConfig::DaoDir(SOYCMS_COMMON_DIR."domain/");//SOY CMS
			SOY2DAOConfig::EntityDir(SOYCMS_COMMON_DIR."domain/");
		}

		return $old;
	}

	/**
	 * DSNの切り替え
	 * @param String id soyshop id
	 * @return String old path
	 */
	public static function switchDsn($id = null){
		$old = SOY2DAOConfig::Dsn();

		$dsn = "";
		$site = "";
		if(!is_null($id)){
			//shop
			$conf = dirname((SOYSHOP_COMMON_DIR))."/conf/shop/".$id.".admin.conf.php";
			if(file_exists($conf)){

				if(defined($id."_SOYSHOP_SITE_DSN")){
					//mysql
					eval("\$dsn = ".$id."_SOYSHOP_SITE_DSN;");
					SOY2DAOConfig::Dsn($dsn);
				}else{
					//sqlite
					eval("\$site = ".$id."_SOYSHOP_SITE_DIRECTORY;");
					SOY2DAOConfig::Dsn("sqlite:" . $site . ".db/sqlite.db");
				}
			}

		}else{
			//cms
			SOY2DAOConfig::Dsn(ADMIN_DB_DSN);

		}

		return $old;
	}


	/**
	 * DSNの切り替え
	 * @param String id soyshop id
	 * @return String old path
	 */
	public static function switchPass($id = null){
		$old = SOY2DAOConfig::pass();

		$pass = "";

		if(!is_null($id)){
			//shop
			$conf = dirname((SOYSHOP_COMMON_DIR))."/conf/shop/".$id.".admin.conf.php";
			if(file_exists($conf)){

				if(defined($id."_SOYSHOP_SITE_PASS")){
					//mysql
					eval("\$pass = ".$id."_SOYSHOP_SITE_PASS;");
					SOY2DAOConfig::pass($pass);
				}else{
					//sqlite
					SOY2DAOConfig::pass("");
				}
			}

		}else{
			//cms
			SOY2DAOConfig::pass(ADMIN_DB_PASS);

		}

		return $old;
	}


	/**
	 * Userの切り替え
	 * @param String id soyshop id
	 * @return String old path
	 */
	public static function switchUser($id = null){
		$old = SOY2DAOConfig::user();

		$user = "";

		if(!is_null($id)){
			//shop
			$conf = dirname((SOYSHOP_COMMON_DIR))."/conf/shop/".$id.".admin.conf.php";
			if(file_exists($conf)){

				if(defined($id."_SOYSHOP_SITE_USER")){
					//mysql
					eval("\$user = ".$id."_SOYSHOP_SITE_USER;");
					SOY2DAOConfig::user($user);
				}else{
					//sqlite
					SOY2DAOConfig::user("");
				}
			}

		}else{
			//cms
			SOY2DAOConfig::user(ADMIN_DB_USER);
		}

		return $old;
	}


	/**
	 * soyshop/webapp/conf/shop/*.conf.php
	 * @param String SOY Shop site id
	 * @return String SOY Shop site name
	 */
	public static function getSOYShopName($id){
		$name = "";

		$conf = dirname((SOYSHOP_COMMON_DIR))."/conf/shop/".$id.".admin.conf.php";
		if(file_exists($conf)){
			self::includeSOYShopConfig($id);
			self::switchRoot();
			self::switchDao();
			self::switchDsn($id);
			self::switchUser($id);
			self::switchPass($id);
			SOY2::imports("domain.config.*");
			$config = SOYShop_ShopConfig::load();
			$name = $config->getShopName();

			self::switchRoot(false);
			self::switchDao(false);
			self::switchDsn();
			self::switchUser();
			self::switchPass();

			return $name;
		}

		return $name;
	}

	/**
	 * soyshop/webapp/conf/shop/*.conf.php
	 * @param String SOY Shop site id
	 * @return String SOY Shop site url
	 */
	public static function getSOYShopUrl($id){
		$conf = dirname((SOYSHOP_COMMON_DIR))."/conf/shop/".$id.".admin.conf.php";

		$url = "";

		if(file_exists($conf)){
			include_once($conf);
			eval("\$url = ".$id."_SOYSHOP_SITE_URL;");
			return $url;
		}
		return $url;
	}

	/**
	 * soyshop/webapp/conf/shop/*.conf.php
	 * @param String SOY Shop site id
	 */
	public static function unsetSOYShopConfig($id){
		$conf = dirname((SOYSHOP_COMMON_DIR))."/conf/shop/".$id.".admin.conf.php";
		if(file_exists($conf)){

		}
	}

	/**
	 * soyshop/webapp/conf/shop/*.conf.php
	 * @param String SOY Shop site id
	 */
	public static function includeSOYShopConfig($id){
		$conf = dirname((SOYSHOP_COMMON_DIR))."/conf/shop/".$id.".admin.conf.php";
		if(file_exists($conf)){
			self::unsetSOYShopConfig($id);
			include_once($conf);
			//DSN
			eval("\$dir = ".$id."_SOYSHOP_SITE_URL;");
			if(!defined("SOYSHOP_DSN")){
//				define("SOYSHOP_DSN", "sqlite:" . $dir . ".db/sqlite.db");
			}
			//USER

			//PASS

		}
	}


	/**
	 * ユーザがログイン権限を持っているか
	 */
	public static function hasAuthLogin(){

		return true;
	}

	/**
	 * site_idからsiteの情報を取得
	 */
	public static function getShopSite($siteId){
		$dao = SOY2DAOFactory::create("admin.SiteDAO");
		try{
			$site = $dao->getBySiteId($siteId);
		}catch(Exception $e){
			$site = new Site();
		}

		return $site;
	}

	public static function setShopAdminSession($session){
		$siteId = $_GET["site_id"];
		$root = $session->getAttribute("isdefault");

		if($root == 1 || $session->getAttribute("isSiteAdministrator")){
			$level = 1;
		}else{
			$level = self::_checkSiteAdmin($siteId, $session->getAttribute("userid"));
		}

		$session->setAttribute("app_shop_auth_level", $level);

		// SOY Shopの管理画面のURIを変更する
		if(file_exists(SOY2::RootDir() . "config/admin.uri.config.php")) include(SOY2::RootDir() . "config/admin.uri.config.php");
		if(!defined("SOYSHOP_ADMIN_URI")) define("SOYSHOP_ADMIN_URI", "soyshop");
		$url = SOY2PageController::createRelativeLink("../" . SOYSHOP_ADMIN_URI, true) . "?site_id=" . $siteId;
		header("Location:" . $url);
		exit;
	}

	/**
	 * SOY Shopのサイト権限をチェックする
	 * @return isLimitUser
	 */
	private static function _checkSiteAdmin($siteId, $userId){
		try{
			$id = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId)->getId();
			return SOY2DAOFactory::create("admin.SiteRoleDAO")->getSiteRole($id, $userId)->getIsLimitUser();
		}catch(Exception $e){
			return 0;
		}
	}
}
